<?php

namespace App\Livewire\Policy;

use App\Enums\PolicyType;
use App\Livewire\Traits\WithSweetAlert;
use App\Models\Policy;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class PolicyList extends Component
{
    use WithPagination, WithSweetAlert;

    public string $search = '';
    public string $type = '';
    public string $status = '';
    public ?string $deleteId = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'type' => ['except' => ''],
        'status' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function toggleActive(Policy $policy)
    {
        $policy->update(['isActive' => !$policy->isActive]);
        $this->swalSuccess($policy->isActive ? 'เปิดใช้งานนโยบายแล้ว' : 'ปิดใช้งานนโยบายแล้ว');
    }

    public function confirmDelete(string $id)
    {
        $this->deleteId = $id;
        $this->swalConfirmDelete('doDeletePolicy', 'นโยบาย');
    }

    #[On('doDeletePolicy')]
    public function deletePolicy()
    {
        if ($this->deleteId) {
            $policy = Policy::find($this->deleteId);
            if ($policy) {
                $policy->delete();
                $this->swalSuccess('ลบนโยบายเรียบร้อยแล้ว');
            }
            $this->deleteId = null;
        }
    }

    public function getPolicyTypesProperty()
    {
        return PolicyType::cases();
    }

    public function render()
    {
        $policies = Policy::query()
            ->withCount(['ministerSupervisions', 'obecSupervisions', 'areaSupervisions'])
            ->when($this->search, function ($q) {
                $q->where(function ($q) {
                    $q->where('title', 'like', "%{$this->search}%")
                      ->orWhere('code', 'like', "%{$this->search}%");
                });
            })
            ->when($this->type, fn($q) => $q->where('type', $this->type))
            ->when($this->status !== '', fn($q) => $q->where('isActive', $this->status === 'active'))
            ->orderBy('code')
            ->paginate(15);

        return view('livewire.policy.policy-list', compact('policies'))
            ->layout('layouts.app', ['title' => 'จัดการนโยบาย', 'header' => 'จัดการนโยบาย']);
    }
}
