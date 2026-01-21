<?php

namespace App\Http\Controllers\Api;

use App\Enums\SupervisionStatus;
use App\Http\Controllers\Controller;
use App\Models\Supervision;
use App\Models\Acknowledgement;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SupervisionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Supervision::with(['school', 'user'])
            ->withCount(['indicators', 'attachments']);

        if ($request->has('search')) {
            $query->whereHas('school', fn($q) => $q->where('name', 'like', "%{$request->search}%"));
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('schoolId')) {
            $query->where('schoolId', $request->schoolId);
        }

        if ($request->has('academicYear')) {
            $query->where('academicYear', $request->academicYear);
        }

        // Filter by assigned schools for supervisors
        if ($request->user()->isSupervisor()) {
            $assignedSchoolIds = $request->user()->assignedSchools()->pluck('school.id');
            $query->whereIn('schoolId', $assignedSchoolIds);
        }

        // School users can only see published supervisions
        if ($request->user()->isSchool()) {
            $query->where('status', SupervisionStatus::PUBLISHED);
        }

        $supervisions = $query->orderBy('date', 'desc')->paginate($request->per_page ?? 15);

        return response()->json($supervisions);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'schoolId' => 'required|exists:school,id',
            'type' => 'required|string|max:100',
            'date' => 'required|date',
            'academicYear' => 'nullable|string|max:10',
            'ministerPolicyId' => 'nullable|exists:policy,id',
            'obecPolicyId' => 'nullable|exists:policy,id',
            'areaPolicyId' => 'nullable|exists:policy,id',
            'summary' => 'required|string',
            'suggestions' => 'required|string',
            'indicators' => 'array',
            'indicators.*.name' => 'required|string',
            'indicators.*.level' => 'required|in:EXCELLENT,GOOD,FAIR,NEEDS_WORK',
            'indicators.*.comment' => 'nullable|string',
        ]);

        $supervision = Supervision::create([
            ...$validated,
            'userId' => $request->user()->id,
            'status' => SupervisionStatus::DRAFT,
        ]);

        // Create indicators
        if (!empty($validated['indicators'])) {
            foreach ($validated['indicators'] as $ind) {
                $supervision->indicators()->create([
                    'supervisionId' => $supervision->id,
                    ...$ind,
                ]);
            }
        }

        $supervision->load(['school', 'user', 'indicators']);

        return response()->json($supervision, 201);
    }

    public function show(Supervision $supervision): JsonResponse
    {
        $supervision->load([
            'school.networkGroupRelation',
            'user',
            'indicators',
            'attachments',
            'acknowledgement',
            'ministerPolicyRelation',
            'obecPolicyRelation',
            'areaPolicyRelation',
        ]);

        return response()->json($supervision);
    }

    public function update(Request $request, Supervision $supervision): JsonResponse
    {
        // Only allow updates on drafts or needs_improvement
        if (!in_array($supervision->status, [SupervisionStatus::DRAFT, SupervisionStatus::NEEDS_IMPROVEMENT])) {
            return response()->json(['message' => 'ไม่สามารถแก้ไขการนิเทศที่ส่งแล้วได้'], 403);
        }

        $validated = $request->validate([
            'type' => 'required|string|max:100',
            'date' => 'required|date',
            'academicYear' => 'nullable|string|max:10',
            'ministerPolicyId' => 'nullable|exists:policy,id',
            'obecPolicyId' => 'nullable|exists:policy,id',
            'areaPolicyId' => 'nullable|exists:policy,id',
            'summary' => 'required|string',
            'suggestions' => 'required|string',
            'indicators' => 'array',
            'indicators.*.id' => 'nullable|exists:indicator,id',
            'indicators.*.name' => 'required|string',
            'indicators.*.level' => 'required|in:EXCELLENT,GOOD,FAIR,NEEDS_WORK',
            'indicators.*.comment' => 'nullable|string',
        ]);

        $supervision->update($validated);

        // Update indicators
        if (isset($validated['indicators'])) {
            $existingIds = [];
            foreach ($validated['indicators'] as $ind) {
                if (!empty($ind['id'])) {
                    $indicator = $supervision->indicators()->find($ind['id']);
                    if ($indicator) {
                        $indicator->update($ind);
                        $existingIds[] = $ind['id'];
                    }
                } else {
                    $indicator = $supervision->indicators()->create($ind);
                    $existingIds[] = $indicator->id;
                }
            }
            $supervision->indicators()->whereNotIn('id', $existingIds)->delete();
        }

        $supervision->load(['school', 'user', 'indicators']);

        return response()->json($supervision);
    }

    public function destroy(Supervision $supervision): JsonResponse
    {
        // Only allow deletion of drafts
        if ($supervision->status !== SupervisionStatus::DRAFT) {
            return response()->json(['message' => 'ไม่สามารถลบการนิเทศที่ส่งแล้วได้'], 403);
        }

        $supervision->delete();

        return response()->json(null, 204);
    }

    // Workflow actions
    public function submit(Request $request, Supervision $supervision): JsonResponse
    {
        if (!$supervision->canSubmit()) {
            return response()->json(['message' => 'ไม่สามารถส่งการนิเทศนี้ได้'], 403);
        }

        $supervision->submit();

        activity()
            ->causedBy($request->user())
            ->performedOn($supervision)
            ->log('ส่งการนิเทศเพื่ออนุมัติ');

        return response()->json($supervision);
    }

    public function approve(Request $request, Supervision $supervision): JsonResponse
    {
        if (!$supervision->canApprove()) {
            return response()->json(['message' => 'ไม่สามารถอนุมัติการนิเทศนี้ได้'], 403);
        }

        $supervision->approve();

        activity()
            ->causedBy($request->user())
            ->performedOn($supervision)
            ->log('อนุมัติการนิเทศ');

        return response()->json($supervision);
    }

    public function reject(Request $request, Supervision $supervision): JsonResponse
    {
        if (!$supervision->canReject()) {
            return response()->json(['message' => 'ไม่สามารถปฏิเสธการนิเทศนี้ได้'], 403);
        }

        $supervision->reject();

        activity()
            ->causedBy($request->user())
            ->performedOn($supervision)
            ->log('ส่งกลับเพื่อปรับปรุง');

        return response()->json($supervision);
    }

    public function publish(Request $request, Supervision $supervision): JsonResponse
    {
        if (!$supervision->canPublish()) {
            return response()->json(['message' => 'ไม่สามารถเผยแพร่การนิเทศนี้ได้'], 403);
        }

        $supervision->publish();

        activity()
            ->causedBy($request->user())
            ->performedOn($supervision)
            ->log('เผยแพร่การนิเทศ');

        return response()->json($supervision);
    }

    public function acknowledge(Request $request, Supervision $supervision): JsonResponse
    {
        if ($supervision->status !== SupervisionStatus::PUBLISHED) {
            return response()->json(['message' => 'การนิเทศยังไม่ถูกเผยแพร่'], 403);
        }

        if ($supervision->acknowledgement) {
            return response()->json(['message' => 'การนิเทศนี้รับทราบแล้ว'], 403);
        }

        $validated = $request->validate([
            'acknowledgedBy' => 'required|string|max:255',
            'comment' => 'nullable|string',
        ]);

        Acknowledgement::create([
            'supervisionId' => $supervision->id,
            'acknowledgedBy' => $validated['acknowledgedBy'],
            'acknowledgedAt' => now(),
            'comment' => $validated['comment'] ?? null,
        ]);

        activity()
            ->causedBy($request->user())
            ->performedOn($supervision)
            ->log('โรงเรียนรับทราบผลการนิเทศ');

        $supervision->load('acknowledgement');

        return response()->json($supervision);
    }
}
