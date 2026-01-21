<?php

namespace App\Livewire\Settings;

use App\Models\SystemSetting;
use Livewire\Component;

class SettingsIndex extends Component
{
    public string $site_name = 'NITESA';
    public string $area_office_name = '';
    public string $current_academic_year = '';
    public bool $allow_registration = false;

    public function mount()
    {
        $this->site_name = SystemSetting::get('site_name', 'NITESA');
        $this->area_office_name = SystemSetting::get('area_office_name', '');
        $this->current_academic_year = SystemSetting::get('current_academic_year', (string)(date('Y') + 543));
        $this->allow_registration = SystemSetting::get('allow_registration', false);
    }

    public function save()
    {
        SystemSetting::set('site_name', $this->site_name, 'ชื่อระบบ');
        SystemSetting::set('area_office_name', $this->area_office_name, 'ชื่อสำนักงานเขตพื้นที่');
        SystemSetting::set('current_academic_year', $this->current_academic_year, 'ปีการศึกษาปัจจุบัน');
        SystemSetting::set('allow_registration', $this->allow_registration, 'เปิด/ปิดการลงทะเบียน');

        session()->flash('success', 'บันทึกการตั้งค่าเรียบร้อยแล้ว');
    }

    public function render()
    {
        return view('livewire.settings.settings-index')
            ->layout('layouts.app', ['title' => 'ตั้งค่าระบบ', 'header' => 'ตั้งค่าระบบ']);
    }
}
