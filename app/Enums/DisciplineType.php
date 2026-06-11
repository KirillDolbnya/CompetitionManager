<?php

namespace App\Enums;

enum DisciplineType: string
{
    case TUL = 'туль';
    case SPARRING = 'спарринг';
    case POWER = 'сила удара';
    case TECH = 'спецтехника';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
