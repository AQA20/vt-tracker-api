<?php

namespace App\Enums;

enum StatusCategory: string
{
    case TECH = 'tech';
    case SAMPLE = 'sample';
    case LAYOUT = 'layout';
    case CAR_M_DWG = 'car_m_dwg';
    case COP_DWG = 'cop_dwg';
    case LANDING_DWG = 'landing_dwg';

    public function label(): string
    {
        return match ($this) {
            self::TECH => 'Tech Sub Status',
            self::SAMPLE => 'Sample Status',
            self::LAYOUT => 'Layout Status',
            self::CAR_M_DWG => 'Car M DWG Status',
            self::COP_DWG => 'COP DWG Status',
            self::LANDING_DWG => 'Landing DWG Status',
        };
    }
}
