<?php

namespace App\Jobs;

use App\Models\Appointment;
use App\Models\Doctor;
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

        if ($appointment->doctor_id) {
            $doctor = Doctor::find($appointment->doctor_id);
            
            if ($doctor) {
                // Find users in the same department and branch as the doctor to notify
                $users = User::where('department_id', $doctor->department_id)
                    ->where('branch_id', $doctor->branch_id)
                    ->get();

                foreach($users as $user)
                {
                    $user->notify(new NewAppointmentNotification($this->userId, $this->appointmentId));
                }
            }
        }
    }
}
