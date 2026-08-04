<?php

namespace App\Mail;

use App\Models\Conversation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ConversationTranscriptMail extends Mailable
{
    use Queueable, SerializesModels;

    public Conversation $conversation;
    public $messages;
    public string $visitorName;
    public string $tenantName;

    public function __construct(Conversation $conversation)
    {
        $this->conversation = $conversation;

        $this->messages = $conversation->messages()
            ->where('sender_type', '!=', 'system')
            ->oldest()
            ->get();

        $this->visitorName = $conversation->visitor_name ?: 'there';
        $this->tenantName  = optional($conversation->tenant)->name ?: config('app.name');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Chat Transcript — ' . $this->tenantName,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.conversation_transcript',
        );
    }
}
