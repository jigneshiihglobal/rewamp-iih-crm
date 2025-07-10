<?php

namespace App\Notifications;

use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class LeadAssignedNotification extends Notification
{
    use Queueable;

    public $lead;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Lead $lead)
    {
        $this->lead = $lead;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        if (Auth::user()->active_workspace->slug === 'shalin-designs') {
            $from_name = config('shalin-designs.mail_from_names.leads');
            $from_address = config('shalin-designs.mail_from_emails.leads');
        } else {
            $from_name = config('custom.mail.from.name.leads');
            $from_address = config('mail.from.address');
        }

        return (new MailMessage)
            ->from($from_address, $from_name)
            ->subject("New lead assigned to you!")
            ->view('emails.leads.lead_assigned_to_you', [
                'notifiable' => $notifiable,
                'lead' => $this->lead,
                'workspaceSlug' => Auth::user()->active_workspace->slug ?? 'iih-global'
            ]);
    }
}
