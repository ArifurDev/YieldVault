<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotifyFA extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $title;
    public string $otp;
    public int $expiry;

    public function __construct(string $title, string $otp, int $expiry)
    {
        $this->title  = $title;
        $this->otp    = $otp;
        $this->expiry = $expiry;
    }

    public function build()
    {
        return $this->subject($this->title)
            ->markdown('email.notify_two_fa_mail', [
                'title'  => $this->title,
                'otp'    => $this->otp,
                'expiry' => $this->expiry,
            ]);
    }
}
