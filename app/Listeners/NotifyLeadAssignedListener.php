<?php

namespace App\Listeners;

use App\Notifications\LeadAssignedNotification;
use Illuminate\Support\Facades\Auth;

class NotifyLeadAssignedListener
{

    public function handle($event)
    {
        $lead = $event->lead;
        $oldLead = $event->oldLead ?? null;
        if ($lead) {
            if ($lead->assigned_to != Auth::id() && (!$oldLead || ($oldLead && $oldLead->assigned_to != $lead->assigned_to))) {
                $lead->loadMissing(['assignee', 'lead_status', 'lead_source']);
                if ($lead->assignee) {
                    $lead->assignee->notify(new LeadAssignedNotification($event->lead));
                }
            }
        }
    }
}
