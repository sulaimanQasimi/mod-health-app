<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\NewPACUNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendNewPACUNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $pacuId;
    protected $userId;
    /**
     * Create a new job instance.
     */
    public function __construct($userId, $pacuId)
    {
        $this->userId = $userId;
        $this->pacuId = $pacuId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $users = User::role('nurse')->get();
            foreach ($users as $user) {
                $user->notify(new NewPACUNotification($this->userId, $this->pacuId));
            }
    }
}
