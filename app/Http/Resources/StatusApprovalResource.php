<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StatusApprovalResource extends JsonResource
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
            'status_update_id' => $this->status_update_id,
            'approval_code' => $this->approval_code,
            'pdf_path' => $this->pdf_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($this->pdf_path) : null,
            'comment' => $this->comment,
            'approved_at' => $this->approved_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
