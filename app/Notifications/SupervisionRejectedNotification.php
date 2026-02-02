<?php

namespace App\Notifications;

use App\Models\Supervision;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SupervisionRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Supervision $supervision,
        public string $rejectorName,
        public ?string $reason = null
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
        $url = route('supervisions.edit', $this->supervision->id);

        $mail = (new MailMessage())
            ->subject('การนิเทศถูกส่งกลับเพื่อแก้ไข - ' . $this->supervision->school->name)
            ->greeting('สวัสดีครับ/ค่ะ คุณ' . $notifiable->name)
            ->line('การนิเทศของคุณถูกส่งกลับเพื่อปรับปรุง')
            ->line('**โรงเรียน:** ' . $this->supervision->school->name)
            ->line('**ประเภทการนิเทศ:** ' . $this->supervision->type)
            ->line('**วันที่นิเทศ:** ' . $this->supervision->date->format('d/m/Y'))
            ->line('**ผู้พิจารณา:** ' . $this->rejectorName);

        if ($this->reason) {
            $mail->line('**เหตุผล:** ' . $this->reason);
        }

        $mail->action('แก้ไขการนิเทศ', $url)
            ->line('กรุณาตรวจสอบและแก้ไขการนิเทศของคุณ แล้วส่งใหม่อีกครั้ง')
            ->salutation('ขอบคุณครับ/ค่ะ' . "\n" . config('app.name'));

        return $mail;
    }

    /**
     * Get the array representation of the notification (for database).
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'supervision_rejected',
            'supervision_id' => $this->supervision->id,
            'school_name' => $this->supervision->school->name,
            'supervision_type' => $this->supervision->type,
            'supervision_date' => $this->supervision->date->format('Y-m-d'),
            'rejector_name' => $this->rejectorName,
            'reason' => $this->reason,
            'url' => route('supervisions.edit', $this->supervision->id),
            'message' => 'การนิเทศถูกส่งกลับเพื่อแก้ไข: ' . $this->supervision->school->name,
        ];
    }
}
