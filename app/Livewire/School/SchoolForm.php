<?php

namespace App\Livewire\School;

use App\Models\NetworkGroup;
use App\Models\School;
use Livewire\Component;

class SchoolForm extends Component
{
    public ?School $school = null;
    public bool $editing = false;

    public string $code = '';
    public string $name = '';
    public string $province = '';
    public string $district = '';
    public string $subDistrict = '';
    public string $address = '';
    public string $phone = '';
    public string $email = '';
    public string $principalName = '';
    public ?int $studentCount = null;
    public ?int $teacherCount = null;
    public ?string $networkGroupId = null;

    protected function rules(): array
    {
        $uniqueRule = $this->editing 
            ? 'unique:school,code,' . $this->school->id 
            : 'unique:school,code';

        return [
            'code' => ['required', 'string', 'max:20', $uniqueRule],
            'name' => ['required', 'string', 'max:255'],
            'province' => ['nullable', 'string', 'max:100'],
            'district' => ['required', 'string', 'max:100'],
            'subDistrict' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:500'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'principalName' => ['nullable', 'string', 'max:255'],
            'studentCount' => ['nullable', 'integer', 'min:0'],
            'teacherCount' => ['nullable', 'integer', 'min:0'],
            'networkGroupId' => ['nullable', 'exists:networkgroup,id'],
        ];
    }

    protected $messages = [
        'code.required' => 'กรุณาระบุรหัสโรงเรียน',
        'code.unique' => 'รหัสโรงเรียนนี้มีอยู่แล้ว',
        'name.required' => 'กรุณาระบุชื่อโรงเรียน',
        'district.required' => 'กรุณาระบุอำเภอ',
        'email.email' => 'รูปแบบอีเมลไม่ถูกต้อง',
    ];

    public function mount(?School $school = null)
    {
        if ($school && $school->exists) {
            $this->school = $school;
            $this->editing = true;
            $this->fill($school->toArray());
        }
    }

    public function save()
    {
        $validated = $this->validate();

        if ($this->editing) {
            $this->school->update($validated);
            $message = 'อัปเดตข้อมูลโรงเรียนเรียบร้อยแล้ว';
        } else {
            School::create($validated);
            $message = 'เพิ่มโรงเรียนเรียบร้อยแล้ว';
        }

        return redirect()->route('schools.index')->with('success', $message);
    }

    public function getNetworkGroupsProperty()
    {
        return NetworkGroup::orderBy('name')->get();
    }

    public function render()
    {
        $title = $this->editing ? 'แก้ไขโรงเรียน' : 'เพิ่มโรงเรียนใหม่';
        
        return view('livewire.school.school-form')
            ->layout('layouts.app', ['title' => $title, 'header' => $title]);
    }
}
