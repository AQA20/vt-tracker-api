<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DeliveryMilestoneResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'delivery_group_id' => $this->delivery_group_id,
            'milestone_code' => $this->milestone_code?->value,
            'milestone_name' => $this->milestone_name,
            'milestone_label' => $this->milestone_code?->label(),
            'milestone_description' => $this->milestone_code?->description(),
            'planned_leadtime_days' => $this->planned_leadtime_days,
            'planned_completion_date' => $this->planned_completion_date?->toDateString(),
            'actual_completion_date' => $this->actual_completion_date?->toDateString(),
            'difference_days' => $this->difference_days,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
