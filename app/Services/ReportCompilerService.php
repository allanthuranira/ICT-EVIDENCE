<?php

namespace App\Services;

use App\Mail\CompiledReportMail;
use App\Models\Campus;
use App\Models\PerformanceItem;
use App\Models\QuarterlyReport;
use Illuminate\Support\Facades\Mail;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\SimpleType\Jc;

class ReportCompilerService
{
    // Builds one Word document containing every campus + HQ filled quarterly report
    // (status: Done / Initiated / Evidence Available / Not Done), then emails it
    // to the final-compilation reviewer (mwithali.thuranira@ksg.ac.ke).
    public function compileAndSend(int $quarter, int $fy): string
    {
        $word = new PhpWord();
        $section = $word->addSection();
        $quarterEnd = (new ReminderService())->quarterEndDate($quarter, $fy)->format('F j, Y');

        $section->addText('KENYA SCHOOL OF GOVERNMENT', ['bold' => true, 'size' => 16], ['alignment' => Jc::CENTER]);
        $section->addText("ICT DEPARTMENT - PC & WORKPLAN COMPILED QUARTER {$quarter} STATUS REPORT", ['bold' => true, 'size' => 12], ['alignment' => Jc::CENTER]);
        $section->addText("FY {$fy}/" . ($fy + 1) . " - All Campuses and ICT HQ - as at {$quarterEnd}", ['size' => 10], ['alignment' => Jc::CENTER]);
        $section->addTextBreak(1);

        $items = PerformanceItem::active()->forFy($fy)->orderBy('type')->orderBy('code')->get();

        foreach ($items as $item) {
            if (!$item->appliesToQuarter($quarter)) {
                continue;
            }
            $section->addText("{$item->code}: {$item->name}" . ($item->type === 'pc' ? ' (PC)' : ' (Workplan)'),
                ['bold' => true, 'size' => 11]);
            $table = $section->addTable(['borderSize' => 6]);
            $table->addRow();
            foreach (['Campus', 'Status', 'Q Target', 'Q Actual', 'Q Variance', 'Remarks'] as $h) {
                $table->addCell(2200)->addText($h, ['bold' => true]);
            }

            $reported = 0;
            foreach (Campus::where('active', true)->orderBy('is_hq')->orderBy('name')->get() as $campus) {
                $rep = QuarterlyReport::where('performance_item_id', $item->id)
                    ->where('campus_id', $campus->id)
                    ->where('quarter', $quarter)->where('fy_start_year', $fy)->first();

                $status = match ($rep?->status) {
                    'done' => 'Done', 'initiated' => 'Initiated',
                    'evidence_available' => 'Evidence Available', default => 'Not Done',
                };
                if ($rep) $reported++;

                $table->addRow();
                $table->addCell(2200)->addText($campus->name);
                $table->addCell(2200)->addText($status);
                $table->addCell(1500)->addText((string) ($rep?->quarter_target ?? '-'));
                $table->addCell(1500)->addText((string) ($rep?->quarter_actual ?? '-'));
                $table->addCell(1500)->addText((string) ($rep?->quarter_variance ?? '-'));
                $table->addCell(4000)->addText($rep?->quarter_remarks ?? '-');
            }
            $section->addText("Submitted: {$reported} of " . Campus::where('active', true)->count() . " campuses/HQ");
            $section->addTextBreak(1);
        }

        $path = storage_path("app/compiled/PC_Workplan_Q{$quarter}_FY{$fy}_Compiled.docx");
        @mkdir(dirname($path), 0755, true);
        IOFactory::createWriter($word, 'Word2007')->save($path);

        Mail::to(config('services.ksg.compile_review_email', 'mwithali.thuranira@ksg.ac.ke'))
            ->send(new CompiledReportMail($path, $quarter, $fy));

        return $path;
    }
}
