<?php

namespace App\Enums;

enum MilestoneCode: string
{
    case ONE_A = '1a';
    case ONE_B = '1b';
    case ONE_C = '1c';
    case TWO = '2';
    case TWO_A = '2a';
    case TWO_C = '2c';
    case TWO_F = '2f';
    case THREE = '3';
    case THREE_A = '3a';
    case THREE_B = '3b';
    case THREE_S = '3s';

    public function label(): string
    {
        return match ($this) {
            self::ONE_A => 'SL Submits Approval Drawings for FL',
            self::ONE_B => 'Submit Drawing to Customer for Approval',
            self::ONE_C => 'Receive Approved Drawings',
            self::TWO => 'FL Sends Final Specification to SL',
            self::TWO_A => 'Technical Specification Approved by SL',
            self::TWO_C => 'Listing Completed',
            self::TWO_F => 'Engineering Completed',
            self::THREE => 'Point of No Return (NRP)',
            self::THREE_A => 'Material Ready at Distribution Center',
            self::THREE_B => 'Shipping Date',
            self::THREE_S => 'Material in Agreed Location per Delivery Term',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::ONE_A => 'Supply Line submits approval drawings to Front Line',
            self::ONE_B => 'Submit drawings to the customer for their approval',
            self::ONE_C => 'Receive approved drawings from client (applies to all unit categories)',
            self::TWO => 'Front Line sends final specification to Supply Line',
            self::TWO_A => 'Technical specification has been approved by Supply Line',
            self::TWO_C => 'Listing process completed',
            self::TWO_F => 'Engineering phase completed',
            self::THREE => 'Point of no return - NRP milestone',
            self::THREE_A => 'Material is ready at distribution center',
            self::THREE_B => 'Date when material is shipped from distribution center',
            self::THREE_S => 'Material delivered to agreed location per delivery terms',
            default => $this->label(),
        };
    }
}
