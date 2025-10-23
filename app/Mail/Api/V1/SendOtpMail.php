<?php

namespace App\Mail\Api\V1;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SendOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public $code;
    public function __construct($code)
    {
        $this->code = $code;
    }

    public function build()
    {
        return $this->subject('Verification code')
            ->markdown('email.otp_mail')
            ->with([
                'code' => $this->code,
            ]);
    }
}
