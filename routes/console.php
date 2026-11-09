<?php

use Illuminate\Support\Facades\Schedule;

// Runs every minute; the ReminderService decides if any escalation email is due.
Schedule::command('reminders:send')->everyMinute()->withoutOverlapping();

// Compile all campus + HQ quarterly reports into one Word doc and email it
// to the final-compilation reviewer 1 day after each quarter ends.
Schedule::command('reports:compile')->yearlyOn(10, 1, '06:00');   // after Q1 (Sep 30)
Schedule::command('reports:compile')->yearlyOn(1, 2, '06:00');    // after Q2 (Dec 31)
Schedule::command('reports:compile')->yearlyOn(4, 1, '06:00');    // after Q3 (Mar 31)
Schedule::command('reports:compile')->yearlyOn(7, 1, '06:00');      // after Q4 (Jun 30)
