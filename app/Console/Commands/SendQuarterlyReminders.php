<?php

namespace App\Console\Commands;

use App\Services\ReminderService;
use Illuminate\Console\Command;

class SendQuarterlyReminders extends Command
{
    protected $signature = 'reminders:send';
    protected $description = 'Send due evidence & quarterly report reminder emails (escalating schedule)';

    public function handle(ReminderService $reminders): int
    {
        $results = $reminders->sendDueReminders();
        $this->info("Evidence reminders: {$results['evidence']}, Report reminders: {$results['report']}");
        return self::SUCCESS;
    }
}
