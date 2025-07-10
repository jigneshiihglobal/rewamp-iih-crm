<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SalesUserInvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public $subject, $content, $workspaceSlug, $email_signature;

    /**
     * Create a new message instance.
     *
     * @return void
     */
     public function __construct(string $subject, string $content, $workspaceSlug = 'iih-global', $email_signature)
    {
        $this->subject = $subject;
        $this->content = $content;
        $this->email_signature = $email_signature;
        $this->workspaceSlug = $workspaceSlug;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

        $view = 'emails.sales_invoices.admin_mail_content';
        $from_name = config('mail.from.name', null);
        $from_address = config('mail.from.address', null);
         
        return $this
            ->from($from_address, $from_name)
            ->view($view);
    }
}
