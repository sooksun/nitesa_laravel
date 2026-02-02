<?php

namespace App\Notifications;

use App\Models\Supervision;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SupervisionPublishedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Supervision $supervision
    ) {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['mail', 'database'];

        // เพิ่ม SMS channel ถ้ามีเบอร์โทรศัพท์
        // if ($notifiable->phone) {
        //     $channels[] = 'nexmo';
        // }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = route('supervisions.show', $this->supervision->id);
        $acknowledgeUrl = route('supervisions.acknowledge', $this->supervision->id);

        return (new MailMessage())
            ->subject('มีผลการนิเทศใหม่ของโรงเรียน - ' . $this->supervision->school->name)
            ->greeting('สวัสดีครับ/ค่ะ คุณ' . $notifiable->name)
            ->line('มีผลการนิเทศใหม่ที่เผยแพร่สำหรับโรงเรียนของคุณ')
            ->line('**โรงเรียน:** ' . $this->supervision->school->name)
            ->line('**ประเภทการนิเทศ:** ' . $this->supervision->type)
            ->line('**วันที่นิเทศ:** ' . $this->supervision->date->format('d/m/Y'))
            ->line('**ผู้นิเทศ:** ' . $this->supervision->user->name)
            ->action('ดูผลการนิเทศ', $url)
            ->line('กรุณายืนยันการรับทราบผลการนิเทศภายใน 7 วัน')
            ->action('ยืนยันรับทราบ', $acknowledgeUrl)
            ->salutation('ขอบคุณครับ/ค่ะ' . "\n" . config('app.name'));
    }

    /**
     * Get the array representation of the notification (for database).
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'supervision_published',
            'supervision_id' => $this->supervision->id,
            'school_name' => $this->supervision->school->name,
            'supervision_type' => $this->supervision->type,
            'supervision_date' => $this->supervision->date->format('Y-m-d'),
            'supervisor_name' => $this->supervision->user->name,
            'url' => route('supervisions.show', $this->supervision->id),
            'acknowledge_url' => route('supervisions.acknowledge', $this->supervision->id),
            'message' => 'มีผลการนิเทศใหม่: ' . $this->supervision->school->name,
        ];
    }
}
