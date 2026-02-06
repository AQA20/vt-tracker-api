<?php

namespace App\Models;

use App\Enums\Status;
use App\Enums\StatusCategory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StatusUpdate extends Model
{
    use HasUuids;

    protected $guarded = ['id'];

    protected $casts = [
        'category' => StatusCategory::class,
        'status' => Status::class,
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(StatusRevision::class)->orderBy('revision_number');
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(StatusApproval::class);
    }
}
