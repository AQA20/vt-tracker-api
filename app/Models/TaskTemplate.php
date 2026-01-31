<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'TaskTemplate',
    properties: [
        new OA\Property(property: 'id', type: 'string', format: 'uuid'),
        new OA\Property(property: 'title', type: 'string'),
        new OA\Property(property: 'task_code', type: 'string'),
        new OA\Property(property: 'order_index', type: 'integer'),
    ]
)]
class TaskTemplate extends Model
{
    use HasFactory, HasUuids;

    protected $guarded = ['id'];

    protected $casts = [];

    public function stageTemplate(): BelongsTo
    {
        return $this->belongsTo(StageTemplate::class);
    }
}
