<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotifyUser extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public $title;
    public $message;
    public function __construct($title,$message)
    {
        $this->title = $title;
        $this->message = $message;
    }

    public function build()
    {
        return $this->subject($this->title)
            ->markdown('email.notify_user_mail')
            ->with([
               'title' => $this->title,
                'message' => $this->message,
            ]);
    }

}
