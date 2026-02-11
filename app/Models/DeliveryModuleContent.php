<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'DeliveryModuleContent',
    required: ['delivery_module_id', 'name'],
    properties: [
        new OA\Property(property: 'id', type: 'string', format: 'uuid'),
        new OA\Property(property: 'delivery_module_id', type: 'string', format: 'uuid'),
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'module', ref: '#/components/schemas/DeliveryModule'),
    ]
)]
class DeliveryModuleContent extends Model
{
    use HasFactory, HasUuids;

    protected $guarded = ['id'];

    public function module(): BelongsTo
    {
        return $this->belongsTo(DeliveryModule::class, 'delivery_module_id');
    }
}
