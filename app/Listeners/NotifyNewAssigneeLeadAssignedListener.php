<?php

namespace App\Listeners;

use App\Events\LeadAssigneeChangedEvent;
use App\Notifications\LeadAssignedNotification;
use Illuminate\Support\Facades\Auth;

class NotifyNewAssigneeLeadAssignedListener
{
    public function handle(LeadAssigneeChangedEvent $event)
    {
        $lead = $event->lead;
        if ($lead) {
            if ($lead->assigned_to != Auth::id()) {
                $lead->loadMissing(['assignee']);
                if ($lead->assignee) {
                    $lead->assignee->notify(new LeadAssignedNotification($event->lead));
                }
            }
        }
    }
}
