<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ImportController;
use App\Livewire\ActivityLog\ActivityLogIndex;
use App\Livewire\Dashboard\DashboardSummary;
use App\Livewire\Import\ImportIndex;
use App\Livewire\Policy\PolicyForm;
use App\Livewire\Policy\PolicyList;
use App\Livewire\Profile\ProfileEdit;
use App\Livewire\Report\ReportIndex;
use App\Livewire\School\SchoolForm;
use App\Livewire\School\SchoolList;
use App\Livewire\School\SchoolShow;
use App\Livewire\Settings\SettingsIndex;
use App\Livewire\Supervision\AcknowledgeForm;
use App\Livewire\Supervision\SupervisionForm;
use App\Livewire\Supervision\SupervisionList;
use App\Livewire\Supervision\SupervisionShow;
use App\Livewire\User\UserForm;
use App\Livewire\User\UserList;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/', fn() => redirect()->route('login'));
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:login');

    // Google OAuth routes
    Route::get('/auth/google', [LoginController::class, 'redirectToGoogle'])->name('auth.google');
    Route::get('/auth/google/callback', [LoginController::class, 'handleGoogleCallback'])->name('auth.google.callback');
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Redirect /home to /dashboard
    Route::get('/home', fn() => redirect()->route('dashboard'));

    // Dashboard
    Route::get('/dashboard', DashboardSummary::class)->name('dashboard');

    // Profile
    Route::get('/profile', ProfileEdit::class)->name('profile');

    // Schools
    Route::prefix('schools')->name('schools.')->group(function () {
        Route::get('/', SchoolList::class)->name('index');
        Route::get('/create', SchoolForm::class)->name('create')->middleware('role:ADMIN');
        Route::get('/{school}', SchoolShow::class)->name('show');
        Route::get('/{school}/edit', SchoolForm::class)->name('edit')->middleware('role:ADMIN');
    });

    // Users (Admin only)
    Route::prefix('users')->name('users.')->middleware('role:ADMIN')->group(function () {
        Route::get('/', UserList::class)->name('index');
        Route::get('/create', UserForm::class)->name('create');
        Route::get('/{user}/edit', UserForm::class)->name('edit');
    });

    // Policies (Admin only)
    Route::prefix('policies')->name('policies.')->middleware('role:ADMIN')->group(function () {
        Route::get('/', PolicyList::class)->name('index');
        Route::get('/create', PolicyForm::class)->name('create');
        Route::get('/{policy}/edit', PolicyForm::class)->name('edit');
    });

    // Import (Admin only)
    Route::get('/import', ImportIndex::class)->name('import.index')->middleware('role:ADMIN');
    Route::get('/import/template/{type}', [ImportController::class, 'downloadTemplate'])->name('import.template')->middleware('role:ADMIN');

    // Supervisions
    Route::prefix('supervisions')->name('supervisions.')->group(function () {
        Route::get('/', SupervisionList::class)->name('index');
        Route::get('/create', SupervisionForm::class)->name('create')->middleware('role:ADMIN,SUPERVISOR');
        Route::get('/{supervision}', SupervisionShow::class)->name('show');
        Route::get('/{supervision}/edit', SupervisionForm::class)->name('edit')->middleware('role:ADMIN,SUPERVISOR');
        Route::get('/{supervision}/acknowledge', AcknowledgeForm::class)->name('acknowledge')->middleware('role:SCHOOL');
    });

    // Reports
    Route::get('/reports', ReportIndex::class)->name('reports.index');

    // Activity Log (Admin and Executive)
    Route::get('/activity-log', ActivityLogIndex::class)->name('activity-log.index')->middleware('role:ADMIN,EXECUTIVE');

    // Attachments
    Route::prefix('attachments')->name('attachments.')->group(function () {
        Route::get('/{attachment}/download', [\App\Http\Controllers\AttachmentController::class, 'download'])->name('download');
        Route::get('/{attachment}/view', [\App\Http\Controllers\AttachmentController::class, 'view'])->name('view');
    });

    // Settings (Admin only)
    Route::get('/settings', SettingsIndex::class)->name('settings.index')->middleware('role:ADMIN');
});
