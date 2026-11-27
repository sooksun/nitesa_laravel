<?php

namespace App\Enums;

enum ExamScope: string
{
    case SCHOOL = 'SCHOOL';
    case AREA = 'AREA';
    case PROVINCE = 'PROVINCE';
    case OBEC = 'OBEC';
    case NATIONAL = 'NATIONAL';

    public function label(): string
    {
        return match ($this) {
            self::SCHOOL => 'โรงเรียน',
            self::AREA => 'เขตพื้นที่',
            self::PROVINCE => 'จังหวัด',
            self::OBEC => 'สังกัด สพฐ.',
            self::NATIONAL => 'ประเทศ',
        };
    }
}
