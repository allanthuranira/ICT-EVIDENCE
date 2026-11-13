<?php

namespace App\Services;

use App\Models\Campus;
use App\Models\PerformanceItem;
use App\Models\QuarterlyReport;
use Illuminate\Support\Carbon;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\SimpleType\TblWidth;

/*
 * Generates the official KSG quarterly reporting Word documents:
 *   - Performance Contract (PC) implementation status report
 *   - Annual Work Plan implementation status report
 * in the exact format of the departmental templates, as separate .docx files.
 *
 * Each document can be generated for ONE campus/HQ (one row per item) or for
 * ALL campuses + ICT HQ combined (one row per item x campus, with a Campus column)
 * which is the version compiled for the final reviewer / M&E.
 *
 * PC rows are grouped under the item's "section" (e.g. "A. Financial Stewardship ...").
 * Workplan rows are grouped under the item's strategic_issue / strategic_goal /
 * kra_group / outcomes / strategic_objective heading rows (all nullable - skipped
 * when empty, exactly like in the real workplans).
 */
class QuarterlyReportWordService
{
    private const STATUS_LABELS = [
        'done' => 'Done',
        'initiated' => 'Initiated',
        'evidence_available' => 'Evidence Available',
        'not_done' => 'Not Done',
        'not_due' => 'Not Due',
    ];

    /** Returns the .docx path of the generated PC report. */
    public function exportPc(?int $campusId, int $quarter, int $fy): string
    {
        [$start, $end] = $this->quarterDates($quarter, $fy);
        $combined = $campusId === null;

        $word = new PhpWord();
        $word->setDefaultFontName('Times New Roman');
        $word->setDefaultFontSize(10);
        $section = $word->addSection(['orientation' => 'landscape']);

        // ---- Header block (template cover text) ----
        $this->center($section, 'KENYA SCHOOL OF GOVERNMENT', ['bold' => true, 'size' => 14]);
        $this->center($section, 'PERFORMANCE CONTRACT MONITORING AND EVALUATION FRAMEWORK', ['bold' => true, 'size' => 12]);
        $this->center($section, 'ICT DEPARTMENT', ['bold' => true, 'size' => 12]);
        $this->center($section, "PERFORMANCE CONTRACT FY {$fy}/" . ($fy + 1), ['bold' => true, 'size' => 12]);
        $this->center($section, 'IMPLEMENTATION STATUS REPORT FOR QUARTER ' . $this->roman($quarter) . ',', ['bold' => true, 'size' => 12]);
        $this->center($section, $start->format('jS F') . ' TO ' . $end->format('jS F Y'), ['bold' => true, 'size' => 12]);
        $this->center($section, 'DATE: ' . $end->format('F j, Y'), ['bold' => true, 'size' => 12]);
        $section->addTextBreak(1);

        $section->addText("PC FY {$fy}/" . ($fy + 1) . ' QUARTER ' . $quarter . ' STATUS REPORT AS AT ' . $end->format('F j, Y'), ['bold' => true, 'size' => 11]);

        // ---- Table ----
        $w = $combined
            ? ['campus' => 1300, 'indicator' => 3400, 'uom' => 1300, 'fy' => 1100,
               'n' => 1100, 'remarks' => 3200, 'cremarks' => 2800]
            : ['indicator' => 4200, 'uom' => 1500, 'fy' => 1300, 'n' => 1200, 'remarks' => 4800, 'cremarks' => 3400];

        $table = $section->addTable(['borderSize' => 6, 'unit' => TblWidth::PERCENT, 'width' => 100]);

        // Header row 1
        $table->addRow();
        if ($combined) {
            $table->addCell($w['campus'], ['vMerge' => 'restart'])->addText('Campus/HQ', ['bold' => true]);
        }
        $table->addCell($w['indicator'], ['vMerge' => 'restart'])->addText('Indicator', ['bold' => true]);
        $table->addCell($w['uom'], ['vMerge' => 'restart'])->addText('Unit of measure', ['bold' => true]);
        $table->addCell($w['fy'], ['vMerge' => 'restart'])->addText('FY Target (M)', ['bold' => true]);
        $table->addCell($w['n'] * 3 + $w['remarks'], ['gridSpan' => 4])->addText("Quarter {$quarter} Achievement", ['bold' => true], ['alignment' => Jc::CENTER]);
        $table->addCell($w['n'] * 3 + $w['cremarks'], ['gridSpan' => 4])->addText('Cumulative Achievement', ['bold' => true], ['alignment' => Jc::CENTER]);

        // Header row 2 (vMerge continuation for the rowspan cells)
        $table->addRow();
        if ($combined) {
            $table->addCell($w['campus'], ['vMerge' => 'continue']);
        }
        $table->addCell($w['indicator'], ['vMerge' => 'continue']);
        $table->addCell($w['uom'], ['vMerge' => 'continue']);
        $table->addCell($w['fy'], ['vMerge' => 'continue']);
        foreach (['Target for Quarter', 'Actual', 'Variance', 'Remarks/Explanation and Mitigation Strategies',
                  'Cumulative Target', 'Cumulative Actual', 'Cumulative Variance', 'Remarks/Explanation'] as $h) {
            $width = str_starts_with($h, 'Remarks') ? ($h === 'Remarks/Explanation and Mitigation Strategies' ? $w['remarks'] : $w['cremarks']) : $w['n'];
            $table->addCell($width)->addText($h, ['bold' => true]);
        }

        // ---- Rows grouped by PC section ----
        $items = PerformanceItem::pc()->active()->forFy($fy)->with('campuses')->orderBy('code')->get()
            ->filter(fn($i) => $i->appliesToQuarter($quarter));

        $campuses = $this->targetCampuses($campusId);
        $spanWidth = array_sum($w) + ($combined ? 0 : 0);

        $currentSection = null;
        foreach ($items as $item) {
            if ($item->section !== $currentSection) {
                $currentSection = $item->section;
                if ($currentSection) {
                    $table->addRow();
                    $table->addCell($this->totalWidth($w, $combined), ['gridSpan' => $this->colCount($combined)])
                        ->addText(strtoupper($currentSection), ['bold' => true, 'size' => 10]);
                }
            }

            foreach ($this->campusRows($item, $campuses, $combined) as $campus) {
                $report = $campus ? $this->reportFor($item, $campus, $quarter, $fy) : null;

                $table->addRow();
                if ($combined) {
                    $table->addCell($w['campus'])->addText($campus->name);
                }
                $table->addCell($w['indicator'])->addText("{$item->code} - {$item->name}");
                $table->addCell($w['uom'])->addText($item->unit_of_measure ?? '-');
                $table->addCell($w['fy'])->addText($item->annual_target !== null ? (string) $item->annual_target : '-');
                $table->addCell($w['n'])->addText($this->num($report?->quarter_target));
                $table->addCell($w['n'])->addText($this->num($report?->quarter_actual));
                $table->addCell($w['n'])->addText($this->num($report?->quarter_variance));
                $table->addCell($w['remarks'])->addText($this->remarksCell($report));
                $table->addCell($w['n'])->addText($this->num($report?->cumulative_target));
                $table->addCell($w['n'])->addText($this->num($report?->cumulative_actual));
                $table->addCell($w['n'])->addText($this->num($report?->cumulative_variance));
                $table->addCell($w['cremarks'])->addText($report?->cumulative_remarks ?: '-');
            }
        }

        return $this->save($word, "PC_Q{$quarter}_FY{$fy}" . ($combined ? '_AllCampuses' : '_' . $this->campusLabel($campusId)) . '.docx');
    }

