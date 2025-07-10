<?php

namespace App\Mail;

use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FollowUpSevenMail extends Mailable
{
    use Queueable, SerializesModels;
    public Lead $lead;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Lead $lead)
    {
        $this->lead = $lead;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $view = 'emails.follow_up_day.seven_mail';
        $from_addr = config('mail.from.welcome_mail');
        $from_name = config('mail.from.name');

        return $this
            ->from($from_addr, $from_name)
            ->subject("Are you building a scalable business?")
            ->view($view);
    }
}
