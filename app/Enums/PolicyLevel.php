<?php

namespace App\Enums;

enum PolicyLevel: string
{
    case MINISTER = 'MINISTER';
    case OBEC = 'OBEC';
    case AREA = 'AREA';

    public function label(): string
    {
        return match ($this) {
            self::MINISTER => 'นโยบายกระทรวง',
            self::OBEC => 'นโยบาย สพฐ.',
            self::AREA => 'นโยบายเขตพื้นที่',
        };
    }

    /**
     * Single-value column on `supervision` kept in step with the first selected policy.
     */
    public function legacyColumn(): string
    {
        return match ($this) {
            self::MINISTER => 'ministerPolicyId',
            self::OBEC => 'obecPolicyId',
            self::AREA => 'areaPolicyId',
        };
    }

    public function idsKey(): string
    {
        return $this->legacyColumn() . 's';
    }

    public function policiesKey(): string
    {
        return match ($this) {
            self::MINISTER => 'ministerPolicies',
            self::OBEC => 'obecPolicies',
            self::AREA => 'areaPolicies',
        };
    }
}
