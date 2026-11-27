<?php

namespace App\Livewire\ExamResult;

use App\Enums\ExamScope;
use App\Enums\ExamType;
use App\Models\ExamResult;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * @property-read Collection<int, string> $years
 * @property-read Collection<int, string> $districts
 * @property-read array<string, array{label: string, scores: array<string, ?float>, average: ?float, examinees: ?int, computed: bool}> $benchmarks
 * @property-read array{schools: int, examinees: ?int, average: ?float, scores: array<string, ?float>} $group
 * @property-read Collection<int, object> $districtAverages
 * @property-read array<string, int> $qualityCounts
 * @property-read array{label: string, average: ?float}|null $reference
 * @property-read array<string, mixed> $chartData
 */
class ExamResultDashboard extends Component
{
    use WithPagination;

    private const QUALITY_LEVELS = ['ดีมาก', 'ดี', 'พอใช้', 'ปรับปรุง'];

    #[Url(as: 'year')]
    public string $academicYear = '';

    #[Url(as: 'type')]
    public string $examType = 'ONET_P6';

    #[Url]
    public string $district = '';

    #[Url(as: 'q')]
    public string $search = '';

    public string $sortBy = 'average';
    public string $sortDir = 'desc';

    public function mount(): void
    {
        if (ExamType::tryFrom($this->examType) === null) {
            $this->examType = ExamType::ONET_P6->value;
        }

        if ($this->academicYear === '' || ! $this->years->contains($this->academicYear)) {
            $this->academicYear = (string) ($this->years->first() ?? ((int) date('Y') + 542));
        }
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['academicYear', 'examType', 'district', 'search'], true)) {
            $this->resetPage();
        }

        if ($property === 'examType') {
            if (ExamType::tryFrom($this->examType) === null) {
                $this->examType = ExamType::ONET_P6->value;
            }
            $this->sortBy = 'average';
            $this->sortDir = 'desc';
            $this->district = $this->districts->contains($this->district) ? $this->district : '';
        }
    }

    public function sort(string $column): void
    {
        if (! array_key_exists($column, $this->sortableColumns())) {
            return;
        }

        if ($this->sortBy === $column) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDir = in_array($column, ['name', 'district'], true) ? 'asc' : 'desc';
        }
        $this->resetPage();
    }

    public function type(): ExamType
    {
        return ExamType::from($this->examType);
    }

    /**
     * @return array<string, string> sort key => SQL column
     */
    private function sortableColumns(): array
    {
        $columns = ['name' => 'school.name', 'district' => 'school.district', 'average' => 'examresult.average'];
        foreach (array_keys($this->type()->subjects()) as $subject) {
            $columns[$subject] = "examresult.{$subject}";
        }
        if ($this->type()->hasExaminees()) {
            $columns['examinees'] = 'examresult.examinees';
        }

        return $columns;
    }

    /**
     * School rows of the selected exam and year that the current user may see.
     */
    private function scopedQuery(): Builder
    {
        $scoped = auth()->user()->scopedSchoolIds();

        return ExamResult::query()
            ->join('school', 'school.id', '=', 'examresult.schoolId')
            ->where('examresult.scope', ExamScope::SCHOOL->value)
            ->where('examresult.examType', $this->examType)
            ->where('examresult.academicYear', $this->academicYear)
            ->when($scoped !== null, fn(Builder $q) => $q->whereIn('examresult.schoolId', $scoped));
    }

    private function filteredQuery(): Builder
    {
        $search = trim($this->search);

        return $this->scopedQuery()
            ->when($this->district !== '', fn(Builder $q) => $q->where('school.district', $this->district))
            ->when($search !== '', fn(Builder $q) => $q->where('school.name', 'like', '%' . addcslashes($search, '%_\\') . '%'));
    }

    #[Computed]
    public function years(): Collection
    {
        return ExamResult::query()->distinct()->orderByDesc('academicYear')->pluck('academicYear');
    }

    #[Computed]
    public function districts(): Collection
    {
        return $this->scopedQuery()
            ->whereNotNull('school.district')
            ->where('school.district', '!=', '')
            ->distinct()
            ->orderBy('school.district')
            ->pluck('school.district');
    }

    /**
     * @return array<string, array{label: string, scores: array<string, ?float>, average: ?float, examinees: ?int, computed: bool}>
     */
    #[Computed]
    public function benchmarks(): array
    {
        return ExamResult::benchmarks($this->type(), $this->academicYear);
    }

    /**
     * @return array{schools: int, examinees: ?int, average: ?float, scores: array<string, ?float>}
     */
    #[Computed]
    public function group(): array
    {
        $row = $this->filteredQuery()->toBase()->select(ExamResult::aggregateColumns($this->type()))->first();
        $number = fn($value) => $value === null ? null : (float) $value;

        return [
            'schools' => (int) ($row->schoolCount ?? 0),
            'examinees' => $this->type()->hasExaminees() ? (int) ($row->examineeTotal ?? 0) : null,
            'average' => $number($row->average ?? null),
            'scores' => collect(array_keys($this->type()->subjects()))
                ->mapWithKeys(fn($subject) => [$subject => $number($row->{$subject} ?? null)])
                ->all(),
        ];
    }

    #[Computed]
    public function districtAverages(): Collection
    {
        return $this->filteredQuery()
            ->toBase()
            ->whereNotNull('school.district')
            ->where('school.district', '!=', '')
            ->groupBy('school.district')
            ->select(['school.district', ...ExamResult::aggregateColumns($this->type())])
            ->orderByDesc('average')
            ->get();
    }

    /**
     * @return array<string, int>
     */
    #[Computed]
    public function qualityCounts(): array
    {
        if (! $this->type()->hasQualityLevel()) {
            return [];
        }

        $counts = $this->filteredQuery()
            ->toBase()
            ->groupBy('examresult.qualityLevel')
            ->select(['examresult.qualityLevel', DB::raw('COUNT(*) AS total')])
            ->pluck('total', 'qualityLevel');

        return collect(self::QUALITY_LEVELS)->mapWithKeys(fn($level) => [$level => (int) ($counts[$level] ?? 0)])->all();
    }

    /**
     * The comparison the headline delta is measured against: national when published, else the area.
     *
     * @return array{label: string, average: ?float}|null
     */
    #[Computed]
    public function reference(): ?array
    {
        foreach ([ExamScope::NATIONAL, ExamScope::AREA] as $scope) {
            $benchmark = $this->benchmarks[$scope->value] ?? null;
            if ($benchmark !== null && $benchmark['average'] !== null) {
                return ['label' => $benchmark['label'], 'average' => $benchmark['average']];
            }
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    #[Computed]
    public function chartData(): array
    {
        $subjects = $this->type()->subjects();
        $series = [['key' => 'GROUP', 'label' => $this->groupLabel(), 'data' => [...array_values($this->group['scores']), $this->group['average']]]];
        foreach ($this->benchmarks as $scope => $benchmark) {
            $series[] = ['key' => $scope, 'label' => $benchmark['label'], 'data' => [...array_values($benchmark['scores']), $benchmark['average']]];
        }

        return [
            'subjects' => ['labels' => [...array_values($subjects), 'เฉลี่ยรวม'], 'series' => $series],
            'districts' => [
                'labels' => $this->districtAverages->pluck('district')->all(),
                'data' => $this->districtAverages->map(fn($row) => $row->average === null ? null : (float) $row->average)->all(),
                'reference' => $this->benchmarks[ExamScope::AREA->value]['average'] ?? null,
            ],
            'quality' => ['labels' => array_keys($this->qualityCounts), 'data' => array_values($this->qualityCounts)],
        ];
    }

    public function groupLabel(): string
    {
        return $this->district !== '' ? "อ.{$this->district}" : 'โรงเรียนที่แสดง';
    }

    public function isFiltered(): bool
    {
        return $this->district !== '' || trim($this->search) !== '';
    }

    public function render()
    {
        $sortable = $this->sortableColumns();
        $column = $sortable[$this->sortBy] ?? 'examresult.average';
        $direction = $this->sortDir === 'asc' ? 'asc' : 'desc';

        $rows = $this->filteredQuery()
            ->select(['examresult.*', 'school.name as schoolName', 'school.district as schoolDistrict'])
            ->orderByRaw("{$column} IS NULL")
            ->orderBy($column, $direction)
            ->orderBy('school.name')
            ->paginate(25);

        $this->dispatch('exam-charts', charts: $this->chartData);

        return view('livewire.exam-result.exam-result-dashboard', [
            'rows' => $rows,
            'type' => $this->type(),
            'types' => ExamType::cases(),
            'areaAverage' => $this->benchmarks[ExamScope::AREA->value]['average'] ?? null,
        ])->layout('layouts.app', ['title' => 'ผลการทดสอบระดับชาติ', 'header' => 'ผลการทดสอบระดับชาติ']);
    }
}
