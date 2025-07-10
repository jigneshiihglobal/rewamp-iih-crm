<?php

namespace App\Providers;

use App\Contracts\CommunicationAPI;
use App\Contracts\GeocodingAPI;
use App\Database\Doctrine\Types\TimestampType;
use App\Models\Client;
use App\Observers\ClientObserver;
use App\Services\MapQuestService;
use App\Services\SlackService;
use Doctrine\DBAL\Types\Type;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use App\Models\Invoice;

class AppServiceProvider extends ServiceProvider
{
    /**
     * All of the container bindings that should be registered.
     *
     * @var array
     */
    public $bindings = [
        GeocodingAPI::class => MapQuestService::class,
        CommunicationAPI::class => SlackService::class,
    ];

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Type::addType('timestamp', TimestampType::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(125);
        Client::observe(ClientObserver::class);
    }
}
