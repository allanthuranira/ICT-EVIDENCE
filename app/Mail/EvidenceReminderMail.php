<?php

namespace App\Mail;

use App\Models\Campus;
use App\Models\PerformanceItem;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Recipient;

class EvidenceReminderMail extends Mailable
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
        $token = \App\Models\SubmissionToken::for($this->item, $this->campus, $this->quarter, $this->fy, 'evidence');

        return $this->subject("Evidence Required: {$this->item->code} - {$this->campus->name} (Q{$this->quarter})")
            ->view('emails.evidence-reminder')
            ->with([
                'recipientName' => $this->recipient?->name ?? $this->campus->activeRepresentative()?->name ?? 'Representative',
                'submitUrl' => route('submit.form', $token),
                'disclaimer' => 'This is a system generated email from the KSG PC & Workplan Evidence Collection System. '
                    . 'Please do not reply. Reminders for this item will stop automatically once the evidence '
                    . 'is submitted through the link above.',
            ]);
    }
}
