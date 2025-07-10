<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class StripepaymentMail extends Mailable
{
    use Queueable, SerializesModels;

   // public Invoice $invoice;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($invoice,$payload)
    {
        $this->invoice = $invoice;
        $this->payload = $payload;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $view = 'emails.stripe_test_mail';
        $from_addr = config('mail.from.welcome_mail');
        $from_name = config('mail.from.name');

        return $this
            ->from($from_addr, $from_name)
            ->subject("Invoice auto payment mail.")
            ->view($view)
            ->with([
                'invoice' => $this->invoice,
                'payload' => $this->payload,
            ]);
    }
}
