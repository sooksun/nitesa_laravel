<?php

namespace App\Services\Exam;

class SchoolMatcher
{
    private const SIMILAR_DISTANCE = 2;
    private const SIMILAR_MIN_LENGTH = 6;

    /** @var array<string, array{id: string, district: string}> */
    private array $byCode = [];

    /** @var array<string, list<array{id: string, district: string}>> */
    private array $byName = [];

    /** @var array<string, string> school id => name as stored */
    private array $names = [];

    /** @var array<string, string> normalized name => name as stored, schools that existed before this import */
    private array $existingNames = [];

    /** @var array<string, string> */
    private array $aliases = [];

    /**
     * @param iterable<array{id: string, code: ?string, name: ?string, district: ?string}> $schools
     * @param array<string, string> $aliases file name => system name
     */
    public function __construct(iterable $schools, array $aliases = [])
    {
        foreach ($schools as $school) {
            $this->add($school);
        }

        foreach ($aliases as $from => $to) {
            $this->aliases[SchoolNameNormalizer::normalize($from)] = SchoolNameNormalizer::normalize($to);
        }
    }

    /**
     * @param array{id: string, code: ?string, name: ?string, district: ?string} $school
     * @param bool $createdNow true for a school created by the running import
     */
    public function add(array $school, bool $createdNow = false): void
    {
        $id = (string) $school['id'];
        $entry = ['id' => $id, 'district' => $this->district($school['district'] ?? null)];
        $this->names[$id] = (string) ($school['name'] ?? '');

        $code = trim((string) ($school['code'] ?? ''));
        if ($code !== '') {
            $this->byCode[$code] = $entry;
        }

        $name = SchoolNameNormalizer::normalize($school['name'] ?? null);
        if ($name !== '') {
            $this->byName[$name][] = $entry;
            if (! $createdNow) {
                $this->existingNames[$name] = $this->names[$id];
            }
        }
    }

    /**
     * @return array{status: 'matched'|'none'|'ambiguous', schoolId: ?string, by: ?string}
     */
    public function match(?string $code, string $name, ?string $district): array
    {
        $normalized = SchoolNameNormalizer::normalize($name);
        $byName = $this->candidates($normalized, $district);

        $code = trim((string) $code);
        if ($code !== '' && isset($this->byCode[$code])) {
            $id = $this->byCode[$code]['id'];
            if (count($byName) === 1 && $byName[0]['id'] !== $id) {
                return $this->result('ambiguous');
            }

            return $this->result('matched', $id, 'code');
        }

        if (count($byName) === 1) {
            return $this->result('matched', $byName[0]['id'], 'name');
        }
        if (count($byName) > 1) {
            return $this->result('ambiguous');
        }

        if (isset($this->aliases[$normalized])) {
            $byAlias = $this->candidates($this->aliases[$normalized], $district);
            if (count($byAlias) === 1) {
                return $this->result('matched', $byAlias[0]['id'], 'alias');
            }
            if (count($byAlias) > 1) {
                return $this->result('ambiguous');
            }
        }

        return $this->result('none');
    }

    public function nameOf(string $schoolId): ?string
    {
        return $this->names[$schoolId] ?? null;
    }

    /**
     * Why an unmatched file row must not become a new school: a school that already existed
     * carries the same or a nearly identical name, so creating one would likely duplicate it.
     */
    public function creationBlocker(string $name): ?string
    {
        $normalized = SchoolNameNormalizer::normalize($name);

        if (isset($this->existingNames[$normalized])) {
            return "มีโรงเรียนชื่อเดียวกันในระบบ ({$this->existingNames[$normalized]}) แต่อำเภอไม่ตรงกัน";
        }

        $letters = mb_str_split($normalized);
        if (count($letters) < self::SIMILAR_MIN_LENGTH) {
            return null;
        }

        $similar = [];
        foreach ($this->existingNames as $existing => $stored) {
            if ($this->distance($letters, mb_str_split($existing)) <= self::SIMILAR_DISTANCE) {
                $similar[] = $stored;
            }
        }

        return $similar === [] ? null : 'ชื่อใกล้เคียงกับโรงเรียนในระบบ: ' . implode(', ', $similar);
    }

    /**
     * @return list<array{id: string, district: string}>
     */
    private function candidates(string $normalizedName, ?string $district): array
    {
        $district = $this->district($district);

        return array_values(array_filter(
            $this->byName[$normalizedName] ?? [],
            fn(array $school) => $this->sameDistrict($school['district'], $district)
        ));
    }

    private function sameDistrict(string $a, string $b): bool
    {
        return $a === '' || $b === '' || str_starts_with($a, $b) || str_starts_with($b, $a);
    }

    private function district(?string $district): string
    {
        return (string) preg_replace('/\s+|^อำเภอ|^อ\./u', '', trim((string) $district));
    }

    /**
     * Levenshtein distance over characters, capped just above the similarity threshold.
     *
     * @param list<string> $a
     * @param list<string> $b
     */
    private function distance(array $a, array $b): int
    {
        if (abs(count($a) - count($b)) > self::SIMILAR_DISTANCE) {
            return self::SIMILAR_DISTANCE + 1;
        }

        $previous = range(0, count($b));
        foreach ($a as $i => $charA) {
            $current = [$i + 1];
            foreach ($b as $j => $charB) {
                $current[] = min(
                    $previous[$j + 1] + 1,
                    $current[$j] + 1,
                    $previous[$j] + ($charA === $charB ? 0 : 1),
                );
            }
            $previous = $current;
        }

        return $previous[count($b)];
    }

    /**
     * @return array{status: 'matched'|'none'|'ambiguous', schoolId: ?string, by: ?string}
     */
    private function result(string $status, ?string $schoolId = null, ?string $by = null): array
    {
        return ['status' => $status, 'schoolId' => $schoolId, 'by' => $by];
    }
}
