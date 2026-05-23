<?php

namespace App\Mail;

use App\Models\ServiceReport;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ServiceReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ServiceReport $report,
        public string $pdfContent,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Reporte {$this->report->report_number} — Ascensores Tzion",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.service-report',
        );
    }

    public function attachments(): array
    {
        return [
            \Illuminate\Mail\Mailables\Attachment::fromData(
                fn() => $this->pdfContent,
                "{$this->report->report_number}.pdf"
            )->withMime('application/pdf'),
        ];
    }
}
