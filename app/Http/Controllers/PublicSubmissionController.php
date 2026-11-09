<?php

namespace App\Http\Controllers;

use App\Models\Campus;
use App\Models\EvidenceSubmission;
use App\Models\PerformanceItem;
use App\Models\QuarterlyReport;
use App\Models\SubmissionToken;
use App\Services\GoogleDriveService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PublicSubmissionController extends Controller
{
    // Public, email-token-protected form (no login required)
    public function form(string $token)
    {
        $data = SubmissionToken::decode($token);
        abort_if(!$data, 403, 'This link is invalid or has expired.');

        $item = PerformanceItem::findOrFail($data['i']);
        $campus = Campus::findOrFail($data['c']);

        $already = $data['k'] === 'evidence'
            ? EvidenceSubmission::existsFor($data['i'], $data['c'], $data['q'], $data['fy'])
            : QuarterlyReport::existsFor($data['i'], $data['c'], $data['q'], $data['fy']);

        return view('submit.form', ['token' => $token, 'item' => $item, 'campus' => $campus,
            'quarter' => $data['q'], 'fy' => $data['fy'], 'kind' => $data['k'], 'already' => $already]);
    }

    public function store(Request $request, string $token, GoogleDriveService $drive)
    {
        $data = SubmissionToken::decode($token);
        abort_if(!$data, 403, 'This link is invalid or has expired.');
        [$itemId, $campusId, $quarter, $fy, $kind] = [$data['i'], $data['c'], $data['q'], $data['fy'], $data['k']];

        $common = $request->validate([
            'submitted_by_email' => 'required|email',
            'notes' => 'nullable|string|max:5000',
        ]);

        if ($kind === 'evidence') {
            $request->validate(['files' => 'required|array|min:1', 'files.*' => 'file|max:51200']); // 50MB each

            foreach ($request->file('files') as $file) {
                $diskPath = $file->store("evidence/Q{$quarter}/{$fy}");
                $sub = EvidenceSubmission::create([
                    'performance_item_id' => $itemId, 'campus_id' => $campusId,
                    'quarter' => $quarter, 'fy_start_year' => $fy,
                    'original_filename' => $file->getClientOriginalName(),
                    'stored_filename' => basename($diskPath),
                    'disk_path' => $diskPath,
                    'notes' => $common['notes'],
                    'submitted_by_email' => $common['submitted_by_email'],
                    'submitted_at' => now(),
                ]);

                // Push to Google Drive: folder "{code} - {name}", file "B1.1_Baringo_Q1_xxx.pdf"
                if ($driveResult = $drive->uploadEvidence($sub)) {
                    $sub->update(['drive_file_id' => $driveResult['id'], 'drive_link' => $driveResult['link']]);
                }
            }
            $message = 'Evidence submitted successfully. Reminder emails for this item have stopped.';
        } else {
            $r = $request->validate([
                'quarter_target' => 'nullable|numeric',
                'quarter_actual' => 'nullable|numeric',
                'quarter_variance' => 'nullable|numeric',
                'quarter_remarks' => 'nullable|string|max:10000',
                'cumulative_target' => 'nullable|numeric',
                'cumulative_actual' => 'nullable|numeric',
                'cumulative_variance' => 'nullable|numeric',
                'cumulative_remarks' => 'nullable|string|max:10000',
                'status' => 'required|in:done,initiated,evidence_available,not_done',
            ]);
            QuarterlyReport::updateOrCreate(
                ['performance_item_id' => $itemId, 'campus_id' => $campusId,
                 'quarter' => $quarter, 'fy_start_year' => $fy],
                $r + ['submitted_by_email' => $common['submitted_by_email'], 'submitted_at' => now()]
            );
            $message = 'Quarterly status report submitted successfully. Reminder emails for this item have stopped.';
        }

        return view('submit.thankyou', ['message' => $message]);
    }
}
