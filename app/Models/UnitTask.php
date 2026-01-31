<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'UnitTask',
    properties: [
        new OA\Property(property: 'id', type: 'string', format: 'uuid'),
        new OA\Property(property: 'unit_stage_id', type: 'string', format: 'uuid'),
        new OA\Property(property: 'task_template_id', type: 'string', format: 'uuid'),
        new OA\Property(property: 'status', type: 'string', enum: ['pending', 'pass', 'fail']),
        new OA\Property(property: 'notes', type: 'string', nullable: true),
        new OA\Property(property: 'checked_by', type: 'integer', nullable: true),
        new OA\Property(property: 'checked_at', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(property: 'template', ref: '#/components/schemas/TaskTemplate'),
    ]
)]
class UnitTask extends Model
{
    use HasFactory, HasUuids;

    protected $guarded = ['id'];

    protected $casts = [
        'checked_at' => 'datetime',
    ];

    public function unitStage(): BelongsTo
    {
        return $this->belongsTo(UnitStage::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(TaskTemplate::class, 'task_template_id');
    }

    public function checker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_by');
    }
}
