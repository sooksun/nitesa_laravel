<?php

namespace App\Http\Controllers\Api;

use App\Enums\SupervisionStatus;
use App\Http\Controllers\Controller;
use App\Models\School;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SchoolController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = School::with(['networkGroupRelation'])
            ->withCount('supervisions');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('code', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('district')) {
            $query->where('district', $request->district);
        }

        if ($request->filled('networkGroupId')) {
            $query->where('networkGroupId', $request->networkGroupId);
        }

        $scopedIds = $request->user()->scopedSchoolIds();
        if ($scopedIds !== null) {
            $query->whereIn('id', $scopedIds);
        }

        $schools = $query->orderBy('name')->paginate(min((int) ($request->per_page ?? 15), 100));

        return response()->json($schools);
    }

    public function store(Request $request): JsonResponse
    {
        $school = School::create($request->validate($this->rules()));

        return response()->json($school, 201);
    }

    public function show(Request $request, School $school): JsonResponse
    {
        abort_unless($request->user()->canViewSchool($school), 403, 'คุณไม่มีสิทธิ์เข้าถึงโรงเรียนนี้');

        $school->load(['networkGroupRelation', 'supervisors']);
        $school->loadCount('supervisions');

        return response()->json($school);
    }

    public function update(Request $request, School $school): JsonResponse
    {
        $school->update($request->validate($this->rules($school)));

        return response()->json($school);
    }

    public function destroy(School $school): JsonResponse
    {
        $school->delete();

        return response()->json(null, 204);
    }

    public function supervisions(Request $request, School $school): JsonResponse
    {
        abort_unless($request->user()->canViewSchool($school), 403, 'คุณไม่มีสิทธิ์เข้าถึงโรงเรียนนี้');

        $supervisions = $school->supervisions()
            ->with(['user', 'indicators'])
            ->when($request->user()->isSchool(), fn ($q) => $q->where('status', SupervisionStatus::PUBLISHED))
            ->orderBy('date', 'desc')
            ->paginate(15);

        return response()->json($supervisions);
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(?School $school = null): array
    {
        return [
            'code' => ['required', 'string', 'max:20', Rule::unique('school', 'code')->ignore($school?->id)],
            'name' => ['required', 'string', 'max:255'],
            'province' => ['nullable', 'string', 'max:100'],
            'district' => ['required', 'string', 'max:100'],
            'subDistrict' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:500'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'principalName' => ['nullable', 'string', 'max:255'],
            'studentCount' => ['nullable', 'integer', 'min:0'],
            'teacherCount' => ['nullable', 'integer', 'min:0'],
            'networkGroupId' => ['nullable', 'exists:networkgroup,id'],
        ];
    }
}
