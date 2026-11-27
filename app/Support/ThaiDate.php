<?php

namespace App\Support;

use DateTimeInterface;
use Illuminate\Support\Carbon;

/**
 * วันที่ตามปฏิทิน (เช่น วันที่นิเทศ) ใช้ format() ซึ่งไม่แปลงเขตเวลา
 * ส่วนเวลาที่ระบบบันทึกเป็น UTC (createdAt ฯลฯ) ใช้ timestamp() / time() ซึ่งแปลงเป็นเวลาที่แสดงผล
 */
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

    public static function timestamp(?DateTimeInterface $moment, bool $withTime = true, string $empty = '-'): string
    {
        return self::format(self::toDisplayTimezone($moment), $withTime, $empty);
    }

    public static function time(?DateTimeInterface $moment, string $empty = '-'): string
    {
        return self::toDisplayTimezone($moment)?->format('H:i:s') ?? $empty;
    }

    public static function timezone(): string
    {
        return config('app.display_timezone', 'Asia/Bangkok');
    }

    public static function now(): Carbon
    {
        return Carbon::now(self::timezone());
    }

    /**
     * จุดเริ่มต้นของวัน (ตามเวลาที่แสดงผล) ในเขตเวลาที่ระบบใช้เก็บข้อมูล สำหรับใช้กรอง timestamp
     */
    public static function startOfDay(string $date): ?Carbon
    {
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return null;
        }

        [$year, $month, $day] = array_map('intval', explode('-', $date));
        if (! checkdate($month, $day, $year)) {
            return null;
        }

        return Carbon::create($year, $month, $day, 0, 0, 0, self::timezone())
            ->setTimezone(config('app.timezone'));
    }

    public static function startOfToday(): Carbon
    {
        return self::now()->startOfDay()->setTimezone(config('app.timezone'));
    }

    private static function toDisplayTimezone(?DateTimeInterface $moment): ?Carbon
    {
        return $moment === null ? null : Carbon::instance($moment)->setTimezone(self::timezone());
    }
}
