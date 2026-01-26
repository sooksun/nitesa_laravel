<?php

namespace App\Http\Controllers\Api;

use App\Enums\SupervisionStatus;
use App\Http\Controllers\Controller;
use App\Models\Acknowledgement;
use App\Models\Supervision;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * API Controller for Supervision management
 *
 * @package App\Http\Controllers\Api
 */
class SupervisionController extends Controller
{
    /**
     * Get paginated list of supervisions with filters
     *
     * @param Request $request
     * @return JsonResponse<LengthAwarePaginator>
     */
    public function index(Request $request): JsonResponse
    {
        $query = Supervision::with(['school', 'user'])
            ->withCount(['indicators', 'attachments']);

        if ($request->has('search') && $request->filled('search')) {
            $search = $request->string('search')->value();
            $query->whereHas('school', fn(Builder $q) => $q->where('name', 'like', "%{$search}%"));
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

    /**
     * Create a new supervision
     *
     * @param Request $request
     * @return JsonResponse<Supervision>
     */
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
        if (! empty($validated['indicators'])) {
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

    /**
     * Get a single supervision with all relationships
     *
     * @param Supervision $supervision
     * @return JsonResponse<Supervision>
     */
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

    /**
     * Update an existing supervision
     *
     * @param Request $request
     * @param Supervision $supervision
     * @return JsonResponse<Supervision>
     */
    public function update(Request $request, Supervision $supervision): JsonResponse
    {
        // Only allow updates on drafts or needs_improvement
        $allowedStatuses = [SupervisionStatus::DRAFT, SupervisionStatus::NEEDS_IMPROVEMENT];
        if (! in_array($supervision->status, $allowedStatuses, true)) {
            return response()->json(
                ['message' => 'ไม่สามารถแก้ไขการนิเทศที่ส่งแล้วได้'],
                403
            );
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
                if (! empty($ind['id'])) {
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

    /**
     * Delete a supervision (only drafts)
     *
     * @param Supervision $supervision
     * @return JsonResponse<null>
     */
    public function destroy(Supervision $supervision): JsonResponse
    {
        // Only allow deletion of drafts
        if ($supervision->status !== SupervisionStatus::DRAFT) {
            return response()->json(
                ['message' => 'ไม่สามารถลบการนิเทศที่ส่งแล้วได้'],
                403
            );
        }

        $supervision->delete();

        return response()->json(null, 204);
    }

    // Workflow actions
    /**
     * Submit supervision for approval
     *
     * @param Request $request
     * @param Supervision $supervision
     * @return JsonResponse<Supervision>
     */
    public function submit(Request $request, Supervision $supervision): JsonResponse
    {
        if (! $supervision->canSubmit()) {
            return response()->json(
                ['message' => 'ไม่สามารถส่งการนิเทศนี้ได้'],
                403
            );
        }

        $supervision->submit();

        activity()
            ->causedBy($request->user())
            ->performedOn($supervision)
            ->log('ส่งการนิเทศเพื่ออนุมัติ');

        // ส่ง notification ไปยังผู้บริหารและผู้ดูแลระบบ
        $this->notifyExecutivesAndAdmins($supervision);

        return response()->json($supervision);
    }

    /**
     * Approve supervision
     *
     * @param Request $request
     * @param Supervision $supervision
     * @return JsonResponse<Supervision>
     */
    public function approve(Request $request, Supervision $supervision): JsonResponse
    {
        if (! $supervision->canApprove()) {
            return response()->json(
                ['message' => 'ไม่สามารถอนุมัติการนิเทศนี้ได้'],
                403
            );
        }

        $supervision->approve();

        activity()
            ->causedBy($request->user())
            ->performedOn($supervision)
            ->log('อนุมัติการนิเทศ');

        // ส่ง notification ไปยังผู้นิเทศที่สร้างการนิเทศนี้
        $supervision->user->notify(
            new \App\Notifications\SupervisionApprovedNotification(
                $supervision,
                $request->user()->name
            )
        );

        return response()->json($supervision);
    }

    /**
     * Reject supervision (send back for improvement)
     *
     * @param Request $request
     * @param Supervision $supervision
     * @return JsonResponse<Supervision>
     */
    public function reject(Request $request, Supervision $supervision): JsonResponse
    {
        if (! $supervision->canReject()) {
            return response()->json(
                ['message' => 'ไม่สามารถปฏิเสธการนิเทศนี้ได้'],
                403
            );
        }

        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $supervision->reject();

        $logMessage = 'ส่งกลับเพื่อปรับปรุง';
        if (! empty($validated['reason'])) {
            $logMessage .= ': ' . $validated['reason'];
        }

        activity()
            ->causedBy($request->user())
            ->performedOn($supervision)
            ->log($logMessage);

        // ส่ง notification ไปยังผู้นิเทศที่สร้างการนิเทศนี้
        $supervision->user->notify(
            new \App\Notifications\SupervisionRejectedNotification(
                $supervision,
                $request->user()->name,
                $validated['reason'] ?? null
            )
        );

        return response()->json($supervision);
    }

    /**
     * Publish supervision
     *
     * @param Request $request
     * @param Supervision $supervision
     * @return JsonResponse<Supervision>
     */
    public function publish(Request $request, Supervision $supervision): JsonResponse
    {
        if (! $supervision->canPublish()) {
            return response()->json(
                ['message' => 'ไม่สามารถเผยแพร่การนิเทศนี้ได้'],
                403
            );
        }

        $supervision->publish();

        activity()
            ->causedBy($request->user())
            ->performedOn($supervision)
            ->log('เผยแพร่การนิเทศ');

        // ส่ง notification ไปยังโรงเรียนและผู้เกี่ยวข้อง
        $this->notifySchool($supervision);

        return response()->json($supervision);
    }

    /**
     * Acknowledge supervision (by school)
     *
     * @param Request $request
     * @param Supervision $supervision
     * @return JsonResponse<Supervision>
     */
    public function acknowledge(Request $request, Supervision $supervision): JsonResponse
    {
        if ($supervision->status !== SupervisionStatus::PUBLISHED) {
            return response()->json(
                ['message' => 'การนิเทศยังไม่ถูกเผยแพร่'],
                403
            );
        }

        if ($supervision->acknowledgement !== null) {
            return response()->json(
                ['message' => 'การนิเทศนี้รับทราบแล้ว'],
                403
            );
        }

        $validated = $request->validate([
            'acknowledgedBy' => ['required', 'string', 'max:255'],
            'comment' => ['nullable', 'string', 'max:1000'],
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

    /**
     * Send notification to executives and admins
     *
     * @param Supervision $supervision
     * @return void
     */
    protected function notifyExecutivesAndAdmins(Supervision $supervision): void
    {
        $recipients = \App\Models\User::whereIn('role', ['EXECUTIVE', 'ADMIN'])
            ->where('isActive', true)
            ->get();

        foreach ($recipients as $recipient) {
            $recipient->notify(
                new \App\Notifications\SupervisionSubmittedNotification($supervision)
            );
        }
    }

    /**
     * Send notification to school and related users
     *
     * @param Supervision $supervision
     * @return void
     */
    protected function notifySchool(Supervision $supervision): void
    {
        // Find users with SCHOOL role
        $schoolUsers = \App\Models\User::where('role', 'SCHOOL')
            ->where('isActive', true)
            ->get();

        foreach ($schoolUsers as $user) {
            $user->notify(
                new \App\Notifications\SupervisionPublishedNotification($supervision)
            );
        }

        // Also notify the supervisor who created this supervision
        $supervision->user->notify(
            new \App\Notifications\SupervisionPublishedNotification($supervision)
        );
    }
}
