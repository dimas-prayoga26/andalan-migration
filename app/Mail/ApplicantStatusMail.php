<?php

namespace App\Mail;

use App\Models\Applicant;
use App\Support\CareerBrand;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApplicantStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @param  array<string, mixed>  $brand
     */
    public function __construct(
        public Applicant $applicant,
        public array $brand,
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $from = $this->fromAddress();

        return new Envelope(
            from: $from,
            replyTo: [
                $from,
            ],
            subject: $this->subjectText(),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.applicants.status',
            with: [
                'applicant' => $this->applicant,
                'brand' => $this->brand,
            ],
        );
    }

    private function subjectText(): string
    {
        $statusName = $this->applicant->statusLabel();

        return 'Status Lamaran Anda: '.$statusName.' - '.$this->brandName();
    }

    private function fromAddress(): Address
    {
        return new Address($this->brandEmail(), $this->brandName());
    }

    private function brandEmail(): string
    {
        return (string) ($this->brand['email'] ?? CareerBrand::fallbackBrand()['email']);
    }

    private function brandName(): string
    {
        return (string) ($this->brand['name'] ?? CareerBrand::fallbackBrand()['name']);
    }
}
