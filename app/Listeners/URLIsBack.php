<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Infinitypaul\LaravelUptime\Events\EndpointIsBackUp;
use App\Notifications\UpTime;



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
    }
}
