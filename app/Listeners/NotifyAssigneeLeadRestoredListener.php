<?php

namespace App\Listeners;

use App\Events\LeadRestoredEvent;
use App\Notifications\LeadRestoredNotification;
use Illuminate\Support\Facades\Auth;

class NotifyAssigneeLeadRestoredListener
{
    public function handle(LeadRestoredEvent $event)
    {
        $lead = $event->lead;
        if ($lead) {
            if ($lead->assigned_to != Auth::id()) {
                $lead->loadMissing(['assignee']);
                if ($lead->assignee) {
                    $lead->assignee->notify(new LeadRestoredNotification($event->lead));
                }
            }
        }
    }
}
