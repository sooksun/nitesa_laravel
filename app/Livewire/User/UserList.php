<?php

namespace App\Livewire\User;

use App\Enums\Role;
use App\Livewire\Traits\WithSweetAlert;
use App\Models\User;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class UserList extends Component
{
    use WithPagination, WithSweetAlert;

    public string $search = '';
    public string $role = '';
    public ?string $deleteId = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'role' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function confirmDelete(string $id)
    {
        $this->deleteId = $id;
        $this->swalConfirmDelete('doDeleteUser', 'ผู้ใช้');
    }

    #[On('doDeleteUser')]
    public function deleteUser()
    {
        if ($this->deleteId) {
            $user = User::find($this->deleteId);
            
            if ($user && $user->id === auth()->id()) {
                $this->swalError('ไม่สามารถลบบัญชีตัวเองได้');
                $this->deleteId = null;
                return;
            }

            if ($user) {
                $user->delete();
                $this->swalSuccess('ลบผู้ใช้เรียบร้อยแล้ว');
            }
            $this->deleteId = null;
        }
    }

    public function getRolesProperty()
    {
        return Role::cases();
    }

    public function render()
    {
        $users = User::query()
            ->when($this->search, function ($q) {
                $q->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                      ->orWhere('email', 'like', "%{$this->search}%");
                });
            })
            ->when($this->role, fn($q) => $q->where('role', $this->role))
            ->orderBy('name')
            ->paginate(15);

        return view('livewire.user.user-list', compact('users'))
            ->layout('layouts.app', ['title' => 'จัดการผู้ใช้งาน', 'header' => 'จัดการผู้ใช้งาน']);
    }
}
