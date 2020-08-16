<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use Infinitypaul\LaravelUptime\Events\EndpointIsBackUp;
use Infinitypaul\LaravelUptime\Events\EndpointIsDown;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        EndpointIsBackUp::class => [
            "App\Listeners\URLIsBack",
        ],
        EndpointIsDown::class => [
            "App\Listeners\YourEndPointIsDown",
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
