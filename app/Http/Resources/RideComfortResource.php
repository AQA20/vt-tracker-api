<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RideComfortResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'unit_id' => $this->unit_id,
            'vibration_value' => $this->vibration_value,
            'noise_db' => $this->noise_db,
            'jerk_value' => $this->jerk_value,
            'passed' => $this->passed,
            'measured_at' => $this->measured_at,
            'device_used' => $this->device_used,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
