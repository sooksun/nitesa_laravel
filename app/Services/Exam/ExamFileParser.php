<?php

namespace App\Services\Exam;

use App\Enums\ExamScope;
use App\Enums\ExamType;

class ExamFileParser
{
    private const HEADER_DEPTH = 3;

    private const BENCHMARK_PREFIXES = [
        'ระดับเขตพื้นที่' => ExamScope::AREA,
        'ระดับจังหวัด' => ExamScope::PROVINCE,
        'ระดับสังกัด' => ExamScope::OBEC,
        'ระดับประเทศ' => ExamScope::NATIONAL,
    ];

    /**
     * @param array<int, array<int, mixed>> $rows first sheet of the file, raw cell values
     *
     * @param string|null $academicYear when given, a file whose title states another year is rejected
     *
     * @return array{schools: list<array<string, mixed>>, benchmarks: list<array<string, mixed>>, skipped: list<array{row: int, name: string}>}
     */
    public function parse(ExamType $type, array $rows, ?string $academicYear = null): array
    {
        $rows = array_values($rows);
        $headerIndex = $this->assertHeaderMatches($type, $rows);
        $this->assertTitleMatches($type, array_slice($rows, 0, $headerIndex), $academicYear);

        $layout = $this->layout($type);
        $schools = [];
        $benchmarks = [];
        $skipped = [];

        foreach ($rows as $index => $row) {
            $row = array_values($row);
            $name = $this->text($row[$layout['name']] ?? null);

            if (is_numeric($this->text($row[$layout['seq']] ?? null)) && $name !== null) {
                $measures = $this->measures($type, $row, $layout);
                $schools[] = [
                    'row' => $index + 1,
                    'code' => $layout['code'] === null ? null : $this->text($row[$layout['code']] ?? null),
                    'name' => $name,
                    'district' => $layout['district'] === null ? null : $this->text($row[$layout['district']] ?? null),
                ] + $measures;

                continue;
            }

            $scope = $this->benchmarkScope($row);
            if ($scope !== null) {
                $benchmarks[] = ['scope' => $scope, 'name' => $this->firstText($row)] + $this->measures($type, $row, $layout);

                continue;
            }

            if ($index > $headerIndex && $name !== null && $name !== 'ชื่อโรงเรียน') {
                $measures = $this->measures($type, $row, $layout);
                if ($measures['average'] !== null || array_filter($measures['scores'], fn($score) => $score !== null) !== []) {
                    $skipped[] = ['row' => $index + 1, 'name' => $name];
                }
            }
        }

        return ['schools' => $schools, 'benchmarks' => $benchmarks, 'skipped' => $skipped];
    }

    /**
     * The report title names the grade and academic year; both must agree with what was selected.
     *
     * @param array<int, array<int, mixed>> $titleRows
     */
    private function assertTitleMatches(ExamType $type, array $titleRows, ?string $academicYear): void
    {
        $title = '';
        foreach ($titleRows as $row) {
            $title .= implode(' ', array_map(fn($cell) => (string) $this->text($cell), $row)) . ' ';
        }

        $expectedGrade = match ($type) {
            ExamType::RT => 'ประถมศึกษาปีที่ 1',
            ExamType::NT => 'ประถมศึกษาปีที่ 3',
            ExamType::ONET_P6 => 'ประถมศึกษาปีที่ 6',
            ExamType::ONET_M3 => 'มัธยมศึกษาปีที่ 3',
        };

        $grade = preg_match('/(ประถมศึกษา|มัธยมศึกษา)ปีที่\s*(\d)/u', $title, $found) === 1 ? "{$found[1]}ปีที่ {$found[2]}" : null;
        if ($grade === null && $type->isOnet()) {
            throw new InvalidExamFileException("ไม่พบระดับชั้นในหัวรายงานของไฟล์ จึงยืนยันไม่ได้ว่าเป็นผล {$type->label()}");
        }
        if ($grade !== null && $grade !== $expectedGrade) {
            throw new InvalidExamFileException("ไฟล์นี้เป็นผลการทดสอบชั้น{$grade} ไม่ตรงกับประเภทที่เลือก ({$type->label()})");
        }

        if ($academicYear !== null
            && preg_match('/ปีการศึกษา\s*(\d{4})/u', $title, $found) === 1
            && $found[1] !== $academicYear) {
            throw new InvalidExamFileException("ไฟล์นี้เป็นผลการทดสอบปีการศึกษา {$found[1]} ไม่ตรงกับปีการศึกษาที่เลือก ({$academicYear})");
        }
    }

