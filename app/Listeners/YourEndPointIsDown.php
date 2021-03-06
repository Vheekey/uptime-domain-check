<?php

namespace App\Listeners;

use App\Mail\EndpointDowntime;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Infinitypaul\LaravelUptime\Events\EndpointIsDown;
use App\Notifications\DownTime;
use App\Notify;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Mail;
use Infinitypaul\LaravelUptime\Endpoint;

class YourEndPointIsDown
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  EndpointIsDown  $event
     * @return void
     */
    public function handle(EndpointIsDown $event)
    {
        //
        $endpointStatus = $event->getEndpointStatus();
        $EndpointDetails = $event->getEndpoint();
        // dump($EndpointDetails);
        Mail::to(config('generic.genericEmail'))->send(new EndpointDowntime($EndpointDetails)); //generic email
        Mail::to($EndpointDetails->notifies->pluck('email'))->send(new EndpointDowntime($EndpointDetails)); //emails
    }
}
