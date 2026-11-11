<?php

namespace App\Livewire\Supervision;

use App\Enums\SupervisionStatus;
use App\Models\Supervision;
use App\Services\SupervisionService;
use Livewire\Attributes\On;
use Livewire\Component;

class SupervisionShow extends Component
{
    public Supervision $supervision;

    public string $rejectReason = '';

    public function mount(Supervision $supervision)
    {
        $user = auth()->user();
        if (! $user || ! $user->canAccessSupervision($supervision)) {
            abort(403, 'คุณไม่มีสิทธิ์เข้าถึงการนิเทศนี้');
        }

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
        $service = app(SupervisionService::class);

        if (! $this->canSubmit()) {
            $this->dispatch('swal:error', ['title' => 'ไม่สามารถส่งการนิเทศนี้ได้']);

            return;
        }

        $errors = $service->validateForSubmission($this->supervision);
        if ($errors !== []) {
            $this->dispatch('swal:error', ['title' => 'ข้อมูลไม่ครบ', 'text' => implode(' / ', $errors)]);

            return;
        }

        if (! $service->submit($this->supervision)) {
            $this->dispatch('swal:error', ['title' => 'ไม่สามารถส่งการนิเทศนี้ได้']);

            return;
        }
        $this->supervision->refresh();

        activity()
            ->causedBy(auth()->user())
            ->performedOn($this->supervision)
            ->log('ส่งการนิเทศเพื่ออนุมัติ');

        \App\Livewire\Dashboard\DashboardSummary::clearCache($this->supervision->academicYear);

        $this->notifyExecutivesAndAdmins();

        $this->dispatch('swal:success', ['title' => 'ส่งการนิเทศเรียบร้อยแล้ว']);
    }

    #[On('doApprove')]
    public function approve()
    {
        $service = app(SupervisionService::class);

        if (! $this->canApprove() || ! $service->approve($this->supervision)) {
            $this->dispatch('swal:error', ['title' => 'ไม่สามารถอนุมัติการนิเทศนี้ได้']);

            return;
        }
        $this->supervision->refresh();

        activity()
            ->causedBy(auth()->user())
            ->performedOn($this->supervision)
            ->log('อนุมัติการนิเทศ');

        \App\Livewire\Dashboard\DashboardSummary::clearCache($this->supervision->academicYear);

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
        $service = app(SupervisionService::class);

        if (! $this->canReject()) {
            $this->dispatch('swal:error', ['title' => 'ไม่สามารถส่งกลับการนิเทศนี้ได้']);

            return;
        }

        $this->validate(
            ['rejectReason' => ['required', 'string', 'max:500']],
            ['rejectReason.required' => 'กรุณาระบุเหตุผลการส่งกลับ']
        );

        if (! $service->reject($this->supervision, $this->rejectReason, auth()->user())) {
            $this->dispatch('swal:error', ['title' => 'ไม่สามารถส่งกลับการนิเทศนี้ได้']);

            return;
        }
        $this->supervision->refresh();

        \App\Livewire\Dashboard\DashboardSummary::clearCache($this->supervision->academicYear);

        $this->supervision->user->notify(
            new \App\Notifications\SupervisionRejectedNotification(
                $this->supervision,
                auth()->user()->name,
                $this->rejectReason
            )
        );

        $this->rejectReason = '';
        $this->dispatch('swal:success', ['title' => 'ส่งกลับเพื่อปรับปรุงเรียบร้อยแล้ว']);
    }

    #[On('doPublish')]
    public function publish()
    {
        $service = app(SupervisionService::class);

        if (! $this->canPublish() || ! $service->publish($this->supervision)) {
            $this->dispatch('swal:error', ['title' => 'ไม่สามารถเผยแพร่การนิเทศนี้ได้']);

            return;
        }
        $this->supervision->refresh();

        activity()
            ->causedBy(auth()->user())
            ->performedOn($this->supervision)
            ->log('เผยแพร่การนิเทศ');

        \App\Livewire\Dashboard\DashboardSummary::clearCache($this->supervision->academicYear);

        $this->notifySchool($service);

        $this->dispatch('swal:success', ['title' => 'เผยแพร่การนิเทศเรียบร้อยแล้ว']);
    }

    public function getRejectionReasonProperty(): ?string
    {
        if ($this->supervision->status !== SupervisionStatus::NEEDS_IMPROVEMENT) {
            return null;
        }

        return app(SupervisionService::class)->latestRejectionReason($this->supervision);
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

        return $this->supervision->canPublish()
               && ($user->isAdmin() || ($user->isSupervisor() && $this->supervision->userId === $user->id));
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
    protected function notifySchool(SupervisionService $service): void
    {
        foreach ($service->schoolRecipients($this->supervision) as $user) {
            $user->notify(
                new \App\Notifications\SupervisionPublishedNotification($this->supervision)
            );
        }

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
