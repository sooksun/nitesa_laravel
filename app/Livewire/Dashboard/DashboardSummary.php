<?php

namespace App\Livewire\Dashboard;

use App\Enums\SupervisionStatus;
use App\Models\NetworkGroup;
use App\Models\School;
use App\Models\Supervision;
use App\Models\Policy;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class DashboardSummary extends Component
{
    public string $academicYear = '';
    public array $academicYears = [];

    public function mount()
    {
        $this->academicYears = Supervision::distinct()
            ->orderBy('academicYear', 'desc')
            ->pluck('academicYear')
            ->filter()
            ->values()
            ->toArray();

        $this->academicYear = $this->academicYears[0] ?? (string)(date('Y') + 543);
    }

    public function getStatsProperty(): array
    {
        $user = auth()->user();
        
        $schoolQuery = School::query();
        $supervisionQuery = DB::table('supervision');
        $userCount = User::count();

        if ($user->isSupervisor()) {
            $assignedSchoolIds = $user->assignedSchools()->pluck('school.id')->toArray();
            $schoolQuery->whereIn('id', $assignedSchoolIds);
            $supervisionQuery->whereIn('schoolId', $assignedSchoolIds);
        }

        if ($this->academicYear) {
            $supervisionQuery->where('academicYear', $this->academicYear);
        }

        $totalSupervisions = $supervisionQuery->count();
        $approvedCount = (clone $supervisionQuery)->where('status', 'APPROVED')->count();
        $publishedCount = (clone $supervisionQuery)->where('status', 'PUBLISHED')->count();
        $approvalRate = $totalSupervisions > 0 ? round((($approvedCount + $publishedCount) / $totalSupervisions) * 100) : 0;

        return [
            'totalSchools' => $schoolQuery->count(),
            'totalSupervisions' => $totalSupervisions,
            'userCount' => $userCount,
            'approvalRate' => $approvalRate,
            'approvedCount' => $approvedCount + $publishedCount,
        ];
    }

    public function getStatusChartDataProperty(): array
    {
        $query = DB::table('supervision');
        
        if ($this->academicYear) {
            $query->where('academicYear', $this->academicYear);
        }

        if (auth()->user()->isSupervisor()) {
            $assignedSchoolIds = auth()->user()->assignedSchools()->pluck('school.id')->toArray();
            $query->whereIn('schoolId', $assignedSchoolIds);
        }

        $results = $query
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();
        
        $data = [];
        foreach ($results as $row) {
            $data[$row->status] = $row->count;
        }

        return [
            'labels' => ['ต้องปรับปรุง', 'ร่าง', 'ส่งแล้ว', 'อนุมัติแล้ว', 'เผยแพร่แล้ว'],
            'data' => [
                $data['NEEDS_IMPROVEMENT'] ?? 0,
                $data['DRAFT'] ?? 0,
                $data['SUBMITTED'] ?? 0,
                $data['APPROVED'] ?? 0,
                $data['PUBLISHED'] ?? 0,
            ],
            'colors' => ['#ef4444', '#d946ef', '#fbbf24', '#22c55e', '#3b82f6'],
        ];
    }

    public function getPolicyUsageProperty(): array
    {
        $query = DB::table('supervision')
            ->join('policy', function ($join) {
                $join->on('supervision.ministerPolicyId', '=', 'policy.id')
                    ->orOn('supervision.obecPolicyId', '=', 'policy.id')
                    ->orOn('supervision.areaPolicyId', '=', 'policy.id');
            })
            ->select('policy.type', DB::raw('count(*) as usage_count'));

        if ($this->academicYear) {
            $query->where('supervision.academicYear', $this->academicYear);
        }

        $data = $query
            ->groupBy('policy.type')
            ->get();

        $typeLabels = [
            'STUDENT_DEVELOPMENT' => 'พัฒนาผู้เรียน',
            'READING_CULTURE' => 'ส่งเสริมการอ่าน',
            'EDU_INNOV_TECH' => 'นวัตกรรมการศึกษา',
            'SCHOOL_SAFETY' => 'ความปลอดภัย',
            'TEACHER_UPSKILL' => 'พัฒนาครู',
            'SMART_GOVERNANCE' => 'บริหารจัดการ',
            'SPECIAL_NEEDS_EDU' => 'เด็กพิเศษ',
        ];

        $colors = [
            'STUDENT_DEVELOPMENT' => '#22c55e',
            'READING_CULTURE' => '#3b82f6',
            'EDU_INNOV_TECH' => '#d946ef',
            'SCHOOL_SAFETY' => '#f97316',
            'TEACHER_UPSKILL' => '#eab308',
            'SMART_GOVERNANCE' => '#06b6d4',
            'SPECIAL_NEEDS_EDU' => '#8b5cf6',
        ];

        return [
            'labels' => $data->pluck('type')->map(fn($t) => $typeLabels[$t] ?? $t)->toArray(),
            'data' => $data->pluck('usage_count')->toArray(),
            'colors' => $data->pluck('type')->map(fn($t) => $colors[$t] ?? '#9ca3af')->toArray(),
        ];
    }

    public function getIndicatorRadarProperty(): array
    {
        $query = DB::table('indicator')
            ->join('supervision', 'indicator.supervisionId', '=', 'supervision.id')
            ->select('indicator.name', 
                DB::raw('SUM(CASE WHEN indicator.level = "EXCELLENT" THEN 1 ELSE 0 END) as excellent'),
                DB::raw('SUM(CASE WHEN indicator.level = "GOOD" THEN 1 ELSE 0 END) as good'),
                DB::raw('SUM(CASE WHEN indicator.level = "FAIR" THEN 1 ELSE 0 END) as fair'),
                DB::raw('SUM(CASE WHEN indicator.level = "NEEDS_WORK" THEN 1 ELSE 0 END) as needs_work')
            );

        if ($this->academicYear) {
            $query->where('supervision.academicYear', $this->academicYear);
        }

        $data = $query->groupBy('indicator.name')->get();

        return [
            'labels' => $data->pluck('name')->toArray(),
            'excellent' => $data->pluck('excellent')->toArray(),
            'good' => $data->pluck('good')->toArray(),
            'fair' => $data->pluck('fair')->toArray(),
            'needsWork' => $data->pluck('needs_work')->toArray(),
        ];
    }

    public function getIndicatorDonutProperty(): array
    {
        $query = DB::table('indicator')
            ->join('supervision', 'indicator.supervisionId', '=', 'supervision.id')
            ->select('indicator.level', DB::raw('count(*) as count'));

        if ($this->academicYear) {
            $query->where('supervision.academicYear', $this->academicYear);
        }

        $results = $query->groupBy('indicator.level')->get();

        $data = [];
        foreach ($results as $row) {
            $data[$row->level] = $row->count;
        }

        return [
            'labels' => ['ดี', 'ดีเยี่ยม', 'ต้องพัฒนา', 'พอใช้'],
            'data' => [
                $data['GOOD'] ?? 0,
                $data['EXCELLENT'] ?? 0,
                $data['NEEDS_WORK'] ?? 0,
                $data['FAIR'] ?? 0,
            ],
            'colors' => ['#3b82f6', '#22c55e', '#f97316', '#eab308'],
        ];
    }

    public function getYearlyTrendProperty(): array
    {
        $data = DB::table('supervision')
            ->select('academicYear', DB::raw('count(*) as count'))
            ->groupBy('academicYear')
            ->orderBy('academicYear')
            ->get();

        return [
            'labels' => $data->pluck('academicYear')->toArray(),
            'data' => $data->pluck('count')->toArray(),
        ];
    }

    public function getNetworkGroupChartProperty(): array
    {
        $query = DB::table('supervision')
            ->join('school', 'supervision.schoolId', '=', 'school.id')
            ->join('networkgroup', 'school.networkGroupId', '=', 'networkgroup.id')
            ->select('networkgroup.name', DB::raw('count(*) as count'));

        if ($this->academicYear) {
            $query->where('supervision.academicYear', $this->academicYear);
        }

        $data = $query->groupBy('networkgroup.id', 'networkgroup.name')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        return [
            'labels' => $data->pluck('name')->toArray(),
            'data' => $data->pluck('count')->toArray(),
        ];
    }

    public function getDistrictChartProperty(): array
    {
        $query = DB::table('supervision')
            ->join('school', 'supervision.schoolId', '=', 'school.id')
            ->select('school.district', DB::raw('count(*) as count'));

        if ($this->academicYear) {
            $query->where('supervision.academicYear', $this->academicYear);
        }

        $data = $query->groupBy('school.district')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        return [
            'labels' => $data->pluck('district')->toArray(),
            'data' => $data->pluck('count')->toArray(),
        ];
    }

    public function getSupervisorPerformanceProperty(): array
    {
        $query = DB::table('supervision')
            ->join('user', 'supervision.userId', '=', 'user.id')
            ->select(
                'user.name',
                DB::raw('count(*) as total'),
                DB::raw('SUM(CASE WHEN supervision.status IN ("APPROVED", "PUBLISHED") THEN 1 ELSE 0 END) as approved')
            );

        if ($this->academicYear) {
            $query->where('supervision.academicYear', $this->academicYear);
        }

        $data = $query->groupBy('user.id', 'user.name')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        return [
            'labels' => $data->pluck('name')->toArray(),
            'total' => $data->pluck('total')->toArray(),
            'approved' => $data->pluck('approved')->toArray(),
            'rate' => $data->map(fn($r) => $r->total > 0 ? round(($r->approved / $r->total) * 100) : 0)->toArray(),
        ];
    }

    public function getRecentSupervisionsProperty()
    {
        $query = Supervision::with(['school', 'user'])
            ->orderBy('createdAt', 'desc')
            ->limit(5);

        if (auth()->user()->isSupervisor()) {
            $assignedSchoolIds = auth()->user()->assignedSchools()->pluck('school.id')->toArray();
            $query->whereIn('schoolId', $assignedSchoolIds);
        }

        return $query->get();
    }

    public function render()
    {
        return view('livewire.dashboard.dashboard-summary')
            ->layout('layouts.app', ['title' => 'แดชบอร์ดผู้ดูแลระบบ', 'header' => 'แดชบอร์ดผู้ดูแลระบบ']);
    }
}
