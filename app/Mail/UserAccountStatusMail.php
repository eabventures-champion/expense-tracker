<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserAccountStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $loginUrl;

    public function __construct(
        public User $user,
        public string $status // 'approved' or 'rejected'
    ) {
        $this->loginUrl = route('login');
    }

    public function envelope(): Envelope
    {
        $subject = $this->status === 'approved'
            ? 'Welcome to CediLedger! Your Financial Workspace Has Been Approved'
            : 'Update Regarding Your CediLedger Account Request';

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.user-account-status',
        );
    }
}
