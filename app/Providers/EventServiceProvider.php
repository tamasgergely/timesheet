<?php

namespace App\Providers;

use App\Models\User;
use App\Models\Client;
use App\Models\Project;
use App\Models\Website;
use App\Observers\UserObserver;
use App\Observers\ClientObserver;
use App\Observers\ProjectObserver;
use App\Observers\WebsiteObserver;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

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
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        Client::observe(ClientObserver::class);
        Website::observe(WebsiteObserver::class);
        Project::observe(ProjectObserver::class);
        User::observe(UserObserver::class);
    }
}
