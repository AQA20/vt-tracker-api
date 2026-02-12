<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Project',
    required: ['name', 'client_name', 'location'],
    properties: [
        new OA\Property(property: 'id', type: 'string', format: 'uuid'),
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'kone_project_id', type: 'string', nullable: true),
        new OA\Property(property: 'client_name', type: 'string'),
        new OA\Property(property: 'location', type: 'string'),
        new OA\Property(property: 'completion_percentage', type: 'integer', example: 65),
        new OA\Property(property: 'installation_progress', type: 'integer'),
        new OA\Property(property: 'commissioning_progress', type: 'integer'),
        new OA\Property(property: 'units_count', type: 'integer', readOnly: true, description: 'Only in list view'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'units', type: 'array', items: new OA\Items(ref: '#/components/schemas/Unit')),
    ]
)]
class Project extends Model
{
    use HasFactory, HasUuids;

    protected $guarded = ['id'];

    protected $appends = ['completion_percentage'];

    public function getCompletionPercentageAttribute(): int
    {
        $units = $this->units()->get();
        $unitsCount = $units->count();
        if ($unitsCount === 0) {
            return 0;
        }

        $avgOverall = $units->avg('progress_percent');

        if ($avgOverall == 100) {
            return 100;
        }

        if ($avgOverall == 0) {
            return 0;
        }

        // If any unit is not 100%, cap project overall progress at 99%
        $hasIncompleteUnits = $units->contains(fn ($u) => $u->progress_percent < 100);
        if ($hasIncompleteUnits && $avgOverall > 99) {
            return 99;
        }

        return (int) ceil($avgOverall);
    }

    public function units(): HasMany
    {
        return $this->hasMany(Unit::class)->orderBy('equipment_number');
    }
}
