<?php
namespace App\Mail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PaymentFailedMail extends Mailable
{
    use Queueable, SerializesModels;
    public function __construct(public $tenant) {}
    public function build()
    {
        return $this->subject('Payment failed — action required')
            ->view('emails.payment_failed');
    }
}