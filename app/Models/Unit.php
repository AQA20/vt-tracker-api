<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Enums\UnitCategory;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "Unit",
    required: ["unit_type", "serial_number", "category"],
    properties: [
        new OA\Property(property: "id", type: "string", format: "uuid"),
        new OA\Property(property: "project_id", type: "string", format: "uuid"),
        new OA\Property(property: "unit_type", type: "string"),
        new OA\Property(property: "equipment_number", type: "string"),
        new OA\Property(property: "category", type: "string", enum: ["elevator", "escalator", "travelator", "dumbwaiter"]),
        new OA\Property(property: "progress_percent", type: "integer"),
        new OA\Property(property: "installation_progress", type: "integer"),
        new OA\Property(property: "commissioning_progress", type: "integer"),
        new OA\Property(property: "created_at", type: "string", format: "date-time"),
        new OA\Property(property: "updated_at", type: "string", format: "date-time")
    ]
)]
class Unit extends Model
{
    use HasFactory, HasUuids;

    protected $guarded = ['id'];

    protected $casts = [
        'category' => UnitCategory::class,
        'progress_percent' => 'integer',
        'installation_progress' => 'integer',
        'commissioning_progress' => 'integer',
    ];



    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function stages(): HasMany
    {
        return $this->hasMany(UnitStage::class)->orderBy('stage_template_id'); // We'll need to join to order by index ideally
    }

    public function rideComfortResults(): HasMany
    {
        return $this->hasMany(RideComfortResult::class);
    }


}
