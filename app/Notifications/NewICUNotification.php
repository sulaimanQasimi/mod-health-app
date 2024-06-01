<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewICUNotification extends Notification
{
    use Queueable;
    protected $icuId;
    protected $icuUser;
    /**
     * Create a new notification instance.
     */
    public function __construct($icuUser, $icuId)
    {
        $this->icuUser = $icuUser;
        $this->icuId = $icuId;
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
            'message' => $this->icuUser . ' ' . localize('global.created_new_icu'),
            'icu_id' => $this->icuId
        ];
    }
}
