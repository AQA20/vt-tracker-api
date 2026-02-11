<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'DeliveryModule',
    required: ['name'],
    properties: [
        new OA\Property(property: 'id', type: 'string', format: 'uuid'),
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'contents', type: 'array', items: new OA\Items(ref: '#/components/schemas/DeliveryModuleContent')),
    ]
)]
class DeliveryModule extends Model
{
    use HasFactory, HasUuids;

    protected $guarded = ['id'];

    public function contents(): HasMany
    {
        return $this->hasMany(DeliveryModuleContent::class);
    }
}
