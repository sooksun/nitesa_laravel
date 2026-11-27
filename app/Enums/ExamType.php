<?php

namespace App\Enums;

enum ExamType: string
{
    case RT = 'RT';
    case NT = 'NT';
    case ONET_P6 = 'ONET_P6';
    case ONET_M3 = 'ONET_M3';

    public function label(): string
    {
        return match ($this) {
            self::RT => 'RT ป.1',
            self::NT => 'NT ป.3',
            self::ONET_P6 => 'O-NET ป.6',
            self::ONET_M3 => 'O-NET ม.3',
        };
    }

    /**
     * Score columns of this exam in display order (column => Thai label).
     *
     * @return array<string, string>
     */
    public function subjects(): array
    {
        return match ($this) {
            self::RT => ['readAloud' => 'อ่านออกเสียง', 'readComprehension' => 'อ่านรู้เรื่อง'],
            self::NT => ['math' => 'คณิตศาสตร์', 'thai' => 'ภาษาไทย'],
            self::ONET_P6, self::ONET_M3 => [
                'thai' => 'ภาษาไทย',
                'english' => 'ภาษาอังกฤษ',
                'math' => 'คณิตศาสตร์',
                'science' => 'วิทยาศาสตร์',
            ],
        };
    }

    public function hasExaminees(): bool
    {
        return $this === self::RT || $this === self::NT;
    }

    public function hasQualityLevel(): bool
    {
        return $this === self::RT || $this === self::NT;
    }

    public function isOnet(): bool
    {
        return $this === self::ONET_P6 || $this === self::ONET_M3;
    }

    public function slug(): string
    {
        return match ($this) {
            self::RT => 'rt',
            self::NT => 'nt',
            self::ONET_P6 => 'onet-p6',
            self::ONET_M3 => 'onet-m3',
        };
    }

    public static function fromSlug(string $slug): self
    {
        $slug = strtolower(str_replace('_', '-', trim($slug)));

        foreach (self::cases() as $case) {
            if ($case->slug() === $slug) {
                return $case;
            }
        }

        throw new \ValueError("ไม่รู้จักประเภทการสอบ: {$slug}");
    }
}
