<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewBloodBankNotification extends Notification
{
    use Queueable;
    protected $bloodBankId;
    protected $bloodBankUser;
    /**
     * Create a new notification instance.
     */
    public function __construct($bloodBankUser, $bloodBankId)
    {
        $this->bloodBankUser = $bloodBankUser;
        $this->bloodBankId = $bloodBankId;
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
            'message' => $this->bloodBankUser . ' ' . localize('global.created_new_blood_request'),
            'blood_bank_id' => $this->bloodBankId
        ];
    }
}
