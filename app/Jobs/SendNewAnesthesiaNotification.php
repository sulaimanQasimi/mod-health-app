<?php

namespace App\Jobs;

use App\Models\Anesthesia;
use App\Models\User;
use App\Notifications\NewAnesthesiaNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendNewAnesthesiaNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $anesthesiaId;
    protected $userId;
    /**
     * Create a new job instance.
     */
    public function __construct($userId, $anesthesiaId)
    {
        $this->userId = $userId;
        $this->anesthesiaId = $anesthesiaId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        $users = User::role('anesthesia_approve')->get();

        foreach ($users as $user) {
            $user->notify(new NewAnesthesiaNotification($this->userId, $this->anesthesiaId));
        }
    }
}
