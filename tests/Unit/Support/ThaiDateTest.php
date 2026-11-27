<?php

namespace Tests\Unit\Support;

use App\Support\ThaiDate;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

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
    }
}
