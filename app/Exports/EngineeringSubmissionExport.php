<?php

namespace App\Exports;

use App\Models\CseDetail;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class EngineeringSubmissionExport implements FromCollection, WithHeadings, WithTitle
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        if (isset($this->filters['template']) && $this->filters['template'] == 'true') {
            return collect([]);
        }

        return CseDetail::query()
            ->leftJoin('status_updates', 'cse_details.id', '=', 'status_updates.cse_id')
            ->leftJoin('dg1_milestones', 'cse_details.id', '=', 'dg1_milestones.cse_id')
            ->select([
                'cse_details.equip_n',
                'cse_details.asset_name',
                'cse_details.unit_id',
                'cse_details.material_code',
                'cse_details.so_no',
                'cse_details.network_no',

                // Status Updates
                'status_updates.tech_sub_status',
                'status_updates.sample_status',
                'status_updates.layout_status',
                'status_updates.car_m_dwg_status',
                'status_updates.cop_dwg_status',
                'status_updates.landing_dwg_status',

                // DG1 Milestones
                'dg1_milestones.ms2',
                'dg1_milestones.ms2a',
                'dg1_milestones.ms2c',
                'dg1_milestones.ms2z',
                'dg1_milestones.ms3',
                'dg1_milestones.ms3a_exw',
                'dg1_milestones.ms3b',
                'dg1_milestones.ms3s_ksa_port',
                'dg1_milestones.ms2_3s',
            ])
            ->get();
    }

    public function headings(): array
    {
        return [
            'equip_n',
            'asset_name',
            'unit_id',
            'material_code',
            'so_no',
            'network_no',

            'tech_sub_status',
            'sample_status',
            'layout_status',
            'car_m_dwg_status',
            'cop_dwg_status',
            'landing_dwg_status',

            'ms2',
            'ms2a',
            'ms2c',
            'ms2z',
            'ms3',
            'ms3a_exw',
            'ms3b',
            'ms3s_ksa_port',
            'ms2_3s',
        ];
    }

    public function title(): string
    {
        return 'engineering_submissions';
    }
}
