<?php

namespace App\Support;

use DateTimeInterface;

final class ThaiDate
{
    private const BUDDHIST_ERA_OFFSET = 543;

    public static function format(?DateTimeInterface $date, bool $withTime = false, string $empty = '-'): string
    {
        if ($date === null) {
            return $empty;
        }

        $formatted = $date->format('d/m/') . ((int) $date->format('Y') + self::BUDDHIST_ERA_OFFSET);

        return $withTime ? $formatted . ' ' . $date->format('H:i') : $formatted;
    }
}
