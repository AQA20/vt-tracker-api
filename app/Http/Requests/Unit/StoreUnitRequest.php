<?php

namespace App\Http\Requests\Unit;

use App\Enums\UnitCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'unit_type' => 'required|string|in:Company MonoSpace 700,Company MonoSpace 500',
            'equipment_number' => 'required|string|unique:units,equipment_number',
            'category' => ['required', new Enum(UnitCategory::class)],
            'sl_reference_no' => 'nullable|string',
            'fl_unit_name' => 'nullable|string',
            'unit_description' => 'nullable|string',
        ];
    }
}
