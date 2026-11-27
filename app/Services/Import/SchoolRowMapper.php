<?php

namespace App\Services\Import;

class SchoolRowMapper
{
    private const PLACEHOLDERS = ['ไม่มีข้อมูล', 'ไม่มี', 'ไม่ระบุ', '-', 'n/a'];

    /** @var array<string, string> lower-cased group code or name => group id */
    private array $groupIds = [];

    /**
     * @param iterable<array{id: string, code: ?string, name: ?string}> $networkGroups
     */
    public function __construct(iterable $networkGroups)
    {
        foreach ($networkGroups as $group) {
            foreach ([$group['code'] ?? null, $group['name'] ?? null] as $key) {
                $key = $this->key($key);
                if ($key !== '') {
                    $this->groupIds[$key] = (string) $group['id'];
                }
            }
        }
    }

    /**
     * @param array<string, mixed> $data one spreadsheet row keyed by its header
     *
     * @return array{code: string, attributes: array<string, mixed>}
     */
    public function map(array $data): array
    {
        $code = $this->text($data['code'] ?? null);
        $name = $this->text($data['name'] ?? null);
        if ($code === null || $name === null) {
            throw new \InvalidArgumentException('ข้อมูลไม่ครบถ้วน (ต้องมี code และ name)');
        }

        $groupGiven = array_key_exists('networkGroupCode', $data) || array_key_exists('network_group_code', $data);
        $group = $this->text($data['networkGroupCode'] ?? $data['network_group_code'] ?? null);
        $address = $this->text($data['address'] ?? null);

        $attributes = [
            'name' => $name,
            'province' => $this->province($data['province'] ?? null),
            'district' => $this->text($data['district'] ?? null) ?? '',
            'subDistrict' => $this->text($data['subDistrict'] ?? $data['sub_district'] ?? null),
            'address' => $address !== null && $address === $group ? null : $address,
            'phone' => $this->phone($data['phone'] ?? null),
            'email' => $this->text($data['email'] ?? null),
            'principalName' => $this->text($data['principalName'] ?? $data['principal_name'] ?? null),
            'studentCount' => (int) ($this->text($data['studentCount'] ?? $data['student_count'] ?? null) ?? 0),
            'teacherCount' => (int) ($this->text($data['teacherCount'] ?? $data['teacher_count'] ?? null) ?? 0),
        ];

        if ($groupGiven) {
            if ($group !== null && ! isset($this->groupIds[$this->key($group)])) {
                throw new \InvalidArgumentException("ไม่พบกลุ่มเครือข่าย: {$group} (นำเข้ากลุ่มเครือข่ายก่อน)");
            }
            $attributes['networkGroupId'] = $group === null ? null : $this->groupIds[$this->key($group)];
        }

        return ['code' => $code, 'attributes' => $attributes];
    }

    private function text(mixed $value): ?string
    {
        if (is_float($value) && floor($value) === $value) {
            $value = (int) $value;
        }

        $text = trim((string) $value);

        return $text === '' || in_array(mb_strtolower($text), self::PLACEHOLDERS, true) ? null : $text;
    }

    private function province(mixed $value): ?string
    {
        $province = $this->text($value);

        return $province === null ? null : trim((string) preg_replace('/^จังหวัด/u', '', $province));
    }

    /**
     * Excel stores an all-digit phone number as a number and drops its leading zero.
     */
    private function phone(mixed $value): ?string
    {
        $phone = $this->text($value);
        if ($phone !== null && preg_match('/^[1-9]\d{7,8}$/', $phone) === 1) {
            return '0' . $phone;
        }

        return $phone;
    }

    private function key(mixed $value): string
    {
        return mb_strtolower(trim((string) $value));
    }
}
