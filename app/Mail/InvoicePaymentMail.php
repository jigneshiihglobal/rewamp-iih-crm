<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InvoicePaymentMail extends Mailable
{
    use Queueable, SerializesModels;
    public $invoice;
    public $currency;
    public $amount;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($invoice,$currency,$amount)
    {
        $this->invoice = $invoice;
        $this->currency = $currency;
        $this->amount = $amount;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $invoice_number = $this->invoice->invoice_number ?? '';

        $from_address = config('mail.from.address');
        $from_name = config('mail.from.name');
        $view = 'emails.invoices.wise_payment';

        return $this
            ->subject("Payment receive - {$invoice_number}")
            ->from($from_address, $from_name)
            ->view($view);
    }
}
