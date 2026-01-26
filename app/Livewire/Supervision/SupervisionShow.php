<?php

namespace App\Livewire\Supervision;

use App\Enums\SupervisionStatus;
use App\Models\Supervision;
use Livewire\Attributes\On;
use Livewire\Component;

class SupervisionShow extends Component
{
    public Supervision $supervision;

    public function mount(Supervision $supervision)
    {
        $this->supervision = $supervision->load([
            'school.networkGroupRelation',
            'user',
            'indicators',
            'attachments',
            'acknowledgement',
            'ministerPolicyRelation',
            'obecPolicyRelation',
            'areaPolicyRelation',
        ]);
    }

    // Confirm dialogs
    public function confirmSubmit()
    {
        $this->dispatch('swal:confirm', [
            'title' => 'ส่งการนิเทศเพื่ออนุมัติ?',
            'text' => 'การนิเทศจะถูกส่งไปยังผู้มีอำนาจอนุมัติ',
            'icon' => 'question',
            'confirmButtonText' => 'ส่งเพื่ออนุมัติ',
            'confirmButtonColor' => '#eab308',
            'onConfirmed' => 'doSubmit',
        ]);
    }

    public function confirmApprove()
    {
        $this->dispatch('swal:confirm', [
            'title' => 'อนุมัติการนิเทศ?',
            'text' => 'คุณแน่ใจหรือไม่ที่จะอนุมัติการนิเทศนี้',
            'icon' => 'question',
            'confirmButtonText' => 'อนุมัติ',
            'confirmButtonColor' => '#2563eb',
            'onConfirmed' => 'doApprove',
        ]);
    }

    public function confirmReject()
    {
        $this->dispatch('swal:confirm', [
            'title' => 'ส่งกลับเพื่อปรับปรุง?',
            'text' => 'การนิเทศจะถูกส่งกลับให้ศึกษานิเทศก์แก้ไข',
            'icon' => 'warning',
            'confirmButtonText' => 'ส่งกลับ',
            'confirmButtonColor' => '#dc2626',
            'onConfirmed' => 'doReject',
        ]);
    }

    public function confirmPublish()
    {
        $this->dispatch('swal:confirm', [
            'title' => 'เผยแพร่การนิเทศ?',
            'text' => 'การนิเทศจะถูกเผยแพร่และโรงเรียนจะสามารถดูได้',
            'icon' => 'question',
            'confirmButtonText' => 'เผยแพร่',
            'confirmButtonColor' => '#16a34a',
            'onConfirmed' => 'doPublish',
        ]);
    }

    // Workflow actions
    #[On('doSubmit')]
    public function submit()
    {
        if (! $this->canSubmit()) {
            $this->dispatch('swal:error', ['title' => 'ไม่สามารถส่งการนิเทศนี้ได้']);

            return;
        }

        $this->supervision->submit();
        $this->supervision->refresh();

        activity()
            ->causedBy(auth()->user())
            ->performedOn($this->supervision)
            ->log('ส่งการนิเทศเพื่ออนุมัติ');

        // Clear dashboard cache
        \App\Livewire\Dashboard\DashboardSummary::clearCache($this->supervision->academicYear);

        // ส่ง notification ไปยังผู้บริหาร (EXECUTIVE) และ ADMIN
        $this->notifyExecutivesAndAdmins();

        $this->dispatch('swal:success', ['title' => 'ส่งการนิเทศเรียบร้อยแล้ว']);
    }

    #[On('doApprove')]
    public function approve()
    {
        if (! $this->canApprove()) {
            $this->dispatch('swal:error', ['title' => 'ไม่สามารถอนุมัติการนิเทศนี้ได้']);

            return;
        }

        $this->supervision->approve();
        $this->supervision->refresh();

        activity()
            ->causedBy(auth()->user())
            ->performedOn($this->supervision)
            ->log('อนุมัติการนิเทศ');

        // Clear dashboard cache
        \App\Livewire\Dashboard\DashboardSummary::clearCache($this->supervision->academicYear);

        // ส่ง notification ไปยังผู้นิเทศที่สร้างการนิเทศนี้
        $this->supervision->user->notify(
            new \App\Notifications\SupervisionApprovedNotification(
                $this->supervision,
                auth()->user()->name
            )
        );

        $this->dispatch('swal:success', ['title' => 'อนุมัติการนิเทศเรียบร้อยแล้ว']);
    }