    /** Returns the .docx path of the generated Annual Work Plan report. */
    public function exportWorkplan(?int $campusId, int $quarter, int $fy): string
    {
        [$start, $end] = $this->quarterDates($quarter, $fy);
        $combined = $campusId === null;

        $word = new PhpWord();
        $word->setDefaultFontName('Times New Roman');
        $word->setDefaultFontSize(10);
        $section = $word->addSection(['orientation' => 'landscape']);

        // ---- Header block ----
        $this->center($section, 'KENYA SCHOOL OF GOVERNMENT', ['bold' => true, 'size' => 14]);
        $this->center($section, 'ANNUAL WORK PLAN MONITORING AND EVALUATION FRAMEWORK', ['bold' => true, 'size' => 12]);
        $this->center($section, 'ICT DEPARTMENT', ['bold' => true, 'size' => 12]);
        $this->center($section, "ANNUAL WORK PLAN FY {$fy}/" . ($fy + 1), ['bold' => true, 'size' => 12]);
        $this->center($section, 'IMPLEMENTATION STATUS REPORT', ['bold' => true, 'size' => 12]);
        $this->center($section, 'QUARTER ' . $quarter . ', ' . $start->format('jS F') . ' TO ' . $end->format('jS F Y'), ['bold' => true, 'size' => 12]);
        $this->center($section, $end->format('F j, Y'), ['bold' => true, 'size' => 12]);
        $section->addTextBreak(1);

        $section->addText("Annual Work Plan FY {$fy}/" . ($fy + 1) . ' Status Report as at ' . $end->format('F j, Y'), ['bold' => true, 'size' => 11]);

        // ---- Table ----
        $w = $combined
            ? ['campus' => 1300, 'strategies' => 2200, 'activities' => 3000, 'output' => 2600, 'indicator' => 1700,
               'n' => 1100, 'remarks' => 3000, 'cremarks' => 2600]
            : ['strategies' => 2400, 'activities' => 3600, 'output' => 3000, 'indicator' => 1900,
               'n' => 1100, 'remarks' => 3400, 'cremarks' => 3000];

        $table = $section->addTable(['borderSize' => 6, 'unit' => TblWidth::PERCENT, 'width' => 100]);

        // Header row 1
        $table->addRow();
        if ($combined) {
            $table->addCell($w['campus'], ['vMerge' => 'restart'])->addText('Campus/HQ', ['bold' => true]);
        }
        $table->addCell($w['strategies'], ['vMerge' => 'restart'])->addText('Strategies', ['bold' => true]);
        $table->addCell($w['activities'], ['vMerge' => 'restart'])->addText('Key Activities', ['bold' => true]);
        $table->addCell($w['output'], ['vMerge' => 'restart'])->addText('Expected Output', ['bold' => true]);
        $table->addCell($w['indicator'], ['vMerge' => 'restart'])->addText('Output Indicator', ['bold' => true]);
        $table->addCell($w['n'] * 3 + $w['remarks'], ['gridSpan' => 4])->addText("Quarter {$quarter} Achievement", ['bold' => true], ['alignment' => Jc::CENTER]);
        $table->addCell($w['n'] * 3 + $w['cremarks'], ['gridSpan' => 4])->addText('Cumulative Achievement', ['bold' => true], ['alignment' => Jc::CENTER]);

        // Header row 2
        $table->addRow();
        if ($combined) {
            $table->addCell($w['campus'], ['vMerge' => 'continue']);
        }
        $table->addCell($w['strategies'], ['vMerge' => 'continue']);
        $table->addCell($w['activities'], ['vMerge' => 'continue']);
        $table->addCell($w['output'], ['vMerge' => 'continue']);
        $table->addCell($w['indicator'], ['vMerge' => 'continue']);
        foreach (['Target for Quarter', 'Actual', 'Variance', 'Remarks/Explanation',
                  'Cumulative Target', 'Cumulative Actual', 'Cumulative Variance', 'Cumulative Remarks/Explanation'] as $h) {
            $width = str_starts_with($h, 'Remarks') || str_contains($h, 'Remarks')
                ? ($h === 'Remarks/Explanation' ? $w['remarks'] : $w['cremarks'])
                : $w['n'];
            $table->addCell($width)->addText($h, ['bold' => true]);
        }

        // ---- Rows grouped by the workplan strategic hierarchy ----
        $items = PerformanceItem::workplan()->active()->forFy($fy)->with('campuses')->orderBy('code')->get()
            ->filter(fn($i) => $i->appliesToQuarter($quarter));

        $campuses = $this->targetCampuses($campusId);

        // Group items by their strategic hierarchy tuple, preserving first-seen order
        $groups = [];
        foreach ($items as $item) {
            $key = json_encode([
                $item->strategic_issue, $item->strategic_goal, $item->kra_group, $item->outcomes, $item->strategic_objective,
            ]);
            $groups[$key][] = $item;
        }

        foreach ($groups as $itemsInGroup) {
            $headings = [
                'Strategic Issue' => $itemsInGroup[0]->strategic_issue,
                'Strategic goal' => $itemsInGroup[0]->strategic_goal,
                'Key Result Area' => $itemsInGroup[0]->kra_group,
                'Outcomes' => $itemsInGroup[0]->outcomes,
                'Strategic Objective' => $itemsInGroup[0]->strategic_objective,
            ];

            foreach ($headings as $label => $value) {
                if (! $value) {
                    continue; // nullable - heading row omitted when not set
                }
                $table->addRow();
                $cell = $table->addCell(
                    $this->totalWidth($w, $combined),
                    ['gridSpan' => $this->colCount($combined)]
                );

                $run = $cell->addTextRun();
                $run->addText($label . ': ', ['bold' => true]);
                $run->addText((string) $value);
            }

            foreach ($itemsInGroup as $item) {
                foreach ($this->campusRows($item, $campuses, $combined) as $campus) {
                    $report = $this->reportFor($item, $campus, $quarter, $fy);

                    $table->addRow();
                    if ($combined) {
                        $table->addCell($w['campus'])->addText($campus->name);
                    }
                    $table->addCell($w['strategies'])->addText($item->strategic_objective ?: '-');
                    $table->addCell($w['activities'])->addText("{$item->code} - {$item->name}");
                    $table->addCell($w['output'])->addText($item->description ?: '-');
                    $table->addCell($w['indicator'])->addText($item->unit_of_measure ?? '-');
                    $table->addCell($w['n'])->addText($this->num($report?->quarter_target));
                    $table->addCell($w['n'])->addText($this->num($report?->quarter_actual));
                    $table->addCell($w['n'])->addText($this->num($report?->quarter_variance));
                    $table->addCell($w['remarks'])->addText($this->remarksCell($report));
                    $table->addCell($w['n'])->addText($this->num($report?->cumulative_target));
                    $table->addCell($w['n'])->addText($this->num($report?->cumulative_actual));
                    $table->addCell($w['n'])->addText($this->num($report?->cumulative_variance));
                    $table->addCell($w['cremarks'])->addText($report?->cumulative_remarks ?: '-');
                }
            }
        }

        return $this->save($word, "Workplan_Q{$quarter}_FY{$fy}" . ($combined ? '_AllCampuses' : '_' . $this->campusLabel($campusId)) . '.docx');
    }

