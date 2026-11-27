<?php

namespace App\Livewire\Manual;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Livewire\Component;

class ManualIndex extends Component
{
    public function render()
    {
        $path = base_path('USER_MANUAL.md');

        $html = is_file($path)
            ? Cache::remember('manual.html.' . filemtime($path), 86400, fn () => Str::markdown(
                file_get_contents($path),
                ['html_input' => 'escape', 'allow_unsafe_links' => false]
            ))
            : '<p>ไม่พบไฟล์คู่มือการใช้งาน</p>';

        return view('livewire.manual.manual-index', ['html' => $html])
            ->layout('layouts.app', ['title' => 'คู่มือการใช้งาน', 'header' => 'คู่มือการใช้งานระบบ']);
    }
}
