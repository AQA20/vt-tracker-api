<?php

namespace App\Http\Requests\RideComfort;

use Illuminate\Foundation\Http\FormRequest;

use App\Enums\RideComfortDevice;
use Illuminate\Validation\Rules\Enum;

class StoreRideComfortRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'vibration_value' => 'required|numeric',
            'noise_db' => 'required|numeric',
            'jerk_value' => 'required|numeric',
            'device_used' => ['nullable', new Enum(RideComfortDevice::class)],
            'notes' => 'nullable|string',
        ];
    }
}