    // ---------------------------------------------------------------- helpers
private function addLabelValue(Cell $cell, string $label, $value, array $labelStyle = ['bold' => true]): void
{
    $run = $cell->addTextRun();
    $run->addText($label . ': ', $labelStyle);
    $run->addText(is_scalar($value) ? (string) $value : '');
}

    private function center($section, string $text, array $style): void
    {
        $section->addText($text, $style, ['alignment' => Jc::CENTER]);
    }

    private function roman(int $quarter): string
    {
        return ['I', 'II', 'III', 'IV'][$quarter - 1] ?? (string) $quarter;
    }

    private function quarterDates(int $quarter, int $fy): array
    {
        return match ($quarter) {
            1 => [Carbon::create($fy, 7, 1), Carbon::create($fy, 9, 30)],
            2 => [Carbon::create($fy, 10, 1), Carbon::create($fy, 12, 31)],
            3 => [Carbon::create($fy + 1, 1, 1), Carbon::create($fy + 1, 3, 31)],
            default => [Carbon::create($fy + 1, 4, 1), Carbon::create($fy + 1, 6, 30)],
        };
    }

    private function targetCampuses(?int $campusId)
    {
        if ($campusId !== null) {
            return Campus::where('active', true)->where('id', $campusId)->get();
        }

        return Campus::where('active', true)->orderBy('is_hq')->orderBy('name')->get();
    }

