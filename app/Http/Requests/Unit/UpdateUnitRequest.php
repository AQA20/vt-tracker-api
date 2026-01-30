<?php

namespace App\Http\Requests\Unit;

use App\Enums\UnitCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'equipment_number' => 'string|unique:units,equipment_number,' . $this->route('unit')->id,
            'category' => [new Enum(UnitCategory::class)],
        ];
    }
}
