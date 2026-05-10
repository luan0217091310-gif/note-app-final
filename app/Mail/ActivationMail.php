<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ActivationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public $user, public string $token) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: '[Note App] Kích hoạt tài khoản của bạn');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.activation');
    }
}
