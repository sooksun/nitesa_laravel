<?php

namespace App\Livewire\Traits;

trait WithSweetAlert
{
    public function swalSuccess(string $title, string $text = ''): void
    {
        $this->dispatch('swal:success', [
            'title' => $title,
            'text' => $text
        ]);
    }

    public function swalError(string $title, string $text = ''): void
    {
        $this->dispatch('swal:error', [
            'title' => $title,
            'text' => $text
        ]);
    }

    public function swalInfo(string $title, string $text = ''): void
    {
        $this->dispatch('swal:info', [
            'title' => $title,
            'text' => $text
        ]);
    }

    public function swalConfirm(
        string $onConfirmed,
        string $title = 'ยืนยันการดำเนินการ?',
        string $text = '',
        string $icon = 'warning',
        string $confirmButtonText = 'ยืนยัน',
        string $confirmButtonColor = '#3085d6'
    ): void {
        $this->dispatch('swal:confirm', [
            'title' => $title,
            'text' => $text,
            'icon' => $icon,
            'confirmButtonText' => $confirmButtonText,
            'confirmButtonColor' => $confirmButtonColor,
            'onConfirmed' => $onConfirmed
        ]);
    }

    public function swalConfirmDelete(string $onConfirmed, string $itemName = 'รายการนี้'): void
    {
        $this->swalConfirm(
            onConfirmed: $onConfirmed,
            title: "ลบ{$itemName}?",
            text: "การดำเนินการนี้ไม่สามารถย้อนกลับได้",
            icon: 'warning',
            confirmButtonText: 'ลบ',
            confirmButtonColor: '#dc2626'
        );
    }
}
