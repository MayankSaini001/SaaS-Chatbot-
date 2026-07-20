<?php
namespace App\Mail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SubscriptionCancelledMail extends Mailable
{
    use Queueable, SerializesModels;
    public function __construct(public $tenant) {}
    public function build()
    {
        return $this->subject('Your subscription has been cancelled')
            ->view('emails.subscription_cancelled');
    }
}