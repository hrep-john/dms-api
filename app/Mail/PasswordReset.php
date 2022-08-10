<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PasswordReset extends Mailable
{
    use Queueable, SerializesModels;

    public string $link;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(String $link)
    {
        $this->link = $link;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.auth.password_reset')
            ->subject( config('app.name') . ' One-time Password Reset Link');
    }
}
