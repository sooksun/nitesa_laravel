<?php

namespace Tests\Unit\Support;

use App\Support\ThaiDate;
use Carbon\Carbon;
use Tests\TestCase;

class ThaiDateTest extends TestCase
{
    public function test_formats_date_in_buddhist_era(): void
    {
        $this->assertSame('27/11/2569', ThaiDate::format(Carbon::parse('2026-11-27 14:05:09')));
        $this->assertSame('01/01/2570', ThaiDate::format(Carbon::parse('2027-01-01')));
    }

    public function test_formats_date_with_time(): void
    {
        $this->assertSame('05/03/2563 09:07', ThaiDate::format(Carbon::parse('2020-03-05 09:07:59'), true));
    }

    public function test_null_date_uses_placeholder(): void
    {
        $this->assertSame('-', ThaiDate::format(null));
        $this->assertSame('', ThaiDate::format(null, true, ''));
        $this->assertSame('-', ThaiDate::timestamp(null));
        $this->assertSame('-', ThaiDate::time(null));
    }

    public function test_timestamp_is_shown_in_thai_time(): void
    {
        $moment = Carbon::parse('2026-12-31 18:30:45', 'UTC');

        $this->assertSame('01/01/2570 01:30', ThaiDate::timestamp($moment));
        $this->assertSame('01/01/2570', ThaiDate::timestamp($moment, false));
        $this->assertSame('01:30:45', ThaiDate::time($moment));
        $this->assertSame('2026-12-31 18:30:45', $moment->format('Y-m-d H:i:s'));
    }

    public function test_relative_time_is_in_thai(): void
    {
        Carbon::setTestNow('2026-11-28 12:00:00');

        $this->assertSame('10 เดือนที่แล้ว', ThaiDate::ago(Carbon::parse('2026-01-21 03:48:24')));
        $this->assertSame('5 นาทีที่แล้ว', ThaiDate::ago(Carbon::parse('2026-11-28 11:55:00')));
        $this->assertSame('-', ThaiDate::ago(null));
        $this->assertSame('en', app()->getLocale());

        Carbon::setTestNow();
    }

    public function test_calendar_date_is_not_shifted(): void
    {
        $this->assertSame('31/12/2569', ThaiDate::format(Carbon::parse('2026-12-31 18:30:45', 'UTC')));
    }

    public function test_start_of_day_is_thai_midnight_in_storage_timezone(): void
    {
        $this->assertSame('2026-11-26 17:00:00', ThaiDate::startOfDay('2026-11-27')->format('Y-m-d H:i:s'));
        $this->assertNull(ThaiDate::startOfDay(''));
        $this->assertNull(ThaiDate::startOfDay('2026-02-30'));
        $this->assertNull(ThaiDate::startOfDay('27/11/2569'));
    }
}
