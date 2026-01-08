<?php

namespace App\Console\Commands;

use App\Jobs\ProcessDailyInvestmentROI;
use Illuminate\Console\Command;

class RunDailyInvestmentROI extends Command
{
    protected $signature = 'roi:daily';
    protected $description = 'Dispatch daily investment ROI job';

    public function handle()
    {
        ProcessDailyInvestmentROI::dispatch();

        $this->info('Daily ROI job dispatched successfully.');
    }
}
