<?php

namespace App\Providers;

use App\Models\LeadNote;
use App\Models\LeadStatus;
use App\Policies\LeadNotePolicy;
use App\Policies\LeadStatusPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
        LeadNote::class => LeadNotePolicy::class,
        LeadStatus::class => LeadStatusPolicy::class
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        //
    }
}
