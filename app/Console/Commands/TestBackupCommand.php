<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\CreateBackupJob;

class TestBackupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:test-job';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test backup job by dispatching it to queue';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Dispatching backup job to queue...');
        
        CreateBackupJob::dispatch();
        
        $this->info('Backup job dispatched successfully!');
        $this->info('Run "php artisan queue:work" to process the job.');
    }
}
