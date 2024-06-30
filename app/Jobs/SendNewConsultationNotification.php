<?php

namespace App\Jobs;

use App\Models\Consultation;
use App\Models\User;
use App\Notifications\NewConsultationNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendNewConsultationNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $consultationId;
    protected $userId;
    /**
     * Create a new job instance.
     */
    public function __construct($userId, $consultationId)
    {
        $this->userId = $userId;
        $this->consultationId = $consultationId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $consultation = Consultation::where('id', $this->consultationId)->first();


        $users = User::whereIn('department_id',json_decode($consultation->department_id))->get();

        foreach ($users as $user) {
            $user->notify(new NewConsultationNotification($this->userId, $this->consultationId));
        }
    }
}
