<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewLabNotification extends Notification
{
    use Queueable;
    protected $labId;
    protected $labUser;
    /**
     * Create a new notification instance.
     */
    public function __construct($labUser, $labId)
    {
        $this->labUser = $labUser;
        $this->labId = $labId;
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
            'message' => $this->labUser . ' ' . localize('global.created_new_lab'),
            'lab_id' => $this->labId
        ];
    }
}
