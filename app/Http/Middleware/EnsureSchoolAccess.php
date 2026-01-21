<?php

namespace App\Http\Middleware;

use App\Enums\Role;
use App\Models\School;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSchoolAccess
{
    /**
     * Handle an incoming request.
     * Ensures the user has access to the specified school.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $schoolId = $request->route('school') ?? $request->input('school_id');

        if (!$schoolId) {
            return $next($request);
        }

        $school = $schoolId instanceof School ? $schoolId : School::find($schoolId);

        if (!$school) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'ไม่พบโรงเรียน'], 404);
            }
            abort(404, 'ไม่พบโรงเรียน');
        }

        // Admin can access all schools
        if ($user->role === Role::ADMIN) {
            return $next($request);
        }

        // Executive can view all schools
        if ($user->role === Role::EXECUTIVE) {
            return $next($request);
        }

        // Supervisor can only access assigned schools
        if ($user->role === Role::SUPERVISOR) {
            if (!$user->assignedSchools()->where('schools.id', $school->id)->exists()) {
                if ($request->expectsJson()) {
                    return response()->json(['message' => 'คุณไม่มีสิทธิ์เข้าถึงโรงเรียนนี้'], 403);
                }
                abort(403, 'คุณไม่มีสิทธิ์เข้าถึงโรงเรียนนี้');
            }
        }

        return $next($request);
    }
}
