<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Infinitypaul\LaravelUptime\Endpoint;

class EndpointDowntime extends Mailable
{
    use Queueable, SerializesModels;

    public $endpoints;


    /**
     * Create a new message instance.
     *
     * @return void
     */

    public function __construct(Endpoint $endpoint)
    {
        //
        $this->endpoints = $endpoint;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.downtime')
                    ->subject("Downtime Notification")
                    ->from("infinitypaul@live.com");
    }
}
