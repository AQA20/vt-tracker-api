<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Dg1Milestone extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'ms2' => 'date',
        'ms2a' => 'date',
        'ms2c' => 'date',
        'ms2z' => 'date',
        'ms3' => 'date',
        'ms3a_exw' => 'date',
        'ms3b' => 'date',
        'ms3s_ksa_port' => 'date',
        'ms2_3s' => 'integer',
    ];

    public function cseDetail(): BelongsTo
    {
        return $this->belongsTo(CseDetail::class, 'cse_id');
    }
}
