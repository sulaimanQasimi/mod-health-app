<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\NewHospitalizationNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendNewHospitalizationNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $hospitalizationId;
    protected $userId;
    /**
     * Create a new job instance.
     */
    public function __construct($userId, $hospitalizationId)
    {
        $this->userId = $userId;
        $this->hospitalizationId = $hospitalizationId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $users = User::role('hospitalization_visits')->get();
            foreach ($users as $user) {
                $user->notify(new NewHospitalizationNotification($this->userId, $this->hospitalizationId));
            }
    }
}
