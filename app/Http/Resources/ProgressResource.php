<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProgressResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'unit_id' => $this->id,
            'progress' => $this->progress_percent,
            'installation_progress' => $this->installation_progress,
            'commissioning_progress' => $this->commissioning_progress,
        ];
    }
}
