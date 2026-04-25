<?php

namespace App\Services;

use App\Enums\UnitCategory;
use App\Models\Project;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class UnitImportService
{
    public function import(Project $project, UploadedFile $file): array
    {
        $sheets = Excel::toArray([], $file);
        $data = $sheets[0] ?? [];

        if (count($data) < 2) {
            throw ValidationException::withMessages([
                'file' => ['The import file is empty or missing headers.'],
            ]);
        }

        $headers = array_map(
            static fn ($header) => strtolower(trim((string) $header)),
            $data[0]
        );
        $rows = array_values(array_slice($data, 1));

        $requiredHeaders = ['equipment_number', 'unit_type', 'category'];
        $missingHeaders = array_values(array_diff($requiredHeaders, $headers));

        if (! empty($missingHeaders)) {
            throw ValidationException::withMessages([
                'file' => ['Missing required columns: '.implode(', ', $missingHeaders)],
            ]);
        }

        $results = [
            'total_rows' => count($rows),
            'successful_rows' => 0,
            'failed_rows' => 0,
            'rows' => [],
        ];

        $seenEquipmentNumbers = [];

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2;
            $normalizedRow = $this->normalizeRow($headers, $row);
            $equipmentNumber = $normalizedRow['equipment_number'] ?? null;

            if ($equipmentNumber) {
                $normalizedEquipment = strtolower($equipmentNumber);
                if (isset($seenEquipmentNumbers[$normalizedEquipment])) {
                    $results['rows'][] = [
                        'row' => $rowNumber,
                        'equipment_number' => $equipmentNumber,
                        'errors' => ['Duplicate equipment number in file.'],
                    ];
                    $results['failed_rows']++;
                    continue;
                }
                $seenEquipmentNumbers[$normalizedEquipment] = true;
            }

            $validator = Validator::make($normalizedRow, [
                'unit_type' => 'required|string|in:Company MonoSpace 700,Company MonoSpace 500',
                'equipment_number' => 'required|string|max:255|unique:units,equipment_number',
                'category' => ['required', new Enum(UnitCategory::class)],
                'sl_reference_no' => 'nullable|string',
                'fl_unit_name' => 'nullable|string',
                'unit_description' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                $results['rows'][] = [
                    'row' => $rowNumber,
                    'equipment_number' => $equipmentNumber,
                    'errors' => $validator->errors()->all(),
                ];
                $results['failed_rows']++;
                continue;
            }

            try {
                $unit = $project->units()->create($validator->validated());
                UnitService::generateStagesAndTasks($unit);
                $results['successful_rows']++;
            } catch (Exception $exception) {
                Log::warning('Unit row import failed', [
                    'project_id' => $project->id,
                    'row' => $rowNumber,
                    'equipment_number' => $equipmentNumber,
                    'error' => $exception->getMessage(),
                ]);

                $results['rows'][] = [
                    'row' => $rowNumber,
                    'equipment_number' => $equipmentNumber,
                    'errors' => ['Unexpected error while importing this row.'],
                ];
                $results['failed_rows']++;
            }
        }

        ProgressService::recalculateProject($project);

        return $results;
    }

    private function normalizeRow(array $headers, array $row): array
    {
        $normalized = [
            'equipment_number' => null,
            'unit_type' => null,
            'category' => null,
            'sl_reference_no' => null,
            'fl_unit_name' => null,
            'unit_description' => null,
        ];

        foreach ($headers as $columnIndex => $header) {
            if (! array_key_exists($header, $normalized)) {
                continue;
            }

            $value = $row[$columnIndex] ?? null;
            if ($value === null) {
                $normalized[$header] = null;
                continue;
            }

            $trimmed = trim((string) $value);
            $normalized[$header] = $trimmed === '' ? null : $trimmed;
        }

        if ($normalized['category'] !== null) {
            $normalized['category'] = strtolower($normalized['category']);
        }

        return $normalized;
    }
}
