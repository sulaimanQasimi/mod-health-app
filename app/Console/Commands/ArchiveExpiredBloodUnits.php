<?php

namespace App\Console\Commands;

use App\Services\BloodBankStockService;
use Illuminate\Console\Command;

class ArchiveExpiredBloodUnits extends Command
{
    protected $signature = 'blood-bank:archive-expired {--branch= : Restrict to a single branch id}';

    protected $description = 'Archive expired blood units and remove them from available stock';

    public function handle(BloodBankStockService $stockService): int
    {
        $branchOption = $this->option('branch');
        $branchId = is_numeric($branchOption) ? (int) $branchOption : null;

        $count = $stockService->archiveExpiredUnits($branchId, null);

        $this->info("Archived expired blood units: {$count}");

        return self::SUCCESS;
    }
}
