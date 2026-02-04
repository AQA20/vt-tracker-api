<?php

namespace App\Enums;

enum EngineeringSubmissionStatus: string
{
    case IN_PROGRESS = 'In Progress';
    case SUBMITTED = 'Submitted';
    case UNDER_REVIEW = 'Under Review';
    case APPROVED = 'Approved';
    case REJECTED = 'Rejected';
    case RE_SUBMISSION = 'Re-Submission';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
