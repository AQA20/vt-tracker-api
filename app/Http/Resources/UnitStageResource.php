<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UnitStageResource extends JsonResource
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
            'stage_template_id' => $this->stage_template_id,
            'status' => $this->status,
            'started_at' => $this->started_at,
            'completed_at' => $this->completed_at,
            'progress_percent' => $this->progress_percent,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'template' => new StageTemplateResource($this->whenLoaded('template')),
            'tasks' => UnitTaskResource::collection($this->whenLoaded('tasks')),
        ];
    }
}
