<?php

namespace App\Models;

use App\Enums\RideComfortDevice;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'RideComfortResult',
    properties: [
        new OA\Property(property: 'id', type: 'string', format: 'uuid'),
        new OA\Property(property: 'unit_id', type: 'string', format: 'uuid'),
        new OA\Property(property: 'vibration_value', type: 'number', format: 'float'),
        new OA\Property(property: 'noise_db', type: 'number', format: 'float'),
        new OA\Property(property: 'jerk_value', type: 'number', format: 'float'),
        new OA\Property(property: 'passed', type: 'boolean'),
        new OA\Property(property: 'measured_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'device_used', type: 'string', enum: ['eva_625', 'vibxpert_ii', 'lms_test_lab', 'kone_ride_check', 'bruel_kjaer_2250', 'other_certified'], nullable: true),
        new OA\Property(property: 'notes', type: 'string', nullable: true),
    ]
)]
class RideComfortResult extends Model
{
    use HasFactory, HasUuids;

    protected $guarded = ['id'];

    protected $casts = [
        'vibration_value' => 'float',
        'noise_db' => 'float',
        'jerk_value' => 'float',
        'passed' => 'boolean',
        'measured_at' => 'datetime',
        'device_used' => RideComfortDevice::class,
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}
