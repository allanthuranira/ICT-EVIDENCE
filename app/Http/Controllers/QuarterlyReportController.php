<?php

namespace App\Http\Controllers;

use App\Models\QuarterlyReport;
use Illuminate\Http\Request;

class QuarterlyReportController extends Controller
{
    public function index(Request $request)
    {
        $reports = QuarterlyReport::with('item', 'campus')
            ->when($request->quarter, fn($q) => $q->where('quarter', $request->quarter))
            ->orderByDesc('submitted_at')->paginate(50);
        return view('reports.index', compact('reports'));
    }
}
