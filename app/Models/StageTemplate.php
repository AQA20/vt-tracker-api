<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Enums\UnitCategory;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "StageTemplate",
    properties: [
        new OA\Property(property: "id", type: "string", format: "uuid"),
        new OA\Property(property: "name", type: "string"),
        new OA\Property(property: "category", type: "string", enum: ["elevator", "escalator", "travelator", "dumbwaiter"]),
        new OA\Property(property: "stage_number", type: "integer"),
        new OA\Property(property: "progress_group", type: "string", nullable: true)
    ]
)]
class StageTemplate extends Model
{
    use HasFactory, HasUuids;

    protected $guarded = ['id'];

    protected $casts = [
        'category' => UnitCategory::class,
    ];

    public function taskTemplates(): HasMany
    {
        return $this->hasMany(TaskTemplate::class);
    }
}
