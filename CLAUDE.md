# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

NITESA — ระบบนิเทศ ติดตาม และประเมินผลการศึกษา (school supervision system for a Thai education service area office). Most domain language and UI strings are Thai. Backend is Laravel 10 (`laravel/framework: ^10.10` in `composer.json` — the README's "Laravel 11" claim is incorrect), PHP 8.1+, Livewire 3 + Tailwind + Alpine for UI, MySQL for storage, Sanctum for API auth.

## Common commands

Local dev (PHP server + Vite must both run):
```bash
php artisan serve            # http://localhost:8000
npm run dev                  # Vite HMR (separate terminal)
npm run build                # production assets
```

Database:
```bash
php artisan migrate                  # apply migrations
php artisan migrate --seed           # apply + seed initial data
php artisan db:seed                  # seed only
```

Lint / static analysis (PHP CS Fixer + Larastan, both wrapped in composer scripts):
```bash
composer lint            # fix style, then run phpstan
composer lint:fix        # PHP CS Fixer auto-fix
composer format:check    # dry-run diff
composer lint:stan       # phpstan analyse --memory-limit=512M (level 5, app/ only)
```

Tests (PHPUnit 10, configured in `phpunit.xml`):
```bash
php artisan test                                  # all suites
php artisan test --testsuite=Unit                 # Unit suite
php artisan test --testsuite=Feature              # Feature suite
php artisan test tests/Unit/ExampleTest.php       # single file
php artisan test --filter=testMethodName          # single test
```
Note: `tests/` currently only contains `ExampleTest.php` placeholders in both Unit and Feature — there is no real test coverage yet. PHPUnit's `<env DB_CONNECTION>` and `DB_DATABASE` overrides are commented out in `phpunit.xml`, so tests run against whatever DB the `.env` points at unless you uncomment them.

Docker (production-style stack — nginx, php-fpm 8.3, redis, queue worker; web on port 9000):
```bash
docker compose up -d --build
docker compose exec app php artisan migrate --force
docker compose exec app php artisan optimize
```
See `README.md` "Production Deployment (Docker)" and `DOCKER_SETUP.md` for the full sequence (key:generate, storage:link, livewire:publish --assets, etc.).

## Architecture

### Domain & roles
Four roles in `App\Enums\Role` (ADMIN, SUPERVISOR, SCHOOL, EXECUTIVE) drive all access control. Authorization is enforced two ways:

1. Route-level via the `role` middleware alias (`App\Http\Middleware\RoleMiddleware`, registered in `app/Http/Kernel.php`). Usage: `->middleware('role:ADMIN,SUPERVISOR')`. Roles are passed as the enum's string value and converted with `Role::from()` inside the middleware.
2. Per-school via `App\Http\Middleware\EnsureSchoolAccess` (alias `school.access`) and the `User::canManageSchool()` / `User::canAccessSupervision()` helpers. SUPERVISOR users only see schools in their `assignedSchools()` belongs-to-many relation; SCHOOL users only see supervisions whose `status === PUBLISHED`.

### Supervision workflow
State machine on `App\Models\Supervision`, stored as `App\Enums\SupervisionStatus`:
```
DRAFT ─► SUBMITTED ─► APPROVED ─► PUBLISHED
            │              ▲
            └► NEEDS_IMPROVEMENT ──┘  (re-submit)
```
The model owns the `can*()` guards (`canSubmit`, `canApprove`, `canPublish`, `canReject`) and the transition methods. **Always go through `App\Services\SupervisionService`** for create/update/submit/approve/reject/publish — it wraps writes in `DB::transaction`, syncs indicators (handles the empty-array delete-all case), logs failed transitions, and validates required fields (`summary`, `suggestions`, ≥1 indicator) before submission. Don't mutate `status` directly from controllers/Livewire components.

### Persistence conventions (important)
The schema does **not** follow Laravel defaults — be careful when writing models, queries, and migrations:

- **Table names** are lowercase singular: `user`, `school`, `supervision`, `policy`, `networkgroup`, `_supervisorschools` (pivot, with columns `A` = school_id and `B` = user_id). Each model sets `protected $table` explicitly.
- **Primary keys** are 26-char ULID strings generated in each model's `boot()` via `Str::ulid()`. Models declare `$keyType = 'string'` and `$incrementing = false`.
- **Timestamps** are camelCase: `createdAt` / `updatedAt`. Each model overrides `const CREATED_AT` / `UPDATED_AT`.
- **Other columns** are camelCase too (`isActive`, `googleId`, `schoolId`, `userId`, `academicYear`, `networkGroupId`, …). Eloquent attributes use camelCase as a result — e.g. `$supervision->schoolId`, not `school_id`.
- Enum-backed columns: `User.role` casts to `Role`, `Supervision.status` casts to `SupervisionStatus` (see each model's `$casts`).

When adding new columns or relationships, mirror these conventions; when querying, remember `where('isActive', true)` and `where('createdAt', …)` rather than the snake_case defaults.

### HTTP layers
Two parallel surfaces share the same models and `SupervisionService`:

- **Web (Livewire)** — `routes/web.php` maps directly to full-page Livewire components under `app/Livewire/{Dashboard,School,User,Policy,Supervision,Report,Import,Settings,Profile,ActivityLog}`. There are essentially no traditional web controllers besides Auth, Attachment, and Import. Components use the `App\Livewire\Traits\WithSweetAlert` trait to dispatch `swal:*` browser events for toasts/confirms (handled by JS that listens for those event names).
- **API** — `routes/api.php` is Sanctum-protected (`auth:sanctum`) and exposes `apiResource` controllers in `app/Http/Controllers/Api/` plus workflow actions (`/supervisions/{id}/submit|approve|reject|publish|acknowledge`) and an `analytics/*` group. Activity log endpoints are gated by `role:ADMIN,EXECUTIVE`.

### Other notable pieces
- `spatie/laravel-activitylog` records audit events (see `ACTIVITY_LOG_GUIDE.md` and `ActivityLogController`).
- `maatwebsite/excel` powers the Import flow (`ImportController` + `App\Livewire\Import`).
- Notifications under `app/Notifications/Supervision*Notification.php` fire on workflow transitions; queue worker is part of the Docker stack.
- Google OAuth login via `laravel/socialite` (`/auth/google`, `/auth/google/callback`).

## Working in this repo

- Mind the casing pitfalls above — using `created_at`, snake_case columns, or auto-increment IDs in new code will silently break against the existing schema.
- Don't bypass `SupervisionService` for workflow transitions; `canSubmit/canApprove/...` guards must be respected to keep the state machine valid.
- New Livewire components belong under the matching `app/Livewire/{Domain}/` folder and should be wired through `routes/web.php` (full-page components, not nested partials) following the existing pattern.
- New role-restricted routes must use the `role:` middleware (string-form roles) — Gate/Policy classes are not used.
- The codebase uses Thai for UI strings and many comments; preserve the language of nearby strings when editing.
- There are extensive Thai-language operator docs in the repo root (DOCKER_SETUP.md, PRODUCTION_DEPLOYMENT.md, NOTIFICATION_SETUP.md, USER_MANUAL.md, etc.). Consult them before changing deployment, notifications, file storage, or DB index strategy.
