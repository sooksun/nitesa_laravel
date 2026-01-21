<?php

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
    Route::apiResource('schools', SchoolController::class);
    Route::get('/schools/{school}/supervisions', [SchoolController::class, 'supervisions']);

    // Users
    Route::apiResource('users', UserController::class);
    Route::post('/users/{user}/assign-schools', [UserController::class, 'assignSchools']);

    // Policies
    Route::apiResource('policies', PolicyController::class);

    // Supervisions
    Route::apiResource('supervisions', SupervisionController::class);
    Route::post('/supervisions/{supervision}/submit', [SupervisionController::class, 'submit']);
    Route::post('/supervisions/{supervision}/approve', [SupervisionController::class, 'approve']);
    Route::post('/supervisions/{supervision}/reject', [SupervisionController::class, 'reject']);
    Route::post('/supervisions/{supervision}/publish', [SupervisionController::class, 'publish']);
    Route::post('/supervisions/{supervision}/acknowledge', [SupervisionController::class, 'acknowledge']);

    // Analytics
    Route::prefix('analytics')->group(function () {
        Route::get('/stats', [AnalyticsController::class, 'stats']);
        Route::get('/supervision-status', [AnalyticsController::class, 'supervisionStatus']);
        Route::get('/policy-usage', [AnalyticsController::class, 'policyUsage']);
        Route::get('/indicator-radar', [AnalyticsController::class, 'indicatorRadar']);
        Route::get('/academic-years', [AnalyticsController::class, 'academicYears']);
        Route::get('/districts', [AnalyticsController::class, 'districts']);
        Route::get('/network-groups', [AnalyticsController::class, 'networkGroups']);
    });
});
