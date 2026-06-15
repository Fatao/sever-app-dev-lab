<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AnalyticsReportMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param string $reportPath Absolute path to the report file.
     * @param string $period     Human-readable period description.
     */
    public function __construct(
        public readonly string $reportPath,
        public readonly string $period,
    ) {}

    /**
     * Build the email envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Analytics Report — ' . $this->period,
        );
    }

    /**
     * Build the email content.
     */
    public function content(): Content
    {
        return new Content(
            htmlString: '<p>Attached is the analytics report for the period: ' . $this->period . '.</p>',
        );
    }

    /**
     * Attach the report file to the email.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [
            Attachment::fromPath($this->reportPath)
                ->as('analytics_report.json')
                ->withMime('application/json'),
        ];
    }
}
