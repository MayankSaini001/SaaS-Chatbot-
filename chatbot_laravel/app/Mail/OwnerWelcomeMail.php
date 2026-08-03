<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OwnerWelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $ownerName;
    public $ownerEmail;
    public $ownerPassword;
    public $companyName;
    public $loginUrl;
    public $pricingUrl;
    public $appName;

    public function __construct($name, $email, $password, $companyName)
    {
        $this->ownerName     = $name;
        $this->ownerEmail    = $email;
        $this->ownerPassword = $password;
        $this->companyName   = $companyName;
        $this->appName       = config('app.name');
        $this->loginUrl      = config('app.url') . '/login';
        $this->pricingUrl    = config('app.url') . '/pricing';
    }

    public function build()
    {
        return $this
            ->subject('🎉 Welcome to ' . $this->appName . ' — Your Account is Ready!')
            ->view('emails.owner_welcome');
    }
}