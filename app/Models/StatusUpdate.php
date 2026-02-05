<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StatusUpdate extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'tech_sub_rejection_count' => 'integer',
        'sample_rejection_count' => 'integer',
        'layout_rejection_count' => 'integer',
        'car_m_dwg_rejection_count' => 'integer',
        'cop_dwg_rejection_count' => 'integer',
        'landing_dwg_rejection_count' => 'integer',
    ];

    public function cseDetail(): BelongsTo
    {
        return $this->belongsTo(CseDetail::class, 'cse_id');
    }
}
