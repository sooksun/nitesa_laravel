<?php

namespace App\Services;

use App\Enums\ExamScope;
use App\Enums\ExamType;
use App\Models\ExamResult;
use App\Models\School;
use App\Services\Exam\ExamFileParser;
use App\Services\Exam\InvalidExamFileException;
use App\Services\Exam\SchoolMatcher;
use App\Services\Exam\SchoolNameNormalizer;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ExamResultImporter
{
    public function __construct(private ExamFileParser $parser) {}

    /**
     * @return array{
     *     total: int,
     *     imported: int,
     *     matchedBy: array{code: int, name: int, alias: int},
     *     schoolsCreated: list<string>,
     *     unmatched: list<array{row: int, name: string, reason: string}>,
     *     nameDiffs: list<array{row: int, fileName: string, systemName: string, by: string}>,
     *     benchmarks: int,
     *     dryRun: bool
     * }
     *
     * @throws InvalidExamFileException
     */
    public function import(
        ExamType $type,
        string $path,
        string $academicYear,
        bool $syncSchools = false,
        bool $dryRun = false,
    ): array {
        $parsed = $this->parser->parse($type, $this->readRows($path), $academicYear);
        $syncSchools = $syncSchools && $type === ExamType::RT;

        return DB::transaction(function () use ($type, $academicYear, $syncSchools, $dryRun, $parsed) {
            $schools = School::query()->get(['id', 'code', 'name', 'district']);
            $matcher = new SchoolMatcher($schools->toArray(), config('exam.aliases', []));
            $missingDistrict = $schools->filter(fn(School $s) => blank($s->district))->keyBy('id');

            $summary = [
                'total' => count($parsed['schools']) + count($parsed['skipped']),
                'imported' => 0,
                'matchedBy' => ['code' => 0, 'name' => 0, 'alias' => 0],
                'schoolsCreated' => [],
                'unmatched' => [],
                'nameDiffs' => [],
                'benchmarks' => count($parsed['benchmarks']),
                'dryRun' => $dryRun,
            ];
            $usedBy = [];

            foreach ($parsed['schools'] as $index => $row) {
                $match = $matcher->match($row['code'], $row['name'], $row['district']);
                $schoolId = $match['schoolId'];

                if ($match['status'] === 'ambiguous') {
                    $summary['unmatched'][] = $this->unmatched($row, 'พบโรงเรียนในระบบที่เข้าข่ายมากกว่าหนึ่งแห่ง');

                    continue;
                }

                if ($match['status'] === 'none') {
                    if (! $syncSchools || blank($row['code'])) {
                        $summary['unmatched'][] = $this->unmatched($row, 'ไม่พบโรงเรียนนี้ในระบบ');

                        continue;
                    }

                    $blocker = $matcher->creationBlocker($row['name']);
                    if ($blocker !== null) {
                        $summary['unmatched'][] = $this->unmatched($row, "ไม่เพิ่มเป็นโรงเรียนใหม่ เพราะ{$blocker}");

                        continue;
                    }

                    $schoolId = $dryRun ? "dry-run-{$index}" : $this->createSchool($row)->id;
                    $matcher->add(
                        ['id' => $schoolId, 'code' => $row['code'], 'name' => $row['name'], 'district' => $row['district']],
                        true,
                    );
                    $summary['schoolsCreated'][] = $row['name'];
                } else {
                    if (isset($usedBy[$schoolId])) {
                        $summary['unmatched'][] = $this->unmatched($row, "จับคู่ได้โรงเรียนเดียวกับแถว {$usedBy[$schoolId]}");

                        continue;
                    }

                    $summary['matchedBy'][$match['by']]++;
                    $systemName = (string) $matcher->nameOf($schoolId);
                    if (SchoolNameNormalizer::normalize($systemName) !== SchoolNameNormalizer::normalize($row['name'])) {
                        $summary['nameDiffs'][] = [
                            'row' => $row['row'],
                            'fileName' => $row['name'],
                            'systemName' => $systemName,
                            'by' => $match['by'],
                        ];
                    }
                }

                $usedBy[$schoolId] = $row['row'];
                $summary['imported']++;

                if ($dryRun) {
                    continue;
                }

                if ($syncSchools && filled($row['district']) && $missingDistrict->has($schoolId)) {
                    $missingDistrict->get($schoolId)->update(['district' => $row['district']]);
                }

                $this->store($type, $academicYear, ExamScope::SCHOOL, $schoolId, $row, $row['code'], $row['name']);
            }

            foreach ($parsed['skipped'] as $row) {
                $summary['unmatched'][] = ['row' => $row['row'], 'name' => $row['name'], 'reason' => 'ไม่มีเลขลำดับในไฟล์ จึงไม่ถูกนำเข้า'];
            }

            if (! $dryRun) {
                foreach ($parsed['benchmarks'] as $row) {
                    $this->store($type, $academicYear, $row['scope'], null, $row, null, $row['name']);
                }
            }

            return $summary;
        });
    }

    /**
     * @return array<int, array<int, mixed>>
     */
    private function readRows(string $path): array
    {
        if (! is_file($path)) {
            throw new InvalidExamFileException("ไม่พบไฟล์: {$path}");
        }

        try {
            return IOFactory::load($path)->getSheet(0)->toArray(null, true, false, false);
        } catch (\Throwable $e) {
            throw new InvalidExamFileException('ไม่สามารถอ่านไฟล์ได้ กรุณาตรวจสอบว่าเป็นไฟล์ Excel (.xlsx หรือ .xls)', 0, $e);
        }
    }

    /**
     * @param array<string, mixed> $row
     */
    private function createSchool(array $row): School
    {
        return School::create([
            'code' => $row['code'],
            'name' => $row['name'],
            'district' => $row['district'] ?? '',
            'province' => 'สกลนคร',
        ]);
    }

    /**
     * @param array<string, mixed> $row
     */
    private function store(
        ExamType $type,
        string $academicYear,
        ExamScope $scope,
        ?string $schoolId,
        array $row,
        ?string $sourceCode,
        string $sourceName,
    ): void {
        ExamResult::updateOrCreate(
            [
                'examType' => $type->value,
                'academicYear' => $academicYear,
                'scope' => $scope->value,
                'schoolId' => $schoolId,
            ],
            array_merge(array_fill_keys(ExamResult::SCORE_COLUMNS, null), $row['scores'], [
                'examinees' => $row['examinees'],
                'average' => $row['average'],
                'qualityLevel' => $row['qualityLevel'],
                'sourceCode' => $sourceCode,
                'sourceName' => $sourceName,
            ]),
        );
    }

    /**
     * @param array<string, mixed> $row
     *
     * @return array{row: int, name: string, reason: string}
     */
    private function unmatched(array $row, string $reason): array
    {
        return ['row' => $row['row'], 'name' => $row['name'], 'reason' => $reason];
    }
}
