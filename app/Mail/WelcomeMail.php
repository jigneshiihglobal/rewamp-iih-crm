<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WelcomeMail extends Mailable
{
    use Queueable, SerializesModels;
    public $name;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $name = $this->name ?? '';
        $from_address = config('mail.from.welcome_mail');
        $from_name = config('mail.from.name');
        $view = 'emails.iih_welcome';

        return $this
            ->subject("We’ll be in touch {$name}")
            ->from($from_address, $from_name)
            ->view($view);
    }
}
