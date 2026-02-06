<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UnitTaskResource extends JsonResource
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
            'unit_stage_id' => $this->unit_stage_id,
            'task_template_id' => $this->task_template_id,
            'status' => $this->status,
            'notes' => $this->notes,
            'checked_by' => $this->checked_by,
            'checked_at' => $this->checked_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'template' => new TaskTemplateResource($this->whenLoaded('template')),
        ];
    }
}
