<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StatusRevisionResource extends JsonResource
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
            'revision_number' => $this->revision_number,
            'pdf_path' => $this->pdf_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($this->pdf_path) : null,
            'revision_date' => $this->revision_date,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
