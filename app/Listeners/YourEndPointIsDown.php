<?php

namespace App\Listeners;

use App\Mail\EndpointDowntime;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Infinitypaul\LaravelUptime\Events\EndpointIsDown;
use App\Notifications\DownTime;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Mail;

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
        Mail::to("vicformidable@gmail.com")->send(new EndpointDowntime($EndpointDetails));
        // dump($endpointStatus);
    }
}
