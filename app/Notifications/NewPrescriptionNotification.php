<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewPrescriptionNotification extends Notification
{
    use Queueable;
    protected $prescriptionId;
    protected $prescriptionUser;
    /**
     * Create a new notification instance.
     */
    public function __construct($prescriptionUser, $prescriptionId)
    {
        $this->prescriptionUser = $prescriptionUser;
        $this->prescriptionId = $prescriptionId;
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
            'message' => $this->prescriptionUser . ' ' . localize('global.created_new_prescription'),
            'prescription_id' => $this->prescriptionId
        ];
    }
}
