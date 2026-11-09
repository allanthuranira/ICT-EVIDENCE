<?php

namespace App\Http\Controllers;

use App\Models\Campus;
use App\Models\EvidenceSubmission;
use App\Models\PerformanceItem;
use App\Models\QuarterlyReport;
use App\Services\ReminderService;

class DashboardController extends Controller
{
    public function index(ReminderService $reminders)
    {
        [$quarter, $fy] = $reminders->currentQuarter();
        $campuses = Campus::where('active', true)->withCount([
            'items as items_q' => fn($q) => $q->where('q' . $quarter, true),
        ])->get();

        $cards = $campuses->map(function ($campus) use ($quarter, $fy) {
            $items = PerformanceItem::active()->forFy($fy)
                ->where('q' . $quarter, true)
                ->whereHas('campuses', fn($q) => $q->where('campus_id', $campus->id))
                ->get();

            $evidenceDone = EvidenceSubmission::where('campus_id', $campus->id)
                ->where('quarter', $quarter)->where('fy_start_year', $fy)->count();
            $reportsDone = QuarterlyReport::where('campus_id', $campus->id)
                ->where('quarter', $quarter)->where('fy_start_year', $fy)->count();

            $total = max($items->count(), 1);
            return [
                'campus' => $campus,
                'total' => $items->count(),
                'evidence' => $evidenceDone,
                'reports' => $reportsDone,
                'evidence_pct' => round($evidenceDone / $total * 100),
                'report_pct' => round($reportsDone / $total * 100),
                'items' => $items->map(fn($i) => [
                    'item' => $i,
                    'evidence' => EvidenceSubmission::existsFor($i->id, $campus->id, $quarter, $fy),
                    'report' => QuarterlyReport::existsFor($i->id, $campus->id, $quarter, $fy),
                ]),
            ];
        });

        return view('dashboard', compact('cards', 'quarter', 'fy'));
    }
}
