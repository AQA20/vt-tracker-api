<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StatusUpdateResource extends JsonResource
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
            'category' => $this->category,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'revisions' => $this->whenLoaded('revisions', function () {
                $grouped = $this->revisions->groupBy(fn ($rev) => $rev->category->value);

                return [
                    'submitted' => StatusRevisionResource::collection($grouped->get('submitted', collect())),
                    'rejected' => StatusRevisionResource::collection($grouped->get('rejected', collect())),
                ];
            }),
            'approvals' => StatusApprovalResource::collection($this->whenLoaded('approvals')),
        ];
    }
}
