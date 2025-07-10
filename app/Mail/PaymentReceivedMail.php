<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PaymentReceivedMail extends Mailable
{
    use Queueable, SerializesModels;
    public string $customer;
    public string $amount;
    public string $currency;
    public string $salesPerson;
    public string $workspaceSlug;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(string $customer, string $amount, string $currency, string $salesPerson = "", string $workspaceSlug = 'iih-global')
    {
        $this->customer = $customer;
        $this->amount = $amount;
        $this->currency = $currency;
        $this->salesPerson = $salesPerson;
        $this->workspaceSlug = $workspaceSlug;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        if($this->workspaceSlug === 'shalin-designs') {
            $from_address = config('shalin-designs.accounts_mail.from.address');
            $from_name = config('shalin-designs.accounts_mail.from.name');
            $view = 'emails.shalin-designs.invoices.payment_received_mail';
        } else {
            $from_address = config('mail.accounts_mail_from.address');
            $from_name = config('mail.accounts_mail_from.name');
            $view = 'emails.invoices.payment_received_mail';
        }

        $subject = "Payment Received from " . $this->customer;
        return $this
            ->subject($subject)
            ->from($from_address, $from_name)
            ->view($view);
    }
}
