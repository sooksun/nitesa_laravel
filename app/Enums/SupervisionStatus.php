<?php

namespace App\Enums;

enum SupervisionStatus: string
{
    case DRAFT = 'DRAFT';
    case SUBMITTED = 'SUBMITTED';
    case APPROVED = 'APPROVED';
    case PUBLISHED = 'PUBLISHED';
    case NEEDS_IMPROVEMENT = 'NEEDS_IMPROVEMENT';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'ร่าง',
            self::SUBMITTED => 'ส่งแล้ว',
            self::APPROVED => 'อนุมัติแล้ว',
            self::PUBLISHED => 'เผยแพร่แล้ว',
            self::NEEDS_IMPROVEMENT => 'ต้องปรับปรุง',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::DRAFT => 'gray',
            self::SUBMITTED => 'yellow',
            self::APPROVED => 'blue',
            self::PUBLISHED => 'green',
            self::NEEDS_IMPROVEMENT => 'red',
        };
    }

    public function bgClass(): string
    {
        return match ($this) {
            self::DRAFT => 'bg-gray-100 text-gray-800',
            self::SUBMITTED => 'bg-yellow-100 text-yellow-800',
            self::APPROVED => 'bg-blue-100 text-blue-800',
            self::PUBLISHED => 'bg-green-100 text-green-800',
            self::NEEDS_IMPROVEMENT => 'bg-red-100 text-red-800',
        };
    }
}