    /**
     * @return array{seq: int, code: ?int, name: int, district: ?int, examinees: ?int, scores: array<string, int>, average: int, level: ?int}
     */
    private function layout(ExamType $type): array
    {
        return match ($type) {
            ExamType::RT => [
                'seq' => 0, 'code' => 1, 'name' => 2, 'district' => 3, 'examinees' => 4,
                'scores' => ['readAloud' => 5, 'readComprehension' => 6], 'average' => 7, 'level' => 8,
            ],
            ExamType::NT => [
                'seq' => 1, 'code' => null, 'name' => 2, 'district' => 3, 'examinees' => 4,
                'scores' => ['math' => 5, 'thai' => 6], 'average' => 7, 'level' => null,
            ],
            ExamType::ONET_P6, ExamType::ONET_M3 => [
                'seq' => 0, 'code' => 1, 'name' => 2, 'district' => null, 'examinees' => null,
                'scores' => ['thai' => 3, 'english' => 4, 'math' => 5, 'science' => 6], 'average' => 7, 'level' => null,
            ],
        };
    }

    /**
     * @param array<int, array<int, mixed>> $rows
     */
    private function assertHeaderMatches(ExamType $type, array $rows): int
    {
        $headerIndex = null;
        foreach ($rows as $index => $row) {
            if (($this->text(array_values($row)[2] ?? null)) === 'ชื่อโรงเรียน') {
                $headerIndex = $index;

                break;
            }
        }

        if ($headerIndex === null) {
            throw new InvalidExamFileException('ไม่พบหัวตาราง "ชื่อโรงเรียน" ในไฟล์ กรุณาตรวจสอบว่าเป็นไฟล์ผลการทดสอบที่ถูกต้อง');
        }

        $header = '';
        foreach (array_slice($rows, $headerIndex, self::HEADER_DEPTH) as $row) {
            $header .= implode('|', array_map(fn($cell) => (string) $this->text($cell), $row)) . '|';
        }

        $isRt = str_contains($header, 'อ่านออกเสียง');
        $isOnet = str_contains($header, 'ภาษาอังกฤษ');
        $isNt = ! $isRt && ! $isOnet && str_contains($header, 'คณิตศาสตร์') && ! str_contains($header, 'รหัสโรงเรียน');

        $matches = match ($type) {
            ExamType::RT => $isRt,
            ExamType::NT => $isNt,
            ExamType::ONET_P6, ExamType::ONET_M3 => $isOnet,
        };

        if (! $matches) {
            throw new InvalidExamFileException("หัวตารางของไฟล์ไม่ตรงกับรูปแบบผลการทดสอบ {$type->label()} กรุณาตรวจสอบประเภทที่เลือก");
        }

        return $headerIndex;
    }

    /**
     * @param array<int, mixed> $row
     * @param array<string, mixed> $layout
     *
     * @return array{examinees: ?int, scores: array<string, ?float>, average: ?float, qualityLevel: ?string}
     */
    private function measures(ExamType $type, array $row, array $layout): array
    {
        $scores = [];
        foreach ($layout['scores'] as $subject => $column) {
            $scores[$subject] = $this->number($row[$column] ?? null);
        }

        $average = $this->number($row[$layout['average']] ?? null);
        $examinees = $layout['examinees'] === null ? null : $this->number($row[$layout['examinees']] ?? null);

        $level = match (true) {
            $type === ExamType::NT => NtQualityLevel::fromAverage($average),
            $layout['level'] !== null => $this->text($row[$layout['level']] ?? null),
            default => null,
        };

        return [
            'examinees' => $examinees === null ? null : (int) $examinees,
            'scores' => $scores,
            'average' => $average,
            'qualityLevel' => $level,
        ];
    }

    /**
     * @param array<int, mixed> $row
     */
    private function benchmarkScope(array $row): ?ExamScope
    {
        $label = $this->firstText($row);
        if ($label === null) {
            return null;
        }

        foreach (self::BENCHMARK_PREFIXES as $prefix => $scope) {
            if (str_starts_with($label, $prefix)) {
                return $scope;
            }
        }

        return null;
    }

    /**
     * @param array<int, mixed> $row
     */
    private function firstText(array $row): ?string
    {
        foreach ($row as $cell) {
            $text = $this->text($cell);
            if ($text !== null) {
                return $text;
            }
        }

        return null;
    }

    private function text(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_float($value) && floor($value) === $value) {
            $value = (int) $value;
        }

        $text = trim((string) $value);

        return $text === '' ? null : $text;
    }

    private function number(mixed $value): ?float
    {
        if (is_string($value)) {
            $value = trim($value);
        }

        return is_numeric($value) ? round((float) $value, 2) : null;
    }
}
