<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupplyChainReferenceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'unit_id' => $this->unit_id,
            'dir_reference' => $this->dir_reference,
            'csp_reference' => $this->csp_reference,
            'source' => $this->source,
            'delivery_terms' => $this->delivery_terms,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
