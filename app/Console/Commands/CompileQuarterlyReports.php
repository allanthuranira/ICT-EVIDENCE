<?php

namespace App\Console\Commands;

use App\Services\ReminderService;
use App\Services\ReportCompilerService;
use Illuminate\Console\Command;

class CompileQuarterlyReports extends Command
{
    protected $signature = 'reports:compile {quarter?} {fy?}';
    protected $description = 'Compile all campus + HQ quarterly reports into one Word document and email for final compilation';

    public function handle(ReportCompilerService $compiler, ReminderService $reminders): int
    {
        $quarter = (int) ($this->argument('quarter') ?: $reminders->currentQuarter()[0]);
        $fy = (int) ($this->argument('fy') ?: $reminders->currentQuarter()[1]);

        $path = $compiler->compileAndSend($quarter, $fy);
        $this->info("Compiled report saved and emailed: {$path}");
        return self::SUCCESS;
    }
}
