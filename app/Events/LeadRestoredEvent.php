<?php

namespace App\Events;

use App\Models\Lead;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LeadRestoredEvent
{
    use Dispatchable, SerializesModels;
    public $lead;

    public function __construct(Lead $lead)
    {
        $this->lead = $lead;
    }
}
