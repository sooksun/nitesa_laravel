<?php

namespace App\Livewire\Report;

use App\Enums\SupervisionStatus;
use App\Models\NetworkGroup;
use App\Models\School;
use App\Models\Supervision;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ReportIndex extends Component
{
    public string $academicYear = '';
    public string $district = '';
    public string $networkGroupId = '';
    public string $reportType = 'summary';

    public function mount()
    {
        $latestYear = Supervision::max('academicYear');
        $this->academicYear = $latestYear ?? (string)(date('Y') + 543);
    }

    public function getAcademicYearsProperty()
    {
        return Supervision::distinct()
            ->orderBy('academicYear', 'desc')
            ->pluck('academicYear')
            ->filter()
            ->values();
    }

    public function getDistrictsProperty()
    {
        return School::distinct()->orderBy('district')->pluck('district')->filter();
    }

    public function getNetworkGroupsProperty()
    {
        return NetworkGroup::orderBy('name')->get();
    }

    public function getSummaryReportProperty()
    {
        $query = Supervision::query()
            ->when($this->academicYear, fn($q) => $q->where('academicYear', $this->academicYear))
            ->when($this->district, fn($q) => $q->whereHas('school', fn($sq) => $sq->where('district', $this->district)))
            ->when($this->networkGroupId, fn($q) => $q->whereHas('school', fn($sq) => $sq->where('networkGroupId', $this->networkGroupId)));

        $totalSchools = School::query()
            ->when($this->district, fn($q) => $q->where('district', $this->district))
            ->when($this->networkGroupId, fn($q) => $q->where('networkGroupId', $this->networkGroupId))
            ->count();

        $supervisedSchools = (clone $query)
            ->distinct('schoolId')
            ->count('schoolId');

        // Use DB::table to avoid Enum casting issues
        $statusQuery = DB::table('supervision')
            ->when($this->academicYear, fn($q) => $q->where('academicYear', $this->academicYear))
            ->when($this->district, fn($q) => $q->whereIn('schoolId', 
                School::where('district', $this->district)->pluck('id')
            ))
            ->when($this->networkGroupId, fn($q) => $q->whereIn('schoolId', 
                School::where('networkGroupId', $this->networkGroupId)->pluck('id')
            ));

        $statusResults = $statusQuery
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();
        
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
        return DB::table('school')
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
            })
            ->groupBy('school.district')
            ->orderBy('school.district')
            ->get();
    }

    public function getIndicatorReportProperty()
    {
        return DB::table('indicator')
            ->join('supervision', 'indicator.supervisionId', '=', 'supervision.id')
            ->select(
                'indicator.name',
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN indicator.level = "EXCELLENT" THEN 1 ELSE 0 END) as excellent'),
                DB::raw('SUM(CASE WHEN indicator.level = "GOOD" THEN 1 ELSE 0 END) as good'),
                DB::raw('SUM(CASE WHEN indicator.level = "FAIR" THEN 1 ELSE 0 END) as fair'),
                DB::raw('SUM(CASE WHEN indicator.level = "NEEDS_WORK" THEN 1 ELSE 0 END) as needs_work'),
                DB::raw('AVG(
                    CASE 
                        WHEN indicator.level = "EXCELLENT" THEN 4
                        WHEN indicator.level = "GOOD" THEN 3
                        WHEN indicator.level = "FAIR" THEN 2
                        WHEN indicator.level = "NEEDS_WORK" THEN 1
                    END
                ) as avg_score')
            )
            ->when($this->academicYear, fn($q) => $q->where('supervision.academicYear', $this->academicYear))
            ->groupBy('indicator.name')
            ->orderBy('indicator.name')
            ->get();
    }

    public function render()
    {
        return view('livewire.report.report-index')
            ->layout('layouts.app', ['title' => 'รายงาน', 'header' => 'รายงาน']);
    }
}
