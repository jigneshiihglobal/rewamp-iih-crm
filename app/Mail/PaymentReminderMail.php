<?php

namespace App\Mail;

use App\Models\Invoice;
use App\Services\InvoiceService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PaymentReminderMail extends Mailable
{
    use Queueable, SerializesModels;
    public Invoice $invoice;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(InvoiceService $service)
    {
        $pdfName = $service->pdf_name($this->invoice->invoice_number, 'invoice', $this->invoice->client->workspace->slug ?? 'iih-global');
        $pdfContent = $service->pdfOutput($this->invoice);
        if ($this->invoice->client->workspace->slug === 'shalin-designs') {
            $view = 'emails.shalin-designs.invoices.payment_reminder';
            $from_addr = config('shalin-designs.accounts_mail.from.address');
            $from_name = config('shalin-designs.accounts_mail.from.name');
        } else {
            $view = 'emails.invoices.payment_reminder';
            $from_addr = config('mail.accounts_mail_from.address');
            $from_name = config('mail.accounts_mail_from.name');
        }

        return $this
            ->from($from_addr, $from_name)
            ->subject("Friendly Reminder: Unpaid Invoice " . $this->invoice->invoice_number)
            ->view($view)
            ->attachData($pdfContent, $pdfName, [
                'mime' => 'application/pdf',
            ]);
    }
}
