<?php

namespace App\Enums;

enum Role: string
{
    case ADMIN = 'ADMIN';
    case SUPERVISOR = 'SUPERVISOR';
    case SCHOOL = 'SCHOOL';
    case EXECUTIVE = 'EXECUTIVE';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'ผู้ดูแลระบบ',
            self::SUPERVISOR => 'ศึกษานิเทศก์',
            self::SCHOOL => 'โรงเรียน',
            self::EXECUTIVE => 'ผู้บริหาร',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ADMIN => 'red',
            self::SUPERVISOR => 'blue',
            self::SCHOOL => 'green',
            self::EXECUTIVE => 'purple',
        };
    }
}
