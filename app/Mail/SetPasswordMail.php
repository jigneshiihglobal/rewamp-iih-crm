<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;
    public $setPasswordLink, $workspaceSlug, $userName;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($setPasswordLink, $workspaceSlug, $userName)
    {
        $this->setPasswordLink = $setPasswordLink;
        $this->workspaceSlug = $workspaceSlug;
        $this->userName = $userName;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.auth.set_password')->from(config('mail.from.address'), config('custom.mail.from.name.set_password'));
    }
}
