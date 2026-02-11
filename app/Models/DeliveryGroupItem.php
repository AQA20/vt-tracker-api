<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'DeliveryGroupItem',
    required: ['delivery_group_id', 'delivery_module_content_id', 'package_type'],
    properties: [
        new OA\Property(property: 'id', type: 'string', format: 'uuid'),
        new OA\Property(property: 'delivery_group_id', type: 'string', format: 'uuid'),
        new OA\Property(property: 'delivery_module_content_id', type: 'string', format: 'uuid'),
        new OA\Property(property: 'remarks', type: 'string', nullable: true),
        new OA\Property(property: 'package_type', type: 'string', enum: ['Standard Packing', 'Sea Packing', 'Bark Free Packing']),
        new OA\Property(property: 'special_delivery_address', type: 'string', nullable: true),
        new OA\Property(property: 'content', ref: '#/components/schemas/DeliveryModuleContent'),
        new OA\Property(property: 'module_name', type: 'string', description: 'Derived from content relationship'),
    ]
)]
class DeliveryGroupItem extends Model
{
    use HasFactory, HasUuids;

    protected $guarded = ['id'];

    public function deliveryGroup(): BelongsTo
    {
        return $this->belongsTo(DeliveryGroup::class);
    }

    public function content(): BelongsTo
    {
        return $this->belongsTo(DeliveryModuleContent::class, 'delivery_module_content_id');
    }
}
