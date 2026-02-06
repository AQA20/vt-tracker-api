<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StageTemplateResource extends JsonResource
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
            'category' => $this->category,
            'stage_number' => $this->stage_number,
            'progress_group' => $this->progress_group,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
