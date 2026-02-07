<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StatusRevision extends Model
{
    use HasUuids;

    protected $guarded = ['id'];

    protected $casts = [
        'revision_date' => 'datetime',
        'revision_number' => 'integer',
        'category' => \App\Enums\StatusRevisionCategory::class,
    ];

    protected static function booted()
    {
        static::saving(function ($revision) {
            if ($revision->revision_number < 0 || $revision->revision_number > 9) {
                throw new \InvalidArgumentException('Revision number must be between 0 and 9');
            }
        });
    }

    public function statusUpdate(): BelongsTo
    {
        return $this->belongsTo(StatusUpdate::class);
    }
}
