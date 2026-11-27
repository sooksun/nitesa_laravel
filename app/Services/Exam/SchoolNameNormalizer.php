<?php

namespace App\Services\Exam;

class SchoolNameNormalizer
{
    private const THAI_DIGITS = ['๐', '๑', '๒', '๓', '๔', '๕', '๖', '๗', '๘', '๙'];

    public static function normalize(?string $name): string
    {
        $name = strtr((string) $name, array_combine(self::THAI_DIGITS, range(0, 9)));

        return (string) preg_replace('/[\s"\x{27}\x{201C}\x{201D}\x{2018}\x{2019}\x{200B}-\x{200D}\x{FEFF}().]|โรงเรียน/u', '', $name);
    }
}
