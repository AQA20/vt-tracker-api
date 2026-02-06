<?php

namespace App\Models;

use App\Enums\ApprovalCode;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StatusApproval extends Model
{
    use HasUuids;

    protected $guarded = ['id'];

    protected $casts = [
        'approval_code' => ApprovalCode::class,
        'approved_at' => 'datetime',
    ];

    public function statusUpdate(): BelongsTo
    {
        return $this->belongsTo(StatusUpdate::class);
    }
}
