<?php

namespace App\Notifications;

use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeadDeletedNotification extends Notification
{
    use Queueable;
    public $lead;

    public function __construct(Lead $lead)
    {
        $this->lead = $lead;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            "title" => "Lead (" . $this->lead->full_name . ") has been deleted!",
            "description" => "",
            "lead_id" => $this->lead->id
        ];
    }
}
