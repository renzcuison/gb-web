<?php

namespace App\Mail;

use App\Models\Inquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InquiryReceivedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Inquiry $inquiry
    ) {
        $this->inquiry->loadMissing(['product', 'variant']);
    }

    public function envelope(): Envelope
    {
        $productName = $this->inquiry->product?->name ?? 'Product Inquiry';

        return new Envelope(
            subject: "Inquiry Received (#{$this->inquiry->id}) — {$productName} | GreatBuy Originals",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.inquiry-received',
        );
    }
}