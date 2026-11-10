<?php

namespace App\Livewire\Report;

use App\Exports\ArrayExport;
use App\Models\NetworkGroup;
use App\Models\School;
use App\Models\Supervision;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * @property-read array<string, mixed> $summaryReport
 * @property-read \Illuminate\Support\Collection<int, object> $districtReport
 * @property-read \Illuminate\Support\Collection<int, object> $indicatorReport
 */
class ReportIndex extends Component
{
    public string $academicYear = '';
    public string $district = '';
    public string $networkGroupId = '';
    public string $reportType = 'summary';

    /** @var array{ids: array<int, string>|null}|null */
    protected ?array $scopedIdsCache = null;

    public function mount()
    {
        $latestYear = Supervision::query()
            ->when($this->scopedSchoolIds() !== null, fn ($q) => $q->whereIn('schoolId', $this->scopedSchoolIds()))
            ->max('academicYear');
        $this->academicYear = $latestYear ?? (string) (date('Y') + 543);
    }

    /**
     * SUPERVISOR: assigned schools, SCHOOL: own school, ADMIN/EXECUTIVE: null (all).
     *
     * @return array<int, string>|null
     */
    protected function scopedSchoolIds(): ?array
    {
        $this->scopedIdsCache ??= ['ids' => auth()->user()->scopedSchoolIds()];

        return $this->scopedIdsCache['ids'];
    }

    /**
     * Restrict a query on the school table (or a table joined to it) to the user's scope and filters.
     */
    protected function applySchoolFilters($query, string $idColumn = 'id', string $prefix = '')
    {
        $scoped = $this->scopedSchoolIds();

        return $query
            ->when($scoped !== null, fn ($q) => $q->whereIn($prefix . $idColumn, $scoped))
            ->when($this->district, fn ($q) => $q->where($prefix . 'district', $this->district))
            ->when($this->networkGroupId, fn ($q) => $q->where($prefix . 'networkGroupId', $this->networkGroupId));
    }

    public function getAcademicYearsProperty()
    {
        return Supervision::query()
            ->when($this->scopedSchoolIds() !== null, fn ($q) => $q->whereIn('schoolId', $this->scopedSchoolIds()))
            ->distinct()
            ->orderBy('academicYear', 'desc')
            ->pluck('academicYear')
            ->filter()
            ->values();
    }

    public function getDistrictsProperty()
    {
        return $this->applySchoolFilters(School::query()->select('district'))
            ->distinct()->orderBy('district')->pluck('district')->filter();
    }

    public function getNetworkGroupsProperty()
    {
        return NetworkGroup::orderBy('name')->get();
    }

    public function getSummaryReportProperty()
    {
        $schoolIds = $this->applySchoolFilters(School::query())->pluck('id');

        $totalSchools = $schoolIds->count();

        $statusResults = DB::table('supervision')
            ->whereIn('schoolId', $schoolIds)
            ->when($this->academicYear, fn ($q) => $q->where('academicYear', $this->academicYear))
            ->when(auth()->user()->isSchool(), fn ($q) => $q->where('status', 'PUBLISHED'))
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();

        $supervisedSchools = DB::table('supervision')
            ->whereIn('schoolId', $schoolIds)
            ->when($this->academicYear, fn ($q) => $q->where('academicYear', $this->academicYear))
            ->when(auth()->user()->isSchool(), fn ($q) => $q->where('status', 'PUBLISHED'))
            ->distinct()
            ->count('schoolId');

        $statusBreakdown = [];
        foreach ($statusResults as $row) {
            $statusBreakdown[$row->status] = $row->count;
        }

        return [
            'total_schools' => $totalSchools,
            'supervised_schools' => $supervisedSchools,
            'not_supervised' => $totalSchools - $supervisedSchools,
            'coverage_rate' => $totalSchools > 0 ? round(($supervisedSchools / $totalSchools) * 100, 1) : 0,
            'total_supervisions' => array_sum($statusBreakdown),
            'status_breakdown' => $statusBreakdown,
        ];
    }

    public function getDistrictReportProperty()
    {
        $query = DB::table('school')
            ->select(
                'school.district',
                DB::raw('COUNT(DISTINCT school.id) as total_schools'),
                DB::raw('COUNT(DISTINCT CASE WHEN supervision.id IS NOT NULL THEN school.id END) as supervised_schools'),
                DB::raw('COUNT(supervision.id) as total_supervisions')
            )
            ->leftJoin('supervision', function ($join) {
                $join->on('school.id', '=', 'supervision.schoolId');
                if ($this->academicYear) {
                    $join->where('supervision.academicYear', $this->academicYear);
                }
            });

        return $this->applySchoolFilters($query, 'id', 'school.')
            ->groupBy('school.district')
            ->orderBy('school.district')
            ->get();
    }

