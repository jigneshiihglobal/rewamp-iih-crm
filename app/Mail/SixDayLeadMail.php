<?php

namespace App\Mail;

use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SixDayLeadMail extends Mailable
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
        $view = 'emails.day_cron.six_day';
        $from_addr = config('mail.from.welcome_mail');
        $from_name = config('mail.from.name');

        return $this
            ->from($from_addr, $from_name)
            ->subject("Do you have my phone number?")
            ->view($view);
    }
}
