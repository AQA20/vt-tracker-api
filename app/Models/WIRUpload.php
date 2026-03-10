<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'WIRUpload',
    properties: [
        new OA\Property(property: 'id', type: 'string', format: 'uuid'),
        new OA\Property(property: 'unit_id', type: 'string', format: 'uuid'),
        new OA\Property(property: 'progress_group', type: 'string', enum: ['installation', 'commissioning']),
        new OA\Property(property: 'file_path', type: 'string'),
        new OA\Property(property: 'file_name', type: 'string'),
        new OA\Property(property: 'file_size', type: 'integer'),
        new OA\Property(property: 'uploaded_by', type: 'string', format: 'uuid'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
    ]
)]
class WIRUpload extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'wir_uploads';

    protected $guarded = ['id'];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
