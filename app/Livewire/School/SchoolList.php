<?php

namespace App\Livewire\School;

use App\Livewire\Traits\WithSweetAlert;
use App\Models\NetworkGroup;
use App\Models\School;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class SchoolList extends Component
{
    use WithPagination;
    use WithSweetAlert;

    public string $search = '';
    public string $district = '';
    public string $networkGroupId = '';
    public string $sortBy = 'name';
    public string $sortDirection = 'asc';
    public ?string $deleteId = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'district' => ['except' => ''],
        'networkGroupId' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function sortBy($column)
    {
        if (! in_array($column, ['name', 'code', 'district', 'studentCount', 'teacherCount'], true)) {
            return;
        }

        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    public function confirmDelete(string $id)
    {
        abort_unless(auth()->user()->isAdmin(), 403);
        $this->deleteId = $id;
        $this->swalConfirmDelete('doDeleteSchool', 'โรงเรียน');
    }

    #[On('doDeleteSchool')]
    public function deleteSchool()
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        if ($this->deleteId) {
            $school = School::find($this->deleteId);
            if ($school) {
                $school->delete();
                $this->swalSuccess('ลบโรงเรียนเรียบร้อยแล้ว');
            }
            $this->deleteId = null;
        }
    }

    public function getDistrictsProperty()
    {
        return School::distinct()->orderBy('district')->pluck('district')->filter();
    }

    public function getNetworkGroupsProperty()
    {
        return NetworkGroup::orderBy('name')->get();
    }

    public function render()
    {
        $query = School::query()
            ->with('networkGroupRelation')
            ->withCount('supervisions')
            ->when($this->search, function ($q) {
                $q->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                      ->orWhere('code', 'like', "%{$this->search}%");
                });
            })
            ->when($this->district, fn($q) => $q->where('district', $this->district))
            ->when($this->networkGroupId, fn($q) => $q->where('networkGroupId', $this->networkGroupId))
            ->orderBy(
                in_array($this->sortBy, ['name', 'code', 'district', 'studentCount', 'teacherCount'], true) ? $this->sortBy : 'name',
                $this->sortDirection === 'desc' ? 'desc' : 'asc'
            );

        $scopedIds = auth()->user()->scopedSchoolIds();
        if ($scopedIds !== null) {
            $query->whereIn('id', $scopedIds);
        }

        $schools = $query->paginate(15);

        return view('livewire.school.school-list', compact('schools'))
            ->layout('layouts.app', ['title' => 'จัดการโรงเรียน', 'header' => 'จัดการโรงเรียน']);
    }
}
