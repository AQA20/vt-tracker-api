<?php

namespace App\Enums;

enum Status: string
{
    case SUBMITTED = 'submitted';
    case IN_PROGRESS = 'in_progress';
    case REJECTED = 'rejected';
    case APPROVED = 'approved';
}
