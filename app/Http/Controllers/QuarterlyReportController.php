<?php

namespace App\Http\Controllers;

use App\Models\Campus;
use App\Models\QuarterlyReport;
use App\Services\QuarterlyReportWordService;
use App\Services\ReminderService;
use Illuminate\Http\Request;

class QuarterlyReportController extends Controller
{
    public function index(Request $request)
    {
        [$currentQuarter, $currentFy] = (new ReminderService())->currentQuarter();

        $reports = QuarterlyReport::with('item', 'campus')
            ->when($request->quarter, fn($q) => $q->where('quarter', $request->quarter))
            ->orderByDesc('submitted_at')->paginate(50);

        $campuses = Campus::where('active', true)->orderBy('is_hq')->orderBy('name')->get();

        return view('reports.index', compact('reports', 'campuses', 'currentQuarter', 'currentFy'));
    }

    /*
     * Downloads the official quarterly report as Word, separately for PC and
     * Workplan: ?type=pc|workplan&campus=all|<campus_id>&quarter=1-4&fy=2026
     */
    public function export(Request $request, QuarterlyReportWordService $word, ReminderService $reminders)
    {
        $type = $request->get('type', 'pc');
        abort_unless(in_array($type, ['pc', 'workplan']), 404);

        $quarter = (int) $request->get('quarter', $reminders->currentQuarter()[0]);
        $fy = (int) $request->get('fy', $reminders->currentQuarter()[1]);
        $campusParam = $request->get('campus', 'all');
        $campusId = $campusParam === 'all' ? null : (int) $campusParam;

        $path = $type === 'pc'
            ? $word->exportPc($campusId, $quarter, $fy)
            : $word->exportWorkplan($campusId, $quarter, $fy);

        return response()->download($path);
    }
}
