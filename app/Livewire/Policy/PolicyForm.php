<?php

namespace App\Livewire\Policy;

use App\Enums\PolicyType;
use App\Models\Policy;
use Livewire\Component;

class PolicyForm extends Component
{
    public ?Policy $policy = null;
    public bool $editing = false;

    public string $type = '';
    public string $code = '';
    public string $title = '';
    public string $description = '';
    public bool $isActive = true;

    protected function rules(): array
    {
        return [
            'type' => ['required', 'string'],
            'code' => ['required', 'string', 'max:20'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'isActive' => ['boolean'],
        ];
    }

    protected $messages = [
        'type.required' => 'กรุณาเลือกประเภทนโยบาย',
        'code.required' => 'กรุณาระบุรหัสนโยบาย',
        'title.required' => 'กรุณาระบุชื่อนโยบาย',
    ];

    public function mount(?Policy $policy = null)
    {
        if ($policy && $policy->exists) {
            $this->policy = $policy;
            $this->editing = true;
            $this->type = $policy->type->value;
            $this->code = $policy->code;
            $this->title = $policy->title;
            $this->description = $policy->description ?? '';
            $this->isActive = $policy->isActive;
        }
    }

    public function save()
    {
        $validated = $this->validate();

        if ($this->editing) {
            $this->policy->update($validated);
            $message = 'อัปเดตนโยบายเรียบร้อยแล้ว';
        } else {
            Policy::create($validated);
            $message = 'เพิ่มนโยบายเรียบร้อยแล้ว';
        }

        return redirect()->route('policies.index')->with('success', $message);
    }

    public function getPolicyTypesProperty()
    {
        return PolicyType::cases();
    }

    public function render()
    {
        $title = $this->editing ? 'แก้ไขนโยบาย' : 'เพิ่มนโยบายใหม่';
        
        return view('livewire.policy.policy-form')
            ->layout('layouts.app', ['title' => $title, 'header' => $title]);
    }
}
