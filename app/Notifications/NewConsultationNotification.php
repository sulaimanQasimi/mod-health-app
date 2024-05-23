<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewConsultationNotification extends Notification
{
    use Queueable;
    protected $consultationId;
    protected $consultationUser;
    /**
     * Create a new notification instance.
     */
    public function __construct($consultationUser, $consultationId)
    {
        $this->consultationUser = $consultationUser;
        $this->consultationId = $consultationId;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'message' => $this->consultationUser . ' ' . localize('global.created_new_consultation'),
            'consultation_id' => $this->consultationId
        ];
    }
}
