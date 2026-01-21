<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SchoolController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = School::with(['networkGroup'])
            ->withCount('supervisions');

        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('code', 'like', "%{$request->search}%");
            });
        }

        if ($request->has('district')) {
            $query->where('district', $request->district);
        }

        if ($request->has('network_group_id')) {
            $query->where('network_group_id', $request->network_group_id);
        }

        // Filter by assigned schools for supervisors
        if ($request->user()->isSupervisor()) {
            $assignedSchoolIds = $request->user()->assignedSchools()->pluck('schools.id');
            $query->whereIn('id', $assignedSchoolIds);
        }

        $schools = $query->orderBy('name')->paginate($request->per_page ?? 15);

        return response()->json($schools);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', School::class);

        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:schools,code',
            'name' => 'required|string|max:255',
            'province' => 'nullable|string|max:100',
            'district' => 'required|string|max:100',
            'sub_district' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'principal_name' => 'nullable|string|max:255',
            'student_count' => 'nullable|integer|min:0',
            'teacher_count' => 'nullable|integer|min:0',
            'network_group_id' => 'nullable|exists:network_groups,id',
        ]);

        $school = School::create($validated);

        return response()->json($school, 201);
    }

    public function show(School $school): JsonResponse
    {
        $school->load(['networkGroup', 'supervisors']);
        $school->loadCount('supervisions');

        return response()->json($school);
    }

    public function update(Request $request, School $school): JsonResponse
    {
        $this->authorize('update', $school);

        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:schools,code,' . $school->id,
            'name' => 'required|string|max:255',
            'province' => 'nullable|string|max:100',
            'district' => 'required|string|max:100',
            'sub_district' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'principal_name' => 'nullable|string|max:255',
            'student_count' => 'nullable|integer|min:0',
            'teacher_count' => 'nullable|integer|min:0',
            'network_group_id' => 'nullable|exists:network_groups,id',
        ]);

        $school->update($validated);

        return response()->json($school);
    }

    public function destroy(School $school): JsonResponse
    {
        $this->authorize('delete', $school);

        $school->delete();

        return response()->json(null, 204);
    }

    public function supervisions(School $school): JsonResponse
    {
        $supervisions = $school->supervisions()
            ->with(['user', 'indicators'])
            ->orderBy('date', 'desc')
            ->paginate(15);

        return response()->json($supervisions);
    }
}
