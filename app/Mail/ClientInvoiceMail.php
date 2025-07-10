<?php

namespace App\Mail;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;

class ClientInvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public $subject, $content, $pdfContent, $pdfName, $workspaceSlug;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(string $subject, string $content, $pdfContent, $pdfName, $workspaceSlug = 'iih-global')
    {
        $this->subject = $subject;
        $this->content = $content;
        $this->pdfContent = $pdfContent;
        $this->pdfName = $pdfName;
        $this->workspaceSlug = $workspaceSlug;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        switch ($this->workspaceSlug) {
            case 'shalin-designs':
                $view = 'emails.shalin-designs.invoices.client_invoice_mail';
                $from_name = config('shalin-designs.accounts_mail.from.name', null);
                $from_address = config('shalin-designs.accounts_mail.from.address');
                break;
            default:
                $view = 'emails.invoices.client_invoice_mail';
                $from_name = config('mail.accounts_mail_from.name', null);
                $from_address = config('mail.accounts_mail_from.address', null);
                break;
        }
        return $this
            ->from($from_address, $from_name)
            ->view($view)
            ->attachData($this->pdfContent, $this->pdfName, [
                'mime' => 'application/pdf',
            ]);
    }
}
