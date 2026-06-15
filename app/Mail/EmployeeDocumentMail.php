<?php

namespace App\Mail;

use App\Models\Employee;
use App\Models\EmailTemplate;
use App\Models\UserAttachment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class EmployeeDocumentMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $mailSubject;
    public string $body;
    public UserAttachment $attachment;

    public function __construct(
        public Employee $employee,
        public string $templateType,
    ) {
        $template = EmailTemplate::forType($templateType);

        $this->mailSubject = $this->replacePlaceholders($template->subject);
        $this->body        = $this->replacePlaceholders($template->body);
        $this->attachment  = $employee->user->attachments()->where('key', $templateType)->firstOrFail();
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->mailSubject);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.employee-document', with: [
            'body'         => $this->body,
            'employeeName' => $this->employee->user->name,
        ]);
    }

    public function attachments(): array
    {
        $fullPath = Storage::disk('public')->path($this->attachment->attachment_path);

        return [
            Attachment::fromPath($fullPath)
                ->as(basename($this->attachment->attachment_path))
                ->withMime('application/pdf'),
        ];
    }

    private function replacePlaceholders(string $text): string
    {
        $employee = $this->employee;

        return str_replace(
            ['{{name}}', '{{position}}', '{{company}}', '{{department}}'],
            [
                $employee->user->name,
                $employee->position,
                config('app.name', 'BotJourney'),
                $employee->department?->name ?? '',
            ],
            $text
        );
    }
}
