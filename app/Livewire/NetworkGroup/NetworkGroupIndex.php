<?php

namespace App\Livewire\NetworkGroup;

use App\Livewire\Traits\WithSweetAlert;
use App\Models\NetworkGroup;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class NetworkGroupIndex extends Component
{
    use WithPagination;
    use WithSweetAlert;

    public string $search = '';

    public ?string $editingId = null;
    public bool $showForm = false;
    public string $code = '';
    public string $name = '';
    public string $description = '';

    public ?string $deleteId = null;

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    protected function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:50', Rule::unique('networkgroup', 'code')->ignore($this->editingId)],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }

    protected $messages = [
        'code.required' => 'กรุณาระบุรหัสกลุ่มเครือข่าย',
        'code.unique' => 'รหัสกลุ่มเครือข่ายนี้มีอยู่แล้ว',
        'name.required' => 'กรุณาระบุชื่อกลุ่มเครือข่าย',
    ];

    public function mount(): void
    {
        $this->authorizeAdmin();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        $this->authorizeAdmin();
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(string $id): void
    {
        $this->authorizeAdmin();
        $group = NetworkGroup::findOrFail($id);

        $this->resetValidation();
        $this->editingId = $group->id;
        $this->code = $group->code;
        $this->name = $group->name;
        $this->description = (string) $group->description;
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->authorizeAdmin();
        $data = $this->validate();
        $data['description'] = $data['description'] ?: null;

        if ($this->editingId) {
            NetworkGroup::findOrFail($this->editingId)->update($data);
            $message = 'อัปเดตกลุ่มเครือข่ายเรียบร้อยแล้ว';
        } else {
            NetworkGroup::create($data);
            $message = 'เพิ่มกลุ่มเครือข่ายเรียบร้อยแล้ว';
        }

        $this->resetForm();
        $this->swalSuccess($message);
    }

    public function cancel(): void
    {
        $this->resetForm();
    }

    public function confirmDelete(string $id): void
    {
        $this->authorizeAdmin();
        $this->deleteId = $id;
        $this->swalConfirmDelete('doDeleteNetworkGroup', 'กลุ่มเครือข่าย');
    }

    #[On('doDeleteNetworkGroup')]
    public function delete(): void
    {
        $this->authorizeAdmin();

        $group = $this->deleteId ? NetworkGroup::find($this->deleteId) : null;
        $this->deleteId = null;

        if (! $group) {
            return;
        }

        // FK is ON DELETE SET NULL: schools stay, they just lose the group
        $group->delete();
        $this->swalSuccess('ลบกลุ่มเครือข่ายเรียบร้อยแล้ว');
    }

    protected function resetForm(): void
    {
        $this->resetValidation();
        $this->editingId = null;
        $this->code = '';
        $this->name = '';
        $this->description = '';
        $this->showForm = false;
    }

    protected function authorizeAdmin(): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403);
    }

    public function render()
    {
        $groups = NetworkGroup::query()
            ->withCount('schools')
            ->when($this->search, function ($q) {
                $q->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                      ->orWhere('code', 'like', "%{$this->search}%");
                });
            })
            ->orderBy('code')
            ->paginate(15);

        return view('livewire.network-group.network-group-index', compact('groups'))
            ->layout('layouts.app', ['title' => 'กลุ่มเครือข่าย', 'header' => 'จัดการกลุ่มเครือข่าย']);
    }
}
