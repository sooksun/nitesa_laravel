<?php

namespace Tests\Unit\Exam;

use App\Services\Exam\SchoolNameNormalizer;
use PHPUnit\Framework\TestCase;

class SchoolNameNormalizerTest extends TestCase
{
    public function test_strips_school_prefix_and_parentheses(): void
    {
        $this->assertSame(
            'บ้านนาคุรุราษฎรอุทิศวิทยายน',
            SchoolNameNormalizer::normalize('โรงเรียนบ้านนา (คุรุราษฎรอุทิศวิทยายน)')
        );
    }

    public function test_strips_quotes_and_whitespace(): void
    {
        $this->assertSame('บ้านกลางผดุงราษฎร์วิทยา', SchoolNameNormalizer::normalize(' บ้านกลาง "ผดุงราษฎร์วิทยา" '));
    }

    public function test_thai_digits_equal_arabic_digits(): void
    {
        $this->assertSame(
            SchoolNameNormalizer::normalize('ไทยรัฐวิทยา 110 (บ้านชมภูพาน)'),
            SchoolNameNormalizer::normalize('ไทยรัฐวิทยา ๑๑๐ (บ้านชมภูพาน)')
        );
    }

    public function test_strips_zero_width_characters(): void
    {
        $this->assertSame('บ้านนาดี', SchoolNameNormalizer::normalize("บ้าน\u{200B}นา\u{200C}ดี\u{FEFF}"));
    }

    public function test_null_becomes_empty_string(): void
    {
        $this->assertSame('', SchoolNameNormalizer::normalize(null));
    }
}
