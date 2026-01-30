<?php

namespace App\Enums;

enum RideComfortDevice: string
{
    case EVA_625 = 'eva_625';
    case VIBXPERT_II = 'vibxpert_ii';
    case LMS_TEST_LAB = 'lms_test_lab';
    case KONE_RIDE_CHECK = 'kone_ride_check';
    case BRUEL_KJAER_2250 = 'bruel_kjaer_2250';
    case OTHER_CERTIFIED = 'other_certified';
}
