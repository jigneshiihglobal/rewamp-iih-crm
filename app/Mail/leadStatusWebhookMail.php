<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class leadStatusWebhookMail extends Mailable
{
    use Queueable, SerializesModels;
    public $payload;
    public $lead_status_event;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($payload,$lead_status_event)
    {
        $this->payload = $payload;
        $this->lead_status_event = $lead_status_event;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $view = 'emails.lead_status_event_view';
        $from_addr = config('mail.from.welcome_mail');
        $from_name = config('mail.from.name');

        return $this
            ->from($from_addr, $from_name)
            ->subject("Lead status event json ")
            ->view($view);

    }
}
