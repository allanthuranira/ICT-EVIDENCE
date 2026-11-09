<?php

namespace App\Http\Controllers;

use App\Models\EvidenceSubmission;
use Illuminate\Http\Request;

class EvidenceController extends Controller
{
    public function index(Request $request)
    {
        $submissions = EvidenceSubmission::with('item', 'campus')
            ->when($request->quarter, fn($q) => $q->where('quarter', $request->quarter))
            ->when($request->campus_id, fn($q) => $q->where('campus_id', $request->campus_id))
            ->orderByDesc('submitted_at')->paginate(50);
        return view('evidence.index', compact('submissions'));
    }

    public function show(EvidenceSubmission $submission)
    {
        return response()->download(storage_path('app/' . $submission->disk_path), $submission->original_filename);
    }
}
