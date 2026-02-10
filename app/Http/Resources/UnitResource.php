<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UnitResource extends JsonResource
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
            'project_id' => $this->project_id,
            'unit_type' => $this->unit_type,
            'equipment_number' => $this->equipment_number,
            'category' => $this->category,
            'progress_percent' => $this->progress_percent,
            'installation_progress' => $this->installation_progress,
            'commissioning_progress' => $this->commissioning_progress,
            'sl_reference_no' => $this->sl_reference_no,
            'fl_unit_name' => $this->fl_unit_name,
            'unit_description' => $this->unit_description,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'status_updates' => StatusUpdateResource::collection($this->whenLoaded('statusUpdates')),
            'supply_chain_reference' => new SupplyChainReferenceResource($this->whenLoaded('supplyChainReference')),
            'delivery_groups' => DeliveryGroupResource::collection($this->whenLoaded('deliveryGroups')),
        ];
    }
}
