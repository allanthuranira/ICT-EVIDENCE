<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CompiledReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $docPath,
        public int $quarter,
        public int $fy,
    ) {}

    public function build(): static
    {
        $name = "PC_Workplan_Q{$this->quarter}_FY{$this->fy}-{$this->fy + 1}_Compiled.docx";

        return $this->subject("Compiled PC & Workplan Q{$this->quarter} Report - Campuses + ICT HQ (for final compilation)")
            ->view('emails.compiled-report')
            ->attach($this->docPath, ['as' => $name])
            ->with(['disclaimer' => 'This is a system generated email. Please do not reply.']);
    }
}
