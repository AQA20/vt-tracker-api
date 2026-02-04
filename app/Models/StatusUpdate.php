<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\EngineeringSubmissionStatus;

class StatusUpdate extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        // We don't cast to Enum class here because the column stores the string value directly
        // and we might want to validate against Enum values in Service/Request layer.
        // However, we could cast if we want strict typing. 
        // The prompt said: "status columns stored as string (NOT DB enum); validation uses a PHP Enum list".
        // It didn't explicitly ask for model casting, but it's good practice. 
        // Let's NOT cast for now to avoid issues if DB has old values, unless required. 
        // Prompt says "validation uses a PHP Enum list".
    ];

    public function cseDetail(): BelongsTo
    {
        return $this->belongsTo(CseDetail::class, 'cse_id');
    }
}
