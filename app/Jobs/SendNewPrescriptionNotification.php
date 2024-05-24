<?php

namespace App\Jobs;

use App\Models\Prescription;
use App\Models\User;
use App\Notifications\NewPrescriptionNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendNewPrescriptionNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $prescriptionId;
    protected $userId;
    /**
     * Create a new job instance.
     */
    public function __construct($userId, $prescriptionId)
    {
        $this->userId = $userId;
        $this->prescriptionId = $prescriptionId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $users = User::role('prescription_issue')->get();
            foreach ($users as $user) {
                $user->notify(new NewPrescriptionNotification($this->userId, $this->prescriptionId));
            }
    }
}
