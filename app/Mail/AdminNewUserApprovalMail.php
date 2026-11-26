<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class AdminNewUserApprovalMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $approveUrl;
    public string $rejectUrl;
    public string $adminConsoleUrl;

    public function __construct(
        public User $registeredUser
    ) {
        $this->approveUrl = URL::signedRoute('admin.users.email-approve', ['targetUser' => $registeredUser->id]);
        $this->rejectUrl = URL::signedRoute('admin.users.email-reject', ['targetUser' => $registeredUser->id]);
        $this->adminConsoleUrl = route('admin.users.index');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[CediLedger SaaS] Action Required: Approve New User Account (' . $this->registeredUser->name . ')',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admin-approval-request',
        );
    }
}
