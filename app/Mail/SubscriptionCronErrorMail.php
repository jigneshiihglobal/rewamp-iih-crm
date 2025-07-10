<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Throwable;

class SubscriptionCronErrorMail extends Mailable
{
    use Queueable, SerializesModels;

    public Throwable $error;
    public $invoice;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(
        Throwable $error    = null,
        $invoice    = null
    ) {
        $this->error    = $error;
        $this->invoice  = $invoice;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->from(config('mail.from.address'), config('custom.mail.from.name.sub_cron_error'))
            ->subject("IIH CRM - Error occurred while renewing invoices")
            ->view('emails.cron.subscription_renewal_error');
    }
}
