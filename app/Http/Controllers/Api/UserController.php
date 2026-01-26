<?php

namespace App\Http\Controllers\Api;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = User::query()
            ->withCount(['assignedSchools', 'supervisions']);

        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        $users = $query->orderBy('name')->paginate($request->per_page ?? 15);

        return response()->json($users);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:user,email',
            'password' => 'required|string|min:8',
            'role' => 'required|in:ADMIN,SUPERVISOR,SCHOOL,EXECUTIVE',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        return response()->json($user, 201);
    }

    public function show(User $user): JsonResponse
    {
        $user->load(['assignedSchools', 'supervisions']);

        return response()->json($user);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:user,email,' . $user->id,
            'password' => 'nullable|string|min:8',
            'role' => 'required|in:ADMIN,SUPERVISOR,SCHOOL,EXECUTIVE',
        ]);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
        ];

        if (! empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        return response()->json($user);
    }

    public function destroy(User $user): JsonResponse
    {
        $user->delete();

        return response()->json(null, 204);
    }

    public function assignSchools(Request $request, User $user): JsonResponse
    {
        if ($user->role !== Role::SUPERVISOR) {
            return response()->json(['message' => 'ผู้ใช้ต้องเป็นศึกษานิเทศก์เท่านั้น'], 403);
        }

        $validated = $request->validate([
            'school_ids' => 'required|array',
            'school_ids.*' => 'exists:school,id',
        ]);

        $user->assignedSchools()->sync($validated['school_ids']);

        $user->load('assignedSchools');

        return response()->json($user);
    }
}
