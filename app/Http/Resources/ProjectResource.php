<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
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
            'name' => $this->name,
            'client_name' => $this->client_name,
            'location' => $this->location,
            'completion_percentage' => $this->completion_percentage,
            'installation_progress' => $this->installation_progress,
            'commissioning_progress' => $this->commissioning_progress,
            'units_count' => $this->units_count,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'units' => UnitResource::collection($this->whenLoaded('units')),
        ];
    }
}
