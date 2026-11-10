<?php

namespace App\Livewire\School;

use App\Models\School;
use Livewire\Component;

class SchoolShow extends Component
{
    public School $school;

    public function mount(School $school)
    {
        if (! auth()->user()->canViewSchool($school)) {
            abort(403, 'คุณไม่มีสิทธิ์เข้าถึงโรงเรียนนี้');
        }

        $this->school = $school->load([
            'networkGroupRelation',
            'supervisors',
            'supervisions' => fn($q) => $q->with('user')
                ->when(auth()->user()->isSchool(), fn($q) => $q->where('status', \App\Enums\SupervisionStatus::PUBLISHED))
                ->latest('date')
                ->limit(10),
        ]);
    }

    public function render()
    {
        return view('livewire.school.school-show')
            ->layout('layouts.app', [
                'title' => $this->school->name,
                'header' => 'รายละเอียดโรงเรียน',
            ]);
    }
}
