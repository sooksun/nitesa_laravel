<?php

namespace App\Http\Controllers\Api;

use App\Enums\SupervisionStatus;
use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\Supervision;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function stats(Request $request): JsonResponse
    {
        $academicYear = $request->academicYear ?? $request->academic_year;

        $schoolQuery = School::query();
        $supervisionQuery = Supervision::query();

        if ($request->user()->isSupervisor()) {
            $assignedSchoolIds = $request->user()->assignedSchools()->pluck('school.id');
            $schoolQuery->whereIn('id', $assignedSchoolIds);
            $supervisionQuery->whereIn('schoolId', $assignedSchoolIds);
        }

        if ($academicYear) {
            $supervisionQuery->where('academicYear', $academicYear);
        }

        return response()->json([
            'total_schools' => $schoolQuery->count(),
            'total_supervisions' => $supervisionQuery->count(),
            'draft_count' => (clone $supervisionQuery)->where('status', SupervisionStatus::DRAFT)->count(),
            'submitted_count' => (clone $supervisionQuery)->where('status', SupervisionStatus::SUBMITTED)->count(),
            'approved_count' => (clone $supervisionQuery)->where('status', SupervisionStatus::APPROVED)->count(),
            'published_count' => (clone $supervisionQuery)->where('status', SupervisionStatus::PUBLISHED)->count(),
            'needs_improvement_count' => (clone $supervisionQuery)->where('status', SupervisionStatus::NEEDS_IMPROVEMENT)->count(),
        ]);
    }

    public function supervisionStatus(Request $request): JsonResponse
    {
        $academicYear = $request->academicYear ?? $request->academic_year;

        $query = DB::table('supervision');

        if ($academicYear) {
            $query->where('academicYear', $academicYear);
        }

        if ($request->user()->isSupervisor()) {
            $assignedSchoolIds = $request->user()->assignedSchools()->pluck('school.id');
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

        return response()->json([
            'labels' => ['ร่าง', 'ส่งแล้ว', 'อนุมัติแล้ว', 'เผยแพร่แล้ว', 'ต้องปรับปรุง'],
            'data' => [
                $data['DRAFT'] ?? 0,
                $data['SUBMITTED'] ?? 0,
                $data['APPROVED'] ?? 0,
                $data['PUBLISHED'] ?? 0,
                $data['NEEDS_IMPROVEMENT'] ?? 0,
            ],
            'colors' => ['#9ca3af', '#fbbf24', '#3b82f6', '#22c55e', '#ef4444'],
        ]);
    }

    public function policyUsage(Request $request): JsonResponse
    {
        $academicYear = $request->academicYear ?? $request->academic_year;

        $query = DB::table('supervision')
            ->select('policy.title', 'policy.code', DB::raw('count(*) as usage_count'))
            ->leftJoin('policy', function ($join) {
                $join->on('supervision.ministerPolicyId', '=', 'policy.id')
                    ->orOn('supervision.obecPolicyId', '=', 'policy.id')
                    ->orOn('supervision.areaPolicyId', '=', 'policy.id');
            })
            ->whereNotNull('policy.id');

        if ($academicYear) {
            $query->where('supervision.academicYear', $academicYear);
        }

        $data = $query
            ->groupBy('policy.id', 'policy.title', 'policy.code')
            ->orderByDesc('usage_count')
            ->limit(10)
            ->get();

        return response()->json([
            'labels' => $data->pluck('title')->toArray(),
            'data' => $data->pluck('usage_count')->toArray(),
        ]);
    }

    public function indicatorRadar(Request $request): JsonResponse
    {
        $academicYear = $request->academicYear ?? $request->academic_year;
        $schoolId = $request->schoolId ?? $request->school_id;

        $query = DB::table('indicator')
            ->join('supervision', 'indicator.supervisionId', '=', 'supervision.id')
            ->select('indicator.name', DB::raw('AVG(
                CASE 
                    WHEN indicator.level = "EXCELLENT" THEN 4
                    WHEN indicator.level = "GOOD" THEN 3
                    WHEN indicator.level = "FAIR" THEN 2
                    WHEN indicator.level = "NEEDS_WORK" THEN 1
                    ELSE 0
                END
            ) as avg_score'));

        if ($academicYear) {
            $query->where('supervision.academicYear', $academicYear);
        }

        if ($schoolId) {
            $query->where('supervision.schoolId', $schoolId);
        }

        $data = $query
            ->groupBy('indicator.name')
            ->orderBy('indicator.name')
            ->get();

        return response()->json([
            'labels' => $data->pluck('name')->toArray(),
            'data' => $data->pluck('avg_score')->map(fn($v) => round($v, 2))->toArray(),
        ]);
    }

    public function academicYears(): JsonResponse
    {
        $years = Supervision::distinct()
            ->orderBy('academicYear', 'desc')
            ->pluck('academicYear')
            ->filter()
            ->values();

        return response()->json($years);
    }

    public function districts(): JsonResponse
    {
        $districts = School::distinct()
            ->orderBy('district')
            ->pluck('district')
            ->filter()
            ->values();

        return response()->json($districts);
    }

    public function networkGroups(): JsonResponse
    {
        $networkGroups = DB::table('networkgroup')
            ->select('networkgroup.id', 'networkgroup.name', 'networkgroup.code')
            ->selectRaw('COUNT(school.id) as school_count')
            ->leftJoin('school', 'networkgroup.id', '=', 'school.networkGroupId')
            ->groupBy('networkgroup.id', 'networkgroup.name', 'networkgroup.code')
            ->orderBy('networkgroup.name')
            ->get();

        return response()->json($networkGroups);
    }
}
