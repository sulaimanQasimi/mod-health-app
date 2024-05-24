<?php

namespace App\Jobs;

use App\Models\Appointment;
use App\Models\User;
use App\Notifications\NewAppointmentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendNewAppointmentNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $appointmentId;
    protected $userId;
    /**
     * Create a new job instance.
     */
    public function __construct($userId, $appointmentId)
    {
        $this->userId = $userId;
        $this->appointmentId = $appointmentId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $appointment = Appointment::where('id', $this->appointmentId)->first();

        $user = User::where('id',$appointment->doctor_id)->get();

        foreach($user as $user)
            {
                $user->notify(new NewAppointmentNotification($this->userId, $this->appointmentId));
            }
    }
}
