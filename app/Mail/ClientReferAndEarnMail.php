<?php

namespace App\Mail;

use App\Models\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ClientReferAndEarnMail extends Mailable
{
    use Queueable, SerializesModels;
    public Client $client;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $view = 'emails.client_refer_earn.refer_and_earn';
        $from_addr = config('mail.from.welcome_mail');
        $from_name = "IIH Global";

        return $this
            ->from($from_addr, $from_name)
            ->subject("Introducing Our Refer and Earn Program: Earn $100 Cash or Amazon Voucher!")
            ->view($view);
    }
}
