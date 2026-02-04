<?php

namespace App\Imports;

use App\Models\CseDetail;
use App\Models\Dg1Milestone;
use App\Models\StatusUpdate;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class EngineeringSubmissionImport implements ToCollection, WithHeadingRow
{
    public array $result = [
        'processed' => 0,
        'created' => 0,
        'updated' => 0,
        'errors' => [],
    ];

    public function collection(Collection $rows)
    {
        $this->result['processed'] += $rows->count();

        foreach ($rows as $index => $row) {
            if (! isset($row['equip_n'])) {
                $this->result['errors'][] = [
                    'sheet' => 'engineering_submissions',
                    'row' => $index + 2,
                    'message' => 'equip_n missing',
                ];

                continue;
            }

            // Upsert CseDetail
            $cse = CseDetail::updateOrCreate(
                ['equip_n' => $row['equip_n']],
                [
                    'asset_name' => $row['asset_name'] ?? null,
                    'unit_id' => $row['unit_id'] ?? null,
                    'material_code' => $row['material_code'] ?? null,
                    'so_no' => $row['so_no'] ?? null,
                    'network_no' => $row['network_no'] ?? null,
                ]
            );

            if ($cse->wasRecentlyCreated) {
                $this->result['created']++;
            } else {
                $this->result['updated']++;
            }

            // 2. Upsert StatusUpdate
            StatusUpdate::updateOrCreate(
                ['cse_id' => $cse->id],
                [
                    'tech_sub_status' => $row['tech_sub_status'] ?? null,
                    'sample_status' => $row['sample_status'] ?? null,
                    'layout_status' => $row['layout_status'] ?? null,
                    'car_m_dwg_status' => $row['car_m_dwg_status'] ?? null,
                    'cop_dwg_status' => $row['cop_dwg_status'] ?? null,
                    'landing_dwg_status' => $row['landing_dwg_status'] ?? null,
                ]
            );

            // 3. Upsert Dg1Milestone
            Dg1Milestone::updateOrCreate(
                ['cse_id' => $cse->id],
                [
                    'ms2' => $this->parseDate($row['ms2'] ?? null),
                    'ms2a' => $this->parseDate($row['ms2a'] ?? null),
                    'ms2c' => $this->parseDate($row['ms2c'] ?? null),
                    'ms2z' => $this->parseDate($row['ms2z'] ?? null),
                    'ms3' => $this->parseDate($row['ms3'] ?? null),
                    'ms3a_exw' => $this->parseDate($row['ms3a_exw'] ?? null),
                    'ms3b' => $this->parseDate($row['ms3b'] ?? null),
                    'ms3s_ksa_port' => $this->parseDate($row['ms3s_ksa_port'] ?? null),
                    'ms2_3s' => $row['ms2_3s'] ?? null,
                ]
            );
        }
    }

    private function parseDate($value)
    {
        if (! $value) {
            return null;
        }
        try {
            // Check if it's already a DateTime object from Excel
            if ($value instanceof \DateTimeInterface) {
                return $value;
            }
            // Check if it's a numeric Excel timestamp
            if (is_numeric($value)) {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value);
            }

            // Fallback for string dates (Y-m-d)
            return $value;
        } catch (\Throwable $e) {
            return $value;
        }
    }
}
