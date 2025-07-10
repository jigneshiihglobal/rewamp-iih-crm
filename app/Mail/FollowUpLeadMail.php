<?php

namespace App\Mail;

use App\Models\EmailSignature;
use App\Models\FollowUp;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FollowUpLeadMail extends Mailable
{
    use Queueable, SerializesModels;
    public FollowUp $followUp;
    public EmailSignature $email_signature;
    public string $workspaceSlug;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(FollowUp $followUp)
    {
        $this->followUp = $followUp;
        $this->workspaceSlug = $followUp->lead->workspace->slug ?? 'iih-global';
        $this->email_signature = $followUp->email_signature ?? ($followUp->sales_person->email_signatures->first() ?? null);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->from(
                $this->followUp->smtp_credential->from_address ??
                    ($this->followUp->sales_person->smtp_credentials->first()->from_address ??
                        $this->followUp->sales_person->email
                    ),
                $this->followUp->smtp_credential->from_name ??
                    ($this->followUp->sales_person->smtp_credentials->first()->from_name ??
                        config('mail.from.name')
                    )
            )
            ->subject($this->followUp->subject)
            ->view('emails.follow_ups.lead_mail');
    }
}
