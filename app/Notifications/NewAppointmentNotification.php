<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewAppointmentNotification extends Notification
{
    use Queueable;
    protected $appointmentId;
    protected $appointmentUser;
    /**
     * Create a new notification instance.
     */
    public function __construct($appointmentUser, $appointmentId)
    {
        $this->appointmentUser = $appointmentUser;
        $this->appointmentId = $appointmentId;
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
            'message' => $this->appointmentUser . ' ' . localize('global.created_new_appointment'),
            'appointment_id' => $this->appointmentId
        ];
    }
}