    /*
     * Rows for an item: in combined mode, only the campuses the item is mapped to;
     * in single-campus mode, the one campus if mapped, else none (empty doc).
     */
    private function campusRows(PerformanceItem $item, $campuses, bool $combined)
    {
        return $campuses->filter(fn($c) => $item->campuses->contains('id', $c->id))->values();
    }

    private function reportFor(PerformanceItem $item, Campus $campus, int $quarter, int $fy): ?QuarterlyReport
    {
        return QuarterlyReport::where('performance_item_id', $item->id)
            ->where('campus_id', $campus->id)
            ->where('quarter', $quarter)
            ->where('fy_start_year', $fy)
            ->first();
    }

    private function totalWidth(array $w, bool $combined): int
    {
        return array_sum($w);
    }

    private function colCount(bool $combined): int
    {
        return $combined ? 13 : 12;
    }

    private function campusLabel(int $campusId): string
    {
        return str_replace(' ', '', Campus::find($campusId)?->name ?? 'Campus');
    }

    private function num($value): string
    {
        return $value !== null ? (string) $value : '-';
    }

    /** Quarter remarks cell: always shows the status line, then remarks/mitigation. */
    private function remarksCell(?QuarterlyReport $report): string
    {
        if (! $report) {
            return 'Not submitted';
        }

        $status = self::STATUS_LABELS[$report->status] ?? $report->status;
        $remarks = trim((string) $report->quarter_remarks);

        return 'Status: ' . $status . ($remarks !== '' ? "\n" . $remarks : '');
    }

    private function save(PhpWord $word, string $filename): string
    {
        $dir = storage_path('app/compiled');
        @mkdir($dir, 0755, true);
        $path = $dir . DIRECTORY_SEPARATOR . $filename;
        IOFactory::createWriter($word, 'Word2007')->save($path);

        return $path;
    }
}
