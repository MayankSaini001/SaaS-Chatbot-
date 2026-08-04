<?php
namespace App\Mail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SubscriptionActivatedMail extends Mailable
{
    use Queueable, SerializesModels;
    public function __construct(public $tenant, public string $plan) {}
    public function build()
    {
        return $this->subject('Your subscription is active!')
            ->view('emails.subscription_activated');
    }
}