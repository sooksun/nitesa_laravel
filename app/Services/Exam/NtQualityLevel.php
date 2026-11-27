<?php

namespace App\Services\Exam;

class NtQualityLevel
{
    /** เกณฑ์ร้อยละรวม 2 ด้านจากรายงานผล NT ของ สพฐ. */
    private const THRESHOLDS = [
        'ดีมาก' => 68.5,
        'ดี' => 47.0,
        'พอใช้' => 27.5,
    ];

    public static function fromAverage(?float $average): ?string
    {
        if ($average === null) {
            return null;
        }

        foreach (self::THRESHOLDS as $level => $minimum) {
            if ($average >= $minimum) {
                return $level;
            }
        }

        return 'ปรับปรุง';
    }
}
