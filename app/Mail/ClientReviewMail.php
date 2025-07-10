<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ClientReviewMail extends Mailable
{
    use Queueable, SerializesModels;

    public $subject, $content;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(string $subject, string $content)
    {
        $this->subject = $subject;
        $this->content = $content;
    }


    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $view = 'emails.clients.client_review_mail';
        $from_name = config('mail.from.name', null);
        $from_address = config('mail.from.address', null);        


        return $this
            ->from($from_address, $from_name)
            ->view($view);
    }
}
