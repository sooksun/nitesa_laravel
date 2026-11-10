<?php

namespace App\Livewire\Supervision;

use App\Enums\SupervisionStatus;
use App\Livewire\Traits\WithSweetAlert;
use App\Models\School;
use App\Models\Supervision;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class SupervisionList extends Component
{
    use WithPagination;
    use WithSweetAlert;

    public string $search = '';
    public string $status = '';
    public string $schoolId = '';
    public string $academicYear = '';
    public ?string $deleteId = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
        'schoolId' => ['except' => ''],
        'academicYear' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function getStatusesProperty()
    {
        return SupervisionStatus::cases();
    }

    public function getSchoolsProperty()
    {
        $query = School::orderBy('name');

        $scopedIds = auth()->user()->scopedSchoolIds();
        if ($scopedIds !== null) {
            $query->whereIn('id', $scopedIds);
        }

        return $query->get();
    }

    public function getAcademicYearsProperty()
    {
        return Supervision::distinct()
            ->orderBy('academicYear', 'desc')
            ->pluck('academicYear')
            ->filter()
            ->values();
    }

    public function confirmDelete(string $id)
    {
        $this->deleteId = $id;
        $this->swalConfirmDelete('doDeleteSupervision', 'การนิเทศ');
    }

    #[On('doDeleteSupervision')]
    public function deleteSupervision()
    {
        if (! $this->deleteId) {
            return;
        }

        $supervision = Supervision::find($this->deleteId);
        if (! $supervision) {
            $this->deleteId = null;

            return;
        }

        // Only allow deletion of drafts by the owner or admin
        if ($supervision->status !== SupervisionStatus::DRAFT && ! auth()->user()->isAdmin()) {
            $this->swalError('ไม่สามารถลบการนิเทศที่ส่งแล้วได้');
            $this->deleteId = null;

            return;
        }

        if (! auth()->user()->isAdmin() && $supervision->userId !== auth()->id()) {
            $this->swalError('คุณไม่มีสิทธิ์ลบการนิเทศนี้');
            $this->deleteId = null;

            return;
        }

        $supervision->delete();
        $this->swalSuccess('ลบการนิเทศเรียบร้อยแล้ว');
        $this->deleteId = null;
    }

    public function render()
    {
        $query = Supervision::with(['school', 'user'])
            ->when($this->search, function ($q) {
                $q->whereHas('school', fn($q) => $q->where('name', 'like', "%{$this->search}%"));
            })
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->when($this->schoolId, fn($q) => $q->where('schoolId', $this->schoolId))
            ->when($this->academicYear, fn($q) => $q->where('academicYear', $this->academicYear))
            ->orderBy('date', 'desc');

        // SUPERVISOR: assigned schools; SCHOOL: own school only
        $scopedIds = auth()->user()->scopedSchoolIds();
        if ($scopedIds !== null) {
            $query->whereIn('schoolId', $scopedIds);
        }

        if (auth()->user()->isSchool()) {
            $query->where('status', SupervisionStatus::PUBLISHED);
        }

        $supervisions = $query->paginate(15);

        return view('livewire.supervision.supervision-list', compact('supervisions'))
            ->layout('layouts.app', ['title' => 'การนิเทศทั้งหมด', 'header' => 'การนิเทศทั้งหมด']);
    }
}
