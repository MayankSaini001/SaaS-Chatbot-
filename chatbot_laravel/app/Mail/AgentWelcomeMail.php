<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AgentWelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $agentName;
    public string $agentEmail;
    public string $agentPassword;
    public string $loginUrl;

    public function __construct(string $name, string $email, string $password)
    {
        $this->agentName     = $name;
        $this->agentEmail    = $email;
        $this->agentPassword = $password;
        $this->loginUrl      = config('app.url') . '/login';
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome to ' . config('app.name') . ' — Your Login Details',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.agent_welcome',
        );
    }
}