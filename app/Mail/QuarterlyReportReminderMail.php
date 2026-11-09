<?php

namespace App\Mail;

use App\Models\Campus;
use App\Models\PerformanceItem;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use App\Models\Recipient;
use Illuminate\Queue\SerializesModels;

class QuarterlyReportReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public PerformanceItem $item,
        public Campus $campus,
        public int $quarter,
        public int $fy,
        public ?Recipient $recipient = null
    ) {}

    public function build(): static
    {
        $token = \App\Models\SubmissionToken::for($this->item, $this->campus, $this->quarter, $this->fy, 'report');
        $recipientName = $this->recipient?->name ?? $this->campus->activeRepresentative()?->name ?? 'Representative';

        return $this->subject("Quarterly Status Report Required: {$this->item->code} - {$this->campus->name} (Q{$this->quarter})")
            ->view('emails.report-reminder')
            ->with([
                'recipientName' => $recipientName,
                'submitUrl' => route('submit.form', $token),
                'disclaimer' => 'This is a system generated email from the KSG PC & Workplan Evidence Collection System. '
                    . 'Please do not reply. Reminders for this item will stop automatically once the quarterly '
                    . 'status is submitted through the link above.',
            ]);
    }
}
