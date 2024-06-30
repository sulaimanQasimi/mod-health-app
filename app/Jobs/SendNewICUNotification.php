<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\NewICUNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendNewICUNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $icuId;
    protected $userId;
    /**
     * Create a new job instance.
     */
    public function __construct($userId, $icuId)
    {
        $this->userId = $userId;
        $this->icuId = $icuId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $users = User::role('nurse')->get();
            foreach ($users as $user) {
                $user->notify(new NewICUNotification($this->userId, $this->icuId));
            }
    }
}
