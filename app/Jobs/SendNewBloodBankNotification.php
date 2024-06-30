<?php

namespace App\Jobs;

use App\Models\BloodBank;
use App\Models\User;
use App\Notifications\NewBloodBankNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendNewBloodBankNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $bloodBankId;
    protected $userId;
    /**
     * Create a new job instance.
     */
    public function __construct($userId, $bloodBankId)
    {
        $this->userId = $userId;
        $this->bloodBankId = $bloodBankId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $bloodBank = BloodBank::where('id', $this->bloodBankId)->first();


        $users = User::role('blood-bank')->get();

        foreach ($users as $user) {
            $user->notify(new NewBloodBankNotification($this->userId, $this->bloodBankId));
        }
    }
}
