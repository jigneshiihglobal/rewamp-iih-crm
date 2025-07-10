<?php

namespace App\Providers;

use App\Events\LeadAssigneeChangedEvent;
use App\Events\LeadCreatedEvent;
use App\Events\LeadDeletedEvent;
use App\Events\LeadRestoredEvent;
use App\Events\LeadUpdatedEvent;
use App\Listeners\NotifyAssigneeLeadDeletedListener;
use App\Listeners\NotifyAssigneeLeadRestoredListener;
use App\Listeners\NotifyLeadAssignedListener;
use App\Listeners\NotifyNewAssigneeLeadAssignedListener;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        LeadCreatedEvent::class => [
            NotifyLeadAssignedListener::class,
        ],
        // LeadAssigneeChangedEvent::class => [
        //     NotifyNewAssigneeLeadAssignedListener::class
        // ],
        // LeadRestoredEvent::class => [
        //     NotifyAssigneeLeadRestoredListener::class
        // ],
        // LeadDeletedEvent::class => [
        //     NotifyAssigneeLeadDeletedListener::class
        // ]
        LeadUpdatedEvent::class => [
            NotifyLeadAssignedListener::class
        ]
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
