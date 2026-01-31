<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Unit;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UnitImportService
{
    protected $unitTemplateService;

    protected $progressService;

    public function __construct(UnitTemplateService $unitTemplateService, ProgressService $progressService)
    {
        $this->unitTemplateService = $unitTemplateService;
        $this->progressService = $progressService;
    }

    public function import(Project $project, UploadedFile $file)
    {
        $path = $file->getRealPath();
        $data = array_map('str_getcsv', file($path));

        if (count($data) < 2) {
            throw new Exception('CSV file is empty or missing headers.');
        }

        $headers = array_map('trim', $data[0]);
        $rows = array_slice($data, 1);

        $expectedHeaders = ['unit_name', 'equipment_number', 'type', 'category', 'capacity', 'speed', 'floors'];
        // Basic validation of headers can be added here if strictness is required.

        $results = [
            'success' => 0,
            'errors' => [],
        ];

        DB::beginTransaction();
        try {
            foreach ($rows as $index => $row) {
                if (count($row) !== count($headers)) {
                    // unexpected row length
                    continue;
                }

                $rowData = array_combine($headers, $row);

                // Validate Row
                $validator = Validator::make($rowData, [
                    'unit_name' => 'required|string|max:255',
                    'equipment_number' => 'required|string|max:255|unique:units,equipment_number',
                    'type' => 'required|string|max:255',
                    'category' => 'nullable|string|max:255',
                    'capacity' => 'nullable|integer',
                    'speed' => 'nullable|numeric',
                    'floors' => 'nullable|integer',
                ]);

                if ($validator->fails()) {
                    $results['errors'][] = [
                        'row' => $index + 2,
                        'errors' => $validator->errors()->all(),
                    ];

                    continue;
                }

                $rowData['project_id'] = $project->id;

                $unit = Unit::create($rowData);
                $this->unitTemplateService->applyTemplate($unit, $rowData['type']);

                // Initialize raw progress (0%)
                $unit->progress = 0;
                $unit->save();

                $results['success']++;
            }

            // Recalculate Project Aggregates ONCE
            $this->progressService->recalculateProject($project);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return $results;
    }
}
