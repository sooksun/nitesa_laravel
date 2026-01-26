<?php

namespace App\Livewire\ActivityLog;

use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Activitylog\Models\Activity;

class ActivityLogIndex extends Component
{
    use WithPagination;

    // Filters
    public string $search = '';
    public string $eventFilter = '';
    public string $subjectTypeFilter = '';
    public string $causerFilter = '';
    public string $dateFrom = '';
    public string $dateTo = '';
    public int $perPage = 20;

    // Reset pagination when filters change
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingEventFilter()
    {
        $this->resetPage();
    }

    public function updatingSubjectTypeFilter()
    {
        $this->resetPage();
    }

    public function updatingCauserFilter()
    {
        $this->resetPage();
    }

    public function updatingDateFrom()
    {
        $this->resetPage();
    }

    public function updatingDateTo()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->reset([
            'search',
            'eventFilter',
            'subjectTypeFilter',
            'causerFilter',
            'dateFrom',
            'dateTo',
        ]);
        $this->resetPage();
    }

    public function getActivitiesProperty()
    {
        $query = Activity::with(['causer', 'subject'])
            ->orderBy('created_at', 'desc');

        // Search filter (ค้นหาในคำอธิบาย)
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('description', 'like', "%{$this->search}%")
                    ->orWhere('event', 'like', "%{$this->search}%");
            });
        }

        // Event filter
        if ($this->eventFilter) {
            $query->where('event', $this->eventFilter);
        }

        // Subject type filter
        if ($this->subjectTypeFilter) {
            $query->where('subject_type', $this->subjectTypeFilter);
        }

        // Causer filter (ผู้กระทำ)
        if ($this->causerFilter) {
            $query->where('causer_id', $this->causerFilter);
        }

        // Date range filter
        if ($this->dateFrom) {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }

        // Apply pagination
        return $query->paginate($this->perPage);
    }

    public function getEventsProperty()
    {
        return Activity::distinct()
            ->pluck('event')
            ->filter()
            ->sort()
            ->values();
    }

    public function getSubjectTypesProperty()
    {
        return Activity::distinct()
            ->pluck('subject_type')
            ->filter()
            ->map(fn($type) => [
                'value' => $type,
                'label' => class_basename($type),
            ])
            ->sortBy('label')
            ->values();
    }

    public function getCausersProperty()
    {
        return \App\Models\User::select('id', 'name', 'email')
            ->where('isActive', true)
            ->orderBy('name')
            ->get();
    }

    public function render()
    {
        return view('livewire.activity-log.activity-log-index', [
            'activities' => $this->activities,
            'events' => $this->events,
            'subjectTypes' => $this->subjectTypes,
            'causers' => $this->causers,
        ])->layout('layouts.app', [
            'title' => 'บันทึกกิจกรรมระบบ',
            'header' => 'บันทึกกิจกรรมระบบ (Activity Log)',
        ]);
    }
}
