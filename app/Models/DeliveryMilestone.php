<?php

namespace App\Models;

use App\Enums\MilestoneCode;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'DeliveryMilestone',
    required: ['delivery_group_id', 'milestone_code'],
    properties: [
        new OA\Property(property: 'id', type: 'string', format: 'uuid'),
        new OA\Property(property: 'delivery_group_id', type: 'string', format: 'uuid'),
        new OA\Property(property: 'milestone_code', type: 'string', enum: ['PO_SENT', 'READY_FOR_SHIPMENT', 'DELIVERED_TO_SITE', 'RECEIVED_BY_INSTALLATION']),
        new OA\Property(property: 'planned_leadtime_days', type: 'integer', nullable: true),
        new OA\Property(property: 'planned_completion_date', type: 'string', format: 'date', nullable: true),
        new OA\Property(property: 'actual_completion_date', type: 'string', format: 'date', nullable: true),
        new OA\Property(property: 'difference_days', type: 'integer', nullable: true),
        new OA\Property(property: 'status', type: 'string', enum: ['on-track', 'overdue', 'completed-on-time', 'completed-late']),
    ]
)]
class DeliveryMilestone extends Model
{
    use HasFactory, HasUuids;

    protected $guarded = ['id'];

    protected $casts = [
        'milestone_code' => MilestoneCode::class,
        'planned_leadtime_days' => 'integer',
        'planned_completion_date' => 'date',
        'actual_completion_date' => 'date',
        'difference_days' => 'integer',
    ];

    protected $appends = ['status']; // Append status to JSON

    protected static function booted()
    {
        // Auto-calculate difference when saving
        static::saving(function (DeliveryMilestone $milestone) {
            if ($milestone->planned_completion_date && $milestone->actual_completion_date) {
                $milestone->difference_days = $milestone->planned_completion_date
                    ->diffInDays($milestone->actual_completion_date, false);
            } else {
                $milestone->difference_days = null;
            }
        });
    }

    public function deliveryGroup(): BelongsTo
    {
        return $this->belongsTo(DeliveryGroup::class);
    }

    public function getStatusAttribute(): string
    {
        // Case 1: Milestone is completed
        if ($this->actual_completion_date) {
            // If no planned date was set, we consider it on-time (or just 'completed')
            if (! $this->planned_completion_date) {
                return 'completed-on-time';
            }

            return $this->actual_completion_date->gt($this->planned_completion_date)
                ? 'completed-late'
                : 'completed-on-time';
        }

        // Case 2: Milestone is NOT completed

        // If no planned date, it's just "on-track" (pending scheduling)
        if (! $this->planned_completion_date) {
            return 'on-track';
        }

        // If today is past planned date, it's overdue
        return now()->gt($this->planned_completion_date)
            ? 'overdue'
            : 'on-track';
    }
}