    #[On('doReject')]
    public function reject()
    {
        if (! $this->canReject()) {
            $this->dispatch('swal:error', ['title' => 'ไม่สามารถปฏิเสธการนิเทศนี้ได้']);

            return;
        }

        $this->supervision->reject();
        $this->supervision->refresh();

        activity()
            ->causedBy(auth()->user())
            ->performedOn($this->supervision)
            ->log('ส่งกลับเพื่อปรับปรุง');

        // Clear dashboard cache
        \App\Livewire\Dashboard\DashboardSummary::clearCache($this->supervision->academicYear);

        // ส่ง notification ไปยังผู้นิเทศที่สร้างการนิเทศนี้
        $this->supervision->user->notify(
            new \App\Notifications\SupervisionRejectedNotification(
                $this->supervision,
                auth()->user()->name
            )
        );

        $this->dispatch('swal:success', ['title' => 'ส่งกลับเพื่อปรับปรุงเรียบร้อยแล้ว']);
    }

    #[On('doPublish')]
    public function publish()
    {
        if (! $this->canPublish()) {
            $this->dispatch('swal:error', ['title' => 'ไม่สามารถเผยแพร่การนิเทศนี้ได้']);

            return;
        }

        $this->supervision->publish();
        $this->supervision->refresh();

        activity()
            ->causedBy(auth()->user())
            ->performedOn($this->supervision)
            ->log('เผยแพร่การนิเทศ');

        // Clear dashboard cache
        \App\Livewire\Dashboard\DashboardSummary::clearCache($this->supervision->academicYear);

        // ส่ง notification ไปยังโรงเรียนที่ถูกนิเทศ
        $this->notifySchool();

        $this->dispatch('swal:success', ['title' => 'เผยแพร่การนิเทศเรียบร้อยแล้ว']);
    }

    public function canSubmit(): bool
    {
        $user = auth()->user();

        return $this->supervision->canSubmit()
               && ($user->isAdmin() || $this->supervision->userId === $user->id);
    }

    public function canApprove(): bool
    {
        $user = auth()->user();

        return $this->supervision->canApprove()
               && ($user->isAdmin() || $user->isExecutive());
    }

    public function canReject(): bool
    {
        $user = auth()->user();

        return $this->supervision->canReject()
               && ($user->isAdmin() || $user->isExecutive());
    }

    public function canPublish(): bool
    {
        $user = auth()->user();

        return $this->supervision->canPublish() && $user->isAdmin();
    }

    public function canEdit(): bool
    {
        $user = auth()->user();

        return ($this->supervision->status === SupervisionStatus::DRAFT
                || $this->supervision->status === SupervisionStatus::NEEDS_IMPROVEMENT)
               && ($user->isAdmin() || $this->supervision->userId === $user->id);
    }

    /**
     * ส่ง notification ไปยังผู้บริหารและผู้ดูแลระบบ
     */
    protected function notifyExecutivesAndAdmins(): void
    {
        $recipients = \App\Models\User::whereIn('role', ['EXECUTIVE', 'ADMIN'])
            ->where('isActive', true)
            ->get();

        foreach ($recipients as $recipient) {
            $recipient->notify(
                new \App\Notifications\SupervisionSubmittedNotification($this->supervision)
            );
        }
    }

    /**
     * ส่ง notification ไปยังโรงเรียน
     */
    protected function notifySchool(): void
    {
        // หา users ที่เป็น SCHOOL role และมีสิทธิ์เข้าถึงโรงเรียนนี้
        $schoolUsers = \App\Models\User::where('role', 'SCHOOL')
            ->where('isActive', true)
            ->get();

        // หรือถ้ามี relationship กับ school โดยตรง สามารถใช้:
        // $schoolUsers = $this->supervision->school->users()->where('role', 'SCHOOL')->get();

        foreach ($schoolUsers as $user) {
            $user->notify(
                new \App\Notifications\SupervisionPublishedNotification($this->supervision)
            );
        }

        // ส่ง notification ให้กับผู้นิเทศด้วย
        $this->supervision->user->notify(
            new \App\Notifications\SupervisionPublishedNotification($this->supervision)
        );
    }

    public function render()
    {
        return view('livewire.supervision.supervision-show')
            ->layout('layouts.app', [
                'title' => 'รายละเอียดการนิเทศ',
                'header' => 'รายละเอียดการนิเทศ',
            ]);
    }
}
