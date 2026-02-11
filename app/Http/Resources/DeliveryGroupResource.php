<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DeliveryGroupResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'unit_id' => $this->unit_id,
            'group_name' => $this->group_name,
            'group_number' => $this->group_number,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'milestones' => DeliveryMilestoneResource::collection($this->whenLoaded('milestones')),
            'supply_chain_reference' => $this->whenLoaded('supplyChainReference', function () {
                return $this->supplyChainReference ? [
                    'id' => $this->supplyChainReference->id,
                    'dir_reference' => $this->supplyChainReference->dir_reference,
                    'csp_reference' => $this->supplyChainReference->csp_reference,
                    'source' => $this->supplyChainReference->source,
                    'delivery_terms' => $this->supplyChainReference->delivery_terms,
                ] : null;
            }),
        ];
    }
}
