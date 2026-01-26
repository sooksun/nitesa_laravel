<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    /**
     * Get activity logs with filters and pagination
     */
    public function index(Request $request): JsonResponse
    {
        $query = Activity::with(['causer', 'subject'])
            ->orderBy('created_at', 'desc');

        // Search filter
        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('description', 'like', "%{$request->search}%")
                    ->orWhere('event', 'like', "%{$request->search}%");
            });
        }

        // Event filter
        if ($request->has('event') && $request->event) {
            $query->where('event', $request->event);
        }

        // Subject type filter
        if ($request->has('subject_type') && $request->subject_type) {
            $query->where('subject_type', $request->subject_type);
        }

        // Causer filter
        if ($request->has('causer_id') && $request->causer_id) {
            $query->where('causer_id', $request->causer_id);
        }

        // Date range filter
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Pagination
        $perPage = $request->get('per_page', 20);
        $activities = $query->paginate($perPage);

        return response()->json($activities);
    }

    /**
     * Get a specific activity log
     */
    public function show(Activity $activity): JsonResponse
    {
        $activity->load(['causer', 'subject']);

        return response()->json($activity);
    }

    /**
     * Get activity statistics
     */
    public function stats(): JsonResponse
    {
        $stats = [
            'total' => Activity::count(),
            'today' => Activity::whereDate('created_at', today())->count(),
            'this_week' => Activity::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'this_month' => Activity::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            'unique_causers' => Activity::distinct('causer_id')->count('causer_id'),
            'events' => Activity::distinct()->pluck('event')->filter()->sort()->values(),
            'subject_types' => Activity::distinct()
                ->pluck('subject_type')
                ->filter()
                ->map(fn($type) => [
                    'value' => $type,
                    'label' => class_basename($type),
                ])
                ->sortBy('label')
                ->values(),
        ];

        return response()->json($stats);
    }

    /**
     * Get activities by causer (user)
     */
    public function byCauser(Request $request, string $causerId): JsonResponse
    {
        $query = Activity::with(['causer', 'subject'])
            ->where('causer_id', $causerId)
            ->orderBy('created_at', 'desc');

        $perPage = $request->get('per_page', 20);
        $activities = $query->paginate($perPage);

        return response()->json($activities);
    }

    /**
     * Get activities by subject
     */
    public function bySubject(Request $request, string $subjectType, string $subjectId): JsonResponse
    {
        $query = Activity::with(['causer', 'subject'])
            ->where('subject_type', $subjectType)
            ->where('subject_id', $subjectId)
            ->orderBy('created_at', 'desc');

        $perPage = $request->get('per_page', 20);
        $activities = $query->paginate($perPage);

        return response()->json($activities);
    }

    /**
     * Get recent activities (last 24 hours)
     */
    public function recent(): JsonResponse
    {
        $activities = Activity::with(['causer', 'subject'])
            ->where('created_at', '>=', now()->subDay())
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return response()->json($activities);
    }

    /**
     * Export activity logs (CSV format)
     */
    public function export(Request $request)
    {
        $query = Activity::with(['causer', 'subject'])
            ->orderBy('created_at', 'desc');

        // Apply same filters as index
        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('description', 'like', "%{$request->search}%")
                    ->orWhere('event', 'like', "%{$request->search}%");
            });
        }

        if ($request->has('event') && $request->event) {
            $query->where('event', $request->event);
        }

        if ($request->has('subject_type') && $request->subject_type) {
            $query->where('subject_type', $request->subject_type);
        }

        if ($request->has('causer_id') && $request->causer_id) {
            $query->where('causer_id', $request->causer_id);
        }

        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $activities = $query->limit(10000)->get(); // Limit export to 10k records

        // Create CSV
        $filename = 'activity-log-' . now()->format('Y-m-d-His') . '.csv';
        $handle = fopen('php://temp', 'r+');

        // Header
        fputcsv($handle, [
            'ID',
            'Event',
            'Description',
            'Subject Type',
            'Subject ID',
            'Causer Name',
            'Causer Email',
            'Created At',
        ]);

        // Data rows
        foreach ($activities as $activity) {
            fputcsv($handle, [
                $activity->id,
                $activity->event,
                $activity->description,
                $activity->subject_type ? class_basename($activity->subject_type) : '',
                $activity->subject_id,
                $activity->causer?->name ?? 'System',
                $activity->causer?->email ?? '',
                $activity->created_at->format('Y-m-d H:i:s'),
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response($csv, 200)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }
}
