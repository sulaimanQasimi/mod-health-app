<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewOperationNotification extends Notification
{
    use Queueable;
    protected $operationId;
    protected $operationUser;
    /**
     * Create a new notification instance.
     */
    public function __construct($operationUser, $operationId)
    {
        $this->operationUser = $operationUser;
        $this->operationId = $operationId;
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
            'message' => $this->operationUser . ' ' . localize('global.approved_new_operation'),
            'operation_id' => $this->operationId
        ];
    }
}
