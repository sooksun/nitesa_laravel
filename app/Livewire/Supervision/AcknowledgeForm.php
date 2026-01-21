<?php

namespace App\Livewire\Supervision;

use App\Enums\SupervisionStatus;
use App\Models\Acknowledgement;
use App\Models\Supervision;
use Livewire\Component;

class AcknowledgeForm extends Component
{
    public Supervision $supervision;
    public string $acknowledged_by = '';
    public string $comment = '';

    protected $rules = [
        'acknowledged_by' => 'required|string|max:255',
        'comment' => 'nullable|string',
    ];

    protected $messages = [
        'acknowledged_by.required' => 'กรุณาระบุชื่อผู้รับทราบ',
    ];

    public function mount(Supervision $supervision)
    {
        if ($supervision->status !== SupervisionStatus::PUBLISHED) {
            abort(403, 'การนิเทศยังไม่ถูกเผยแพร่');
        }

        if ($supervision->acknowledgement) {
            return redirect()->route('supervisions.show', $supervision)
                ->with('info', 'การนิเทศนี้รับทราบแล้ว');
        }

        $this->supervision = $supervision->load(['school', 'user']);
    }

    public function submit()
    {
        $this->validate();

        Acknowledgement::create([
            'supervision_id' => $this->supervision->id,
            'acknowledged_by' => $this->acknowledged_by,
            'acknowledged_at' => now(),
            'comment' => $this->comment ?: null,
        ]);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($this->supervision)
            ->log('โรงเรียนรับทราบผลการนิเทศ');

        return redirect()->route('supervisions.show', $this->supervision)
            ->with('success', 'รับทราบผลการนิเทศเรียบร้อยแล้ว');
    }

    public function render()
    {
        return view('livewire.supervision.acknowledge-form')
            ->layout('layouts.app', ['title' => 'รับทราบผลการนิเทศ', 'header' => 'รับทราบผลการนิเทศ']);
    }
}
