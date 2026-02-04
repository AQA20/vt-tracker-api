<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CseDetail extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function statusUpdate(): HasOne
    {
        return $this->hasOne(StatusUpdate::class, 'cse_id');
    }

    public function dg1Milestone(): HasOne
    {
        return $this->hasOne(Dg1Milestone::class, 'cse_id');
    }
}
