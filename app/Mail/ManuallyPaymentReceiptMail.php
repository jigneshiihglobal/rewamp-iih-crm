<?php

namespace App\Mail;

use App\Models\Invoice;
use App\Services\InvoiceService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ManuallyPaymentReceiptMail extends Mailable
{
    use Queueable, SerializesModels;
    public Invoice $invoice;
    public  $custom_attach;
    public $receiptFile;
    public $receipt_subject;
    public $receiptContent;
    private InvoiceService $service;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Invoice $invoice,$custom_attach,$receiptFile,string $receipt_subject,string $receiptContent)
    {
        $this->invoice = $invoice;
        $this->custom_attach = $custom_attach;
        $this->receiptFile = $receiptFile;
        $this->receipt_subject = $receipt_subject;
        $this->receiptContent = $receiptContent;
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
        $custom_attach = $this->custom_attach ?? '';
        $receiptFile = $this->receiptFile ?? '';
        $receipt_subject = $this->receipt_subject ?? '';
        if($this->invoice->client->workspace->slug === 'shalin-designs') {
            $from_address = config('shalin-designs.accounts_mail.from.address');
            $from_name = config('shalin-designs.accounts_mail.from.name');
            $view = 'emails.shalin-designs.invoices.custome_payment_receipt';
        } else {
            $from_address = config('mail.accounts_mail_from.address');
            $from_name = config('mail.accounts_mail_from.name');
            $view = 'emails.invoices.custome_payment_receipt';
        }

        $content =  $this->subject($receipt_subject)
            ->from($from_address, $from_name)
            ->view($view);
        if(isset($receiptFile) && !empty($receiptFile)){
            $content =  $content->attachData(
                $this->service->pdfOutput($this->invoice, [], 'payment_receipt'),
                $this->service->pdf_name($invoice_number, 'payment_receipt'),
                ['mime' => 'application/pdf']
            );
        }
        if(isset($custom_attach) && !empty($custom_attach)){
            foreach($custom_attach as $file){
                $content = $content->attach($file);
            }
        }
        return $content;
    }
}