    public function getIndicatorReportProperty()
    {
        $query = DB::table('indicator')
            ->join('supervision', 'indicator.supervisionId', '=', 'supervision.id')
            ->join('school', 'supervision.schoolId', '=', 'school.id')
            ->select(
                'indicator.name',
                DB::raw('COUNT(*) as total'),
                DB::raw("SUM(CASE WHEN indicator.level = 'EXCELLENT' THEN 1 ELSE 0 END) as excellent"),
                DB::raw("SUM(CASE WHEN indicator.level = 'GOOD' THEN 1 ELSE 0 END) as good"),
                DB::raw("SUM(CASE WHEN indicator.level = 'FAIR' THEN 1 ELSE 0 END) as fair"),
                DB::raw("SUM(CASE WHEN indicator.level = 'NEEDS_WORK' THEN 1 ELSE 0 END) as needs_work"),
                DB::raw("AVG(
                    CASE
                        WHEN indicator.level = 'EXCELLENT' THEN 4
                        WHEN indicator.level = 'GOOD' THEN 3
                        WHEN indicator.level = 'FAIR' THEN 2
                        WHEN indicator.level = 'NEEDS_WORK' THEN 1
                    END
                ) as avg_score")
            )
            ->when($this->academicYear, fn (QueryBuilder $q) => $q->where('supervision.academicYear', $this->academicYear))
            ->when(auth()->user()->isSchool(), fn (QueryBuilder $q) => $q->where('supervision.status', 'PUBLISHED'));

        return $this->applySchoolFilters($query, 'id', 'school.')
            ->groupBy('indicator.name')
            ->orderBy('indicator.name')
            ->get();
    }

    public function canExport(): bool
    {
        return auth()->user()->isAdmin();
    }

    public function export(): BinaryFileResponse
    {
        abort_unless($this->canExport(), 403);

        [$headings, $rows] = match ($this->reportType) {
            'district' => [
                ['อำเภอ', 'จำนวนโรงเรียน', 'ได้รับการนิเทศ', 'จำนวนการนิเทศ', 'ร้อยละความครอบคลุม'],
                $this->districtReport->map(fn ($r) => [
                    $r->district ?: '-',
                    (int) $r->total_schools,
                    (int) $r->supervised_schools,
                    (int) $r->total_supervisions,
                    $r->total_schools > 0 ? round($r->supervised_schools / $r->total_schools * 100, 1) : 0,
                ])->all(),
            ],
            'indicator' => [
                ['ตัวชี้วัด', 'จำนวนครั้ง', 'ดีมาก', 'ดี', 'พอใช้', 'ต้องปรับปรุง', 'คะแนนเฉลี่ย (เต็ม 4)'],
                $this->indicatorReport->map(fn ($r) => [
                    $r->name,
                    (int) $r->total,
                    (int) $r->excellent,
                    (int) $r->good,
                    (int) $r->fair,
                    (int) $r->needs_work,
                    round((float) $r->avg_score, 2),
                ])->all(),
            ],
            default => $this->summaryExportRows(),
        };

        $filename = sprintf('nitesa-report-%s-%s.xlsx', $this->reportType, $this->academicYear ?: 'all');

        return Excel::download(new ArrayExport($headings, $rows), $filename);
    }

    /**
     * @return array{0: array<int, string>, 1: array<int, array<int, mixed>>}
     */
    protected function summaryExportRows(): array
    {
        $s = $this->summaryReport;
        $labels = [
            'DRAFT' => 'ร่าง',
            'SUBMITTED' => 'ส่งแล้ว',
            'APPROVED' => 'อนุมัติแล้ว',
            'PUBLISHED' => 'เผยแพร่แล้ว',
            'NEEDS_IMPROVEMENT' => 'ต้องปรับปรุง',
        ];

        $rows = [
            ['ปีการศึกษา', $this->academicYear ?: 'ทั้งหมด'],
            ['จำนวนโรงเรียนทั้งหมด', $s['total_schools']],
            ['โรงเรียนที่ได้รับการนิเทศ', $s['supervised_schools']],
            ['โรงเรียนที่ยังไม่ได้รับการนิเทศ', $s['not_supervised']],
            ['ร้อยละความครอบคลุม', $s['coverage_rate']],
            ['จำนวนการนิเทศทั้งหมด', $s['total_supervisions']],
        ];
        foreach ($labels as $key => $label) {
            $rows[] = ['สถานะ: ' . $label, $s['status_breakdown'][$key] ?? 0];
        }

        return [['รายการ', 'ค่า'], $rows];
    }

    public function render()
    {
        return view('livewire.report.report-index')
            ->layout('layouts.app', ['title' => 'รายงาน', 'header' => 'รายงาน']);
    }
}
