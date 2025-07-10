<?php

namespace App\Mail;

use App\Models\Invoice;
use App\Services\InvoiceService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PaymentReceiptMail extends Mailable
{
    use Queueable, SerializesModels;
    public Invoice $invoice;
    private InvoiceService $service;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
        $this->service = new InvoiceService();
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $invoice_number = $this->invoice->invoice_number ?? '';
        if($this->invoice->client->workspace->slug === 'shalin-designs') {
            $from_address = config('shalin-designs.accounts_mail.from.address');
            $from_name = config('shalin-designs.accounts_mail.from.name');
            $view = 'emails.shalin-designs.invoices.payment_receipt';
        } else {
            $from_address = config('mail.accounts_mail_from.address');
            $from_name = config('mail.accounts_mail_from.name');
            $view = 'emails.invoices.payment_receipt';
        }

        return $this
            ->subject("Payment Receipt - {$invoice_number}")
            ->from($from_address, $from_name)
            ->view($view)
            ->attachData(
                $this->service->pdfOutput($this->invoice, [], 'payment_receipt'),
                $this->service->pdf_name($invoice_number, 'payment_receipt'),
                ['mime' => 'application/pdf']
            );
    }
}
