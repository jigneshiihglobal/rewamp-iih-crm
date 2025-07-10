<?php

namespace App\Listeners;

use App\Events\LeadDeletedEvent;
use App\Notifications\LeadDeletedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Auth;

class NotifyAssigneeLeadDeletedListener
{
    public function handle(LeadDeletedEvent $event)
    {
        $lead = $event->lead;
        if($lead) {
            if($lead->assigned_to != Auth::id()) {
                $lead->loadMissing(['assignee']);
                if($lead->assignee) {
                    $lead->assignee->notify(new LeadDeletedNotification($event->lead));
                }
            }
        }
    }
}
