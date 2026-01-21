<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Policy;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PolicyController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Policy::query();

        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                  ->orWhere('code', 'like', "%{$request->search}%");
            });
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->boolean('active_only', false)) {
            $query->where('isActive', true);
        }

        $policies = $query->orderBy('code')->paginate($request->per_page ?? 15);

        return response()->json($policies);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'required|string',
            'code' => 'required|string|max:50|unique:policy,code',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'isActive' => 'boolean',
        ]);

        $policy = Policy::create($validated);

        return response()->json($policy, 201);
    }

    public function show(Policy $policy): JsonResponse
    {
        return response()->json($policy);
    }

    public function update(Request $request, Policy $policy): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'required|string',
            'code' => 'required|string|max:50|unique:policy,code,' . $policy->id,
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'isActive' => 'boolean',
        ]);

        $policy->update($validated);

        return response()->json($policy);
    }

    public function destroy(Policy $policy): JsonResponse
    {
        $policy->delete();

        return response()->json(null, 204);
    }
}
