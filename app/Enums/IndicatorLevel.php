<?php

namespace App\Enums;

enum IndicatorLevel: string
{
    case EXCELLENT = 'EXCELLENT';
    case GOOD = 'GOOD';
    case FAIR = 'FAIR';
    case NEEDS_WORK = 'NEEDS_WORK';

    public function label(): string
    {
        return match($this) {
            self::EXCELLENT => 'ดีเยี่ยม',
            self::GOOD => 'ดี',
            self::FAIR => 'พอใช้',
            self::NEEDS_WORK => 'ต้องพัฒนา',
        };
    }

    public function score(): int
    {
        return match($this) {
            self::EXCELLENT => 4,
            self::GOOD => 3,
            self::FAIR => 2,
            self::NEEDS_WORK => 1,
        };
    }

    public function color(): string
    {
        return match($this) {
            self::EXCELLENT => 'green',
            self::GOOD => 'blue',
            self::FAIR => 'yellow',
            self::NEEDS_WORK => 'red',
        };
    }
}
