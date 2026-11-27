<?php

namespace Tests\Unit\Exam;

use App\Services\Exam\NtQualityLevel;
use PHPUnit\Framework\TestCase;

class NtQualityLevelTest extends TestCase
{
    public function test_thresholds_follow_the_nt_criteria_sheet(): void
    {
        $this->assertSame('ดีมาก', NtQualityLevel::fromAverage(68.5));
        $this->assertSame('ดี', NtQualityLevel::fromAverage(68.49));
        $this->assertSame('ดี', NtQualityLevel::fromAverage(47.0));
        $this->assertSame('พอใช้', NtQualityLevel::fromAverage(46.99));
        $this->assertSame('พอใช้', NtQualityLevel::fromAverage(27.5));
        $this->assertSame('ปรับปรุง', NtQualityLevel::fromAverage(27.49));
    }

    public function test_missing_average_has_no_level(): void
    {
        $this->assertNull(NtQualityLevel::fromAverage(null));
    }
}
