<?php

use App\Http\Controllers\Api\ActivityLogController;
use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\PolicyController;
use App\Http\Controllers\Api\SchoolController;
use App\Http\Controllers\Api\SupervisionController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {
    // Current user
    Route::get('/user', fn(Request $request) => $request->user());

    // Schools
    Route::get('/schools', [SchoolController::class, 'index'])->name('api.schools.index');
    Route::get('/schools/{school}', [SchoolController::class, 'show'])->name('api.schools.show');
    Route::get('/schools/{school}/supervisions', [SchoolController::class, 'supervisions'])
        ->middleware('role:ADMIN,SUPERVISOR,EXECUTIVE')
        ->name('api.schools.supervisions');
    Route::middleware('role:ADMIN')->group(function () {
        Route::post('/schools', [SchoolController::class, 'store'])->name('api.schools.store');
        Route::match(['put', 'patch'], '/schools/{school}', [SchoolController::class, 'update'])->name('api.schools.update');
        Route::delete('/schools/{school}', [SchoolController::class, 'destroy'])->name('api.schools.destroy');
    });

    // Users (Admin only)
    Route::middleware('role:ADMIN')->group(function () {
        Route::apiResource('users', UserController::class)->names('api.users');
        Route::post('/users/{user}/assign-schools', [UserController::class, 'assignSchools'])->name('api.users.assign-schools');
    });

    // Policies
    Route::get('/policies', [PolicyController::class, 'index'])->name('api.policies.index');
    Route::get('/policies/{policy}', [PolicyController::class, 'show'])->name('api.policies.show');
    Route::middleware('role:ADMIN')->group(function () {
        Route::post('/policies', [PolicyController::class, 'store'])->name('api.policies.store');
        Route::match(['put', 'patch'], '/policies/{policy}', [PolicyController::class, 'update'])->name('api.policies.update');
        Route::delete('/policies/{policy}', [PolicyController::class, 'destroy'])->name('api.policies.destroy');
    });

    // Supervisions
    Route::get('/supervisions', [SupervisionController::class, 'index'])->name('api.supervisions.index');
    Route::get('/supervisions/{supervision}', [SupervisionController::class, 'show'])->name('api.supervisions.show');
    Route::middleware('role:ADMIN,SUPERVISOR')->group(function () {
        Route::post('/supervisions', [SupervisionController::class, 'store'])->name('api.supervisions.store');
        Route::match(['put', 'patch'], '/supervisions/{supervision}', [SupervisionController::class, 'update'])->name('api.supervisions.update');
        Route::delete('/supervisions/{supervision}', [SupervisionController::class, 'destroy'])->name('api.supervisions.destroy');
        Route::post('/supervisions/{supervision}/submit', [SupervisionController::class, 'submit']);
    });
    Route::middleware('role:ADMIN,EXECUTIVE')->group(function () {
        Route::post('/supervisions/{supervision}/approve', [SupervisionController::class, 'approve']);
        Route::post('/supervisions/{supervision}/reject', [SupervisionController::class, 'reject']);
    });
    Route::post('/supervisions/{supervision}/publish', [SupervisionController::class, 'publish'])->middleware('role:ADMIN,SUPERVISOR');
    Route::post('/supervisions/{supervision}/acknowledge', [SupervisionController::class, 'acknowledge'])->middleware('role:SCHOOL');

    // Analytics
    Route::prefix('analytics')->middleware('role:ADMIN,SUPERVISOR,EXECUTIVE')->group(function () {
        Route::get('/stats', [AnalyticsController::class, 'stats']);
        Route::get('/supervision-status', [AnalyticsController::class, 'supervisionStatus']);
        Route::get('/policy-usage', [AnalyticsController::class, 'policyUsage']);
        Route::get('/indicator-radar', [AnalyticsController::class, 'indicatorRadar']);
        Route::get('/academic-years', [AnalyticsController::class, 'academicYears']);
        Route::get('/districts', [AnalyticsController::class, 'districts']);
        Route::get('/network-groups', [AnalyticsController::class, 'networkGroups']);
    });

    // Activity Log (Admin and Executive only)
    Route::prefix('activity-log')->middleware('role:ADMIN')->group(function () {
        Route::get('/', [ActivityLogController::class, 'index']);
        Route::get('/stats', [ActivityLogController::class, 'stats']);
        Route::get('/recent', [ActivityLogController::class, 'recent']);
        Route::get('/export', [ActivityLogController::class, 'export']);
        Route::get('/causer/{causerId}', [ActivityLogController::class, 'byCauser']);
        Route::get('/subject/{subjectType}/{subjectId}', [ActivityLogController::class, 'bySubject']);
        Route::get('/{activity}', [ActivityLogController::class, 'show']);
    });
});
