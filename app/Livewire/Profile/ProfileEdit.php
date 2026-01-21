<?php

namespace App\Livewire\Profile;

use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class ProfileEdit extends Component
{
    public string $name = '';
    public string $email = '';
    public string $current_password = '';
    public string $new_password = '';
    public string $new_password_confirmation = '';

    public function mount()
    {
        $this->name = auth()->user()->name;
        $this->email = auth()->user()->email;
    }

    public function updateProfile()
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . auth()->id()],
        ]);

        auth()->user()->update([
            'name' => $this->name,
            'email' => $this->email,
        ]);

        session()->flash('success', 'อัปเดตข้อมูลเรียบร้อยแล้ว');
    }

    public function updatePassword()
    {
        $this->validate([
            'current_password' => ['required'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (!Hash::check($this->current_password, auth()->user()->password)) {
            $this->addError('current_password', 'รหัสผ่านปัจจุบันไม่ถูกต้อง');
            return;
        }

        auth()->user()->update([
            'password' => Hash::make($this->new_password),
        ]);

        $this->reset(['current_password', 'new_password', 'new_password_confirmation']);

        session()->flash('success', 'เปลี่ยนรหัสผ่านเรียบร้อยแล้ว');
    }

    public function render()
    {
        return view('livewire.profile.profile-edit')
            ->layout('layouts.app', ['title' => 'โปรไฟล์', 'header' => 'โปรไฟล์']);
    }
}
