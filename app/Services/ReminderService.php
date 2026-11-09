<?php

namespace App\Services;

use App\Mail\EvidenceReminderMail;
use App\Mail\QuarterlyReportReminderMail;
use App\Models\Campus;
use App\Models\EvidenceSubmission;
use App\Models\PerformanceItem;
use App\Models\QuarterlyReport;
use App\Models\ReminderLog;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

class ReminderService
{
    /*
     * Escalating reminder schedule relative to quarter end date:
     *  - 5 weeks before end : 1 email
     *  - 3 weeks before     : 2x that week (Mon + Thu)
     *  - 2 weeks before     : 3x that work week (Mon + Wed + Fri)
     *  - 1 week before      : every work day (Mon-Fri)
     *  - day before end     : every 4 hours (all days incl. weekends)
     * Before any email we check whether the evidence / report row is already filled.
     */
    public function sendDueReminders(): array
    {
        [$quarter, $fy] = $this->currentQuarter();
        $end = $this->quarterEndDate($quarter, $fy);
        $now = Carbon::now();

        $results = ['evidence' => 0, 'report' => 0];

        if ($now->greaterThan($end)) {
            return $results; // quarter closed
        }

        $daysToEnd = (int) $now->diffInDays($end, false);

        // Which kind of reminder is due right now?
        $kindsDue = [];
        if ($daysToEnd === 35) {                                    // 5 weeks
            $kindsDue = ['once'];
        } elseif ($daysToEnd <= 21 && $daysToEnd >= 15) {           // 3 weeks
            $kindsDue = $now->isMonday() || $now->isThursday() ? ['twice_week'] : [];
        } elseif ($daysToEnd <= 14 && $daysToEnd >= 8) {            // 2 weeks
            $kindsDue = $now->isMonday() || $now->isWednesday() || $now->isFriday() ? ['workweek3'] : [];
        } elseif ($daysToEnd <= 7 && $daysToEnd >= 1 && $now->isWeekday()) { // 1 week
            $kindsDue = ['workweek_daily'];
        }

        $sendNow = fn(string $slot, bool $alreadyThisSlot) => count($kindsDue) && !$alreadyThisSlot;
        $due = count($kindsDue) || $daysToEnd <= 1; // last day: every 4 hours

        if (!$due) {
            return $results;
        }

        $items = PerformanceItem::active()->forFy($fy)->get();
        $campuses = Campus::where('active', true)->with('recipients')->get();

        foreach ($items as $item) {
            if (!$item->appliesToQuarter($quarter)) {
                continue;
            }
            foreach ($campuses as $campus) {
                if (!$item->campuses->contains('id', $campus->id)) {
                    continue; // item not mapped to this campus
                }
                // Per-item assignee takes priority; fall back to the campus default representative
                $rep = $item->assignedRecipientForCampus($campus->id) ?? $campus->activeRepresentative();
                if (!$rep) {
                    continue;
                }

                // ---- Evidence reminder ----
                if ($daysToEnd <= 1) {
                    $slot = '4hr_' . $now->format('H'); // one per 4h window
                    $already = ReminderLog::whereDate('sent_at', today())
                        ->where('performance_item_id', $item->id)->where('campus_id', $campus->id)
                        ->where('quarter', $quarter)->where('fy_start_year', $fy)
                        ->where('kind', 'evidence')->where('sent_at', '>=', $now->copy()->startOfDay())
                        ->where('sent_at', '<=', $now->copy()->subHours(4))
                        ->exists();
                    $shouldSend = !$already;
                } else {
                    $shouldSend = count($kindsDue) > 0
                        && !ReminderLog::alreadySentToday($item->id, $campus->id, $quarter, $fy, 'evidence');
                }

                if ($shouldSend && !EvidenceSubmission::existsFor($item->id, $campus->id, $quarter, $fy)) {
                    $this->dispatch($item, $campus, $quarter, $fy, $rep, 'evidence');
                    $results['evidence']++;
                }

                // ---- Quarterly status report reminder ----
                if ($shouldSend && !QuarterlyReport::existsFor($item->id, $campus->id, $quarter, $fy)) {
                    $this->dispatch($item, $campus, $quarter, $fy, $rep, 'report');
                    $results['report']++;
                }
            }
        }

        return $results;
    }

    private function dispatch(PerformanceItem $item, Campus $campus, int $quarter, int $fy, $rep, string $kind): void
    {
        $head = $campus->activeIctHead();
        $cc = array_filter([
            $head?->email,
            config('services.ksg.ddict_email', 'ddict@ksg.ac.ke'), // always CC DDICT
        ]);

    $mailable = $kind === 'evidence'
        ? new EvidenceReminderMail($item, $campus, $quarter, $fy, $rep)
        : new QuarterlyReportReminderMail($item, $campus, $quarter, $fy, $rep);

        Mail::to($rep->email)->cc($cc)->send($mailable);

        ReminderLog::create([
            'performance_item_id' => $item->id,
            'campus_id' => $campus->id,
            'quarter' => $quarter,
            'fy_start_year' => $fy,
            'kind' => $kind,
            'sent_to' => $rep->email,
            'cc' => implode(',', $cc),
            'sent_at' => now(),
        ]);
    }

    public function currentQuarter(): array
    {
        $now = Carbon::now();
        $m = $now->month;
        $quarter = match (true) {
            $m >= 7 && $m <= 9 => 1,
            $m >= 10 && $m <= 12 => 2,
            $m >= 1 && $m <= 3 => 3,
            default => 4,
        };
        $fy = ($m >= 7) ? $now->year : $now->year - 1; // FY starts July
        return [$quarter, $fy];
    }

    public function quarterEndDate(int $quarter, int $fy): Carbon
    {
        return match ($quarter) {
            1 => Carbon::create($fy, 9, 30, 23, 59, 59),
            2 => Carbon::create($fy, 12, 13, 23, 59, 59),// changed from 12/31 to 12/10 to allow for report submission testing
            3 => Carbon::create($fy + 1, 3, 31, 23, 59, 59),
            default => Carbon::create($fy + 1, 6, 30, 23, 59, 59),
        };
    }
}
