<?php

namespace App\Livewire\User;

use App\Enums\Role;
use App\Models\School;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class UserForm extends Component
{
    public ?User $user = null;
    public bool $editing = false;

    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $role = 'SCHOOL';
    public array $assignedSchools = [];
    public string $schoolId = '';

    protected function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'role' => ['required', 'in:ADMIN,SUPERVISOR,SCHOOL,EXECUTIVE'],
            'assignedSchools' => ['array'],
            'schoolId' => ['required_if:role,SCHOOL', 'nullable', 'exists:school,id'],
        ];

        if ($this->editing) {
            $rules['email'][] = 'unique:user,email,' . $this->user->id;
            $rules['password'] = ['nullable', 'string', 'min:8', 'confirmed'];
        } else {
            $rules['email'][] = 'unique:user,email';
            $rules['password'] = ['required', 'string', 'min:8', 'confirmed'];
        }

        return $rules;
    }

    protected $messages = [
        'name.required' => 'กรุณาระบุชื่อ',
        'email.required' => 'กรุณาระบุอีเมล',
        'email.email' => 'รูปแบบอีเมลไม่ถูกต้อง',
        'email.unique' => 'อีเมลนี้มีอยู่แล้วในระบบ',
        'password.required' => 'กรุณาระบุรหัสผ่าน',
        'password.min' => 'รหัสผ่านต้องมีอย่างน้อย 8 ตัวอักษร',
        'password.confirmed' => 'รหัสผ่านไม่ตรงกัน',
        'schoolId.required_if' => 'กรุณาเลือกโรงเรียนของผู้ใช้งาน',
    ];

    public function mount(?User $user = null)
    {
        if ($user && $user->exists) {
            $this->user = $user;
            $this->editing = true;
            $this->name = $user->name;
            $this->email = $user->email;
            $this->role = $user->role->value;
            $this->assignedSchools = $user->assignedSchools()->pluck('school.id')->toArray();
            $this->schoolId = $user->isSchool() ? (string) ($this->assignedSchools[0] ?? '') : '';
        }
    }

    public function save()
    {
        $validated = $this->validate();

        $userData = [
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
        ];

        if (! empty($this->password)) {
            $userData['password'] = Hash::make($this->password);
        }

        if ($this->editing) {
            $this->user->update($userData);
            $user = $this->user;
            $message = 'อัปเดตข้อมูลผู้ใช้เรียบร้อยแล้ว';
        } else {
            $user = User::create($userData);
            $message = 'เพิ่มผู้ใช้เรียบร้อยแล้ว';
        }

        // SUPERVISOR: schools in charge; SCHOOL: the single school the account belongs to
        if ($this->role === 'SUPERVISOR') {
            $user->assignedSchools()->sync($this->assignedSchools);
        } elseif ($this->role === 'SCHOOL') {
            $user->assignedSchools()->sync([$this->schoolId]);
        } else {
            $user->assignedSchools()->detach();
        }

        return redirect()->route('users.index')->with('success', $message);
    }

    public function getRolesProperty()
    {
        return Role::cases();
    }

    public function getSchoolsProperty()
    {
        return School::orderBy('name')->get();
    }

    public function render()
    {
        $title = $this->editing ? 'แก้ไขผู้ใช้' : 'เพิ่มผู้ใช้ใหม่';

        return view('livewire.user.user-form')
            ->layout('layouts.app', ['title' => $title, 'header' => $title]);
    }
}
