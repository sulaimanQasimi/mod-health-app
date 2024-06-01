<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\NewOperationNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendNewOperationNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $operationId;
    protected $userId;
    /**
     * Create a new job instance.
     */
    public function __construct($userId, $operationId)
    {
        $this->userId = $userId;
        $this->operationId = $operationId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $users = User::role('operations_approve')->get();
            foreach ($users as $user) {
                $user->notify(new NewOperationNotification($this->userId, $this->operationId));
            }
    }
}
