<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "UnitStage",
    properties: [
        new OA\Property(property: "id", type: "string", format: "uuid"),
        new OA\Property(property: "unit_id", type: "string", format: "uuid"),
        new OA\Property(property: "stage_template_id", type: "string", format: "uuid"),
        new OA\Property(property: "status", type: "string", enum: ["pending", "in_progress", "completed"]),
        new OA\Property(property: "started_at", type: "string", format: "date-time", nullable: true),
        new OA\Property(property: "completed_at", type: "string", format: "date-time", nullable: true),
        new OA\Property(property: "progress_percent", type: "integer", example: 50),
        new OA\Property(property: "template", ref: "#/components/schemas/StageTemplate")
    ]
)]
class UnitStage extends Model
{
    use HasFactory, HasUuids;

    protected $guarded = ['id'];
    protected $appends = ['progress_percent'];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(StageTemplate::class, 'stage_template_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(UnitTask::class);
    }

    public function getProgressPercentAttribute(): int
    {
        $totalTasks = $this->tasks()->count();
        if ($totalTasks === 0) {
            return $this->status === 'completed' ? 100 : 0;
        }

        $passedTasks = $this->tasks()->where('status', 'pass')->count();
        
        if ($passedTasks === $totalTasks) {
            return 100;
        }

        if ($passedTasks === 0) {
            return 0;
        }

        $percentage = ($passedTasks / $totalTasks) * 100;
        return (int) max(1, min(99, ceil($percentage)));
    }


}
