<?php

namespace App\Mail;

use App\Models\FollowUp;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FollowUpCallReminder extends Mailable
{
    use Queueable, SerializesModels;
    public FollowUp $followUp;
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
                $view = 'emails.shalin-designs.follow_ups.reminder';
                $from_addr = config('shalin-designs.mail_from_emails.follow_up_reminder');
                $from_name = config('shalin-designs.mail_from_names.follow_up_reminder', 'Shalin Designs - Follow up');
                break;

            default:
                $view = 'emails.follow_ups.reminder';
                $from_addr = config('mail.from.address');
                $from_name = config('custom.mail.from.name.follow_up_reminder', 'IIH CRM - Follow up');
                break;
        }

        return $this
            ->from($from_addr,  $from_name)
            ->subject('You need to do a follow up!')
            ->view($view);
    }
}
