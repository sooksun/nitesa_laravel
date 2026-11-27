<?php

namespace App\Http\Controllers\Api;

use App\Enums\SupervisionStatus;
use App\Http\Controllers\Controller;
use App\Models\Acknowledgement;
use App\Models\Supervision;
use App\Services\SupervisionService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API Controller for Supervision management
 *
 * @package App\Http\Controllers\Api
 */
class SupervisionController extends Controller
{
    public function __construct(private readonly SupervisionService $service)
    {
    }

    /**
     * Get paginated list of supervisions with filters
     *
     * @param Request $request
     * @return JsonResponse
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

        // SUPERVISOR: assigned schools; SCHOOL: own school only
        $scopedIds = $request->user()->scopedSchoolIds();
        if ($scopedIds !== null) {
            $query->whereIn('schoolId', $scopedIds);
        }

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
     * @return JsonResponse
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

        $user = $request->user();
        if ($user->isSupervisor() && ! $user->assignedSchools()->where('school.id', $validated['schoolId'])->exists()) {
            return response()->json(['message' => 'คุณไม่มีสิทธิ์บันทึกการนิเทศของโรงเรียนนี้'], 403);
        }

        $indicators = $validated['indicators'] ?? [];
        unset($validated['indicators']);

        $supervision = $this->service->createSupervision([
            ...$validated,
            'userId' => $user->id,
            'status' => SupervisionStatus::DRAFT,
        ], $indicators);

        return response()->json($supervision, 201);
    }

    /**
     * Get a single supervision with all relationships
     *
     * @param Supervision $supervision
     * @return JsonResponse
     */
    public function show(Request $request, Supervision $supervision): JsonResponse
    {
        abort_unless($request->user()->canAccessSupervision($supervision), 403, 'คุณไม่มีสิทธิ์เข้าถึงการนิเทศนี้');

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
     * @return JsonResponse
     */
    public function update(Request $request, Supervision $supervision): JsonResponse
    {
        $this->authorizeOwner($request, $supervision);

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

        if (array_key_exists('indicators', $validated)) {
            $indicators = $validated['indicators'];
            unset($validated['indicators']);
            $supervision = $this->service->updateSupervision($supervision, $validated, $indicators);
        } else {
            $supervision->update($validated);
            $supervision->load(['school', 'user', 'indicators']);
        }

        return response()->json($supervision);
    }

    /**
     * Delete a supervision (only drafts)
     *
     * @param Supervision $supervision
     * @return JsonResponse
     */
    public function destroy(Request $request, Supervision $supervision): JsonResponse
    {
        $this->authorizeOwner($request, $supervision);

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
     * @return JsonResponse
     */
    public function submit(Request $request, Supervision $supervision): JsonResponse
    {
        $this->authorizeOwner($request, $supervision);

        if (! $supervision->canSubmit()) {
            return response()->json(
                ['message' => 'ไม่สามารถส่งการนิเทศนี้ได้'],
                403
            );
        }

        $errors = $this->service->validateForSubmission($supervision);
        if ($errors !== []) {
            return response()->json(['message' => 'ข้อมูลไม่ครบ', 'errors' => $errors], 422);
        }

        $this->service->submit($supervision);

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
     * @return JsonResponse
     */
    public function approve(Request $request, Supervision $supervision): JsonResponse
    {
        if (! $supervision->canApprove()) {
            return response()->json(
                ['message' => 'ไม่สามารถอนุมัติการนิเทศนี้ได้'],
                403
            );
        }

        $this->service->approve($supervision);

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
     * @return JsonResponse
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
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $this->service->reject($supervision, $validated['reason'], $request->user());

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
     * @return JsonResponse
     */
    public function publish(Request $request, Supervision $supervision): JsonResponse
    {
        $this->authorizeOwner($request, $supervision);

        if (! $supervision->canPublish()) {
            return response()->json(
                ['message' => 'ไม่สามารถเผยแพร่การนิเทศนี้ได้'],
                403
            );
        }

        $this->service->publish($supervision);

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
     * @return JsonResponse
     */
    public function acknowledge(Request $request, Supervision $supervision): JsonResponse
    {
        abort_unless($request->user()->canAccessSupervision($supervision), 403, 'คุณไม่มีสิทธิ์เข้าถึงการนิเทศนี้');

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

    protected function authorizeOwner(Request $request, Supervision $supervision): void
    {
        $user = $request->user();

        abort_unless($user->isAdmin() || $supervision->userId === $user->id, 403, 'คุณไม่มีสิทธิ์แก้ไขการนิเทศนี้');
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
        foreach ($this->service->schoolRecipients($supervision) as $user) {
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
