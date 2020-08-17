<?php

namespace App\Listeners;

use App\Mail\EndpointUptime;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Infinitypaul\LaravelUptime\Events\EndpointIsBackUp;
use App\Notifications\UpTime;
use Illuminate\Support\Facades\Mail;

class URLIsBack
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
     * @param  EndpointIsBackUp  $event
     * @return void
     */
    public function handle(EndpointIsBackUp $event)
    {
        //
        $endpointStatus = $event->getEndpointStatus();
        $EndpointDetails = $event->getEndpoint();
        // dump($EndpointDetails);
        Mail::to("vicformidable@gmail.com")->send(new EndpointUptime($EndpointDetails)); //generic email
        Mail::to($EndpointDetails->notifies->pluck('email'))->send(new EndpointUptime($EndpointDetails)); //emails
    }
}
