<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DeliveryGroupItemResource extends JsonResource
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
            'delivery_group_id' => $this->delivery_group_id,
            'delivery_module_content_id' => $this->delivery_module_content_id,
            'content' => new DeliveryModuleContentResource($this->whenLoaded('content')),
            'module_name' => $this->whenLoaded('content', function () {
                return $this->content->module->name;
            }),
            'remarks' => $this->remarks,
            'package_type' => $this->package_type,
            'special_delivery_address' => $this->special_delivery_address,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
