<?php

namespace App\Http\Requests;

use App\Enums\EngineeringSubmissionStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEngineeringSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $statusValues = EngineeringSubmissionStatus::values();

        return [
            'equip_n' => ['required', 'integer', 'unique:cse_details,equip_n'],
            'asset_name' => ['nullable', 'string'],
            'unit_id' => ['nullable', 'string'],
            'material_code' => ['nullable', 'string'],
            'so_no' => ['nullable', 'integer'],
            'network_no' => ['nullable', 'integer'],

            // Nested status_update
            'status_update' => ['nullable', 'array'],
            'status_update.tech_sub_status' => ['nullable', Rule::in($statusValues)],
            'status_update.sample_status' => ['nullable', Rule::in($statusValues)],
            'status_update.layout_status' => ['nullable', Rule::in($statusValues)],
            'status_update.car_m_dwg_status' => ['nullable', Rule::in($statusValues)],
            'status_update.cop_dwg_status' => ['nullable', Rule::in($statusValues)],
            'status_update.landing_dwg_status' => ['nullable', Rule::in($statusValues)],

            // Nested dg1_milestone
            'dg1_milestone' => ['nullable', 'array'],
            'dg1_milestone.ms2' => ['nullable', 'date_format:Y-m-d'],
            'dg1_milestone.ms2a' => ['nullable', 'date_format:Y-m-d'],
            'dg1_milestone.ms2c' => ['nullable', 'date_format:Y-m-d'],
            'dg1_milestone.ms2z' => ['nullable', 'date_format:Y-m-d'],
            'dg1_milestone.ms3' => ['nullable', 'date_format:Y-m-d'],
            'dg1_milestone.ms3a_exw' => ['nullable', 'date_format:Y-m-d'],
            'dg1_milestone.ms3b' => ['nullable', 'date_format:Y-m-d'],
            'dg1_milestone.ms3s_ksa_port' => ['nullable', 'date_format:Y-m-d'],
            'dg1_milestone.ms2_3s' => ['nullable', 'integer'],
        ];
    }
}
