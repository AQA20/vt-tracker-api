<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'DeliveryGroup',
    required: ['unit_id', 'group_name', 'group_number'],
    properties: [
        new OA\Property(property: 'id', type: 'string', format: 'uuid'),
        new OA\Property(property: 'unit_id', type: 'string', format: 'uuid'),
        new OA\Property(property: 'group_name', type: 'string'),
        new OA\Property(property: 'group_number', type: 'integer'),
        new OA\Property(property: 'notes', type: 'string', nullable: true),
        new OA\Property(property: 'items', type: 'array', items: new OA\Items(ref: '#/components/schemas/DeliveryGroupItem')),
        new OA\Property(property: 'milestones', type: 'array', items: new OA\Items(ref: '#/components/schemas/DeliveryMilestone')),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
    ]
)]
class DeliveryGroup extends Model
{
    use HasFactory, HasUuids;

    protected $guarded = ['id'];

    protected $casts = [
        'group_number' => 'integer',
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function milestones(): HasMany
    {
        return $this->hasMany(DeliveryMilestone::class)->orderBy('milestone_code');
    }

    public function supplyChainReference()
    {
        return $this->hasOne(SupplyChainReference::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(DeliveryGroupItem::class);
    }
}
