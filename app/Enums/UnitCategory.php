<?php

namespace App\Enums;

enum UnitCategory: string
{
    case ELEVATOR = 'elevator';
    case ESCALATOR = 'escalator';
    case TRAVELATOR = 'travelator';
    case DUMBWAITER = 'dumbwaiter';
}
