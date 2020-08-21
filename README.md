# Domain Availability tracking with Laravel
So I worked with this laravel package that helps you track if your domain or endpoint is down and when it comes up. I decided to try it out with some tweaks though. 

This project contains a mailing feature on downtime and yeah uptime too. Lets go!

## Setup

#### Step 1: Install the package via composer.
```bash
composer require infinitypaul/laravel-uptime
```

#### Step 2: Publish configuration and migration files
```bash
php artisan vendor:publish --provider="Infinitypaul\LaravelUptime\LaravelUptimeServiceProvider"
```
#### Step 3: Run Migration files
Create database then run migration files
```bash
php artisan migrate
```
After doing those, you would notice the following:
- uptime.php would be created in /config
- endpoints table for storing endpoints
- statuses table for storing the ping status of the endpoints

#### Step 4: Add Endpoints
```bash
 php artisan endpoint:add <endpoint url> -f <frequency>
 ```
 e.g
 ```bash
 php artisan endpoint:add https://www.example.com -f 5
 ```
 PS: Frequency in minutes

#### Step 5: Create custom mailing table
We'll be creating another model with migration to tie endpoint id(s) to email addresses to be notified
```bash
    php artisan make:model Notify -m 
```
In App/Notify.php
```bash
    namespace App;

    use Illuminate\Database\Eloquent\Model;
    use Infinitypaul\LaravelUptime\Endpoint;

    class Notify extends Model
    {
        //
        public function endpoint()
        {
            return $this->belongsTo(Endpoint::class);
        }
    }
```
In the migration file
```bash
    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    class CreateNotifiesTable extends Migration
    {
        /**
        * Run the migrations.
        *
        * @return void
        */
        public function up()
        {
            Schema::create('notifies', function (Blueprint $table) {
                $table->id();
                $table->integer('endpoint_id')->unsigned();
                $table->string('name');
                $table->string('email');

                $table->timestamps();

                $table->foreign('endpoint_id')
                    ->references('id')
                    ->on('endpoints')
                    ->onDelete('cascade');
            });
        }

        /**
        * Reverse the migrations.
        *
        * @return void
        */
        public function down()
        {
            Schema::dropIfExists('notifies');
        }
    }

```

#### Step 6: Configure .env and config for generic email
In our .env file, we'll be adding another constant so our .env must have the following:
```bash

    DB_DATABASE=monitoring

    MAIL_FROM_ADDRESS="realtime@uptime.com"
    MAIL_FROM_NAME="${APP_NAME}"

    GENERIC_EMAIL="example@gmail.com"

```
In config folder, create a file generic.php and add the following:

```bash
    <?php
    return [
    'genericEmail' => env('GENERIC_EMAIL')
    ];
```

#### Step 6: Create Mailing notifications
```bash
    php artisan make:mail EndpointDowntime --markdown=emails.downtime
```
```bash
    php artisan make:mail EndpointUptime --markdown=emails.uptime
```
In app\Mail\EndpointUptime.php
```bash
    <?php

    namespace App\Mail;

    use Illuminate\Bus\Queueable;
    use Illuminate\Contracts\Queue\ShouldQueue;
    use Illuminate\Mail\Mailable;
    use Illuminate\Queue\SerializesModels;
    use Infinitypaul\LaravelUptime\Endpoint;

    class EndpointUptime extends Mailable
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
            return $this->markdown('emails.uptime')
                        ->subject("Uptime Notification")
                        ->from(env("MAIL_FROM_ADDRESS"));
        }
    }

```

In app\Mail\EndpointDowntime.php
```bash
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
                        ->from(env("MAIL_FROM_ADDRESS"));
        }
    }
```

##### Create Content for mailing
In resources\views\emails\downtime.blade.php
```bash
    @component('mail::message')
    # Hi,

    This is to inform you that {{$endpoints->uri}} has been down {{$endpoints->status->created_at->diffForHumans()}}.

    Thanks,<br>
    Regards
    @endcomponent

```

In resources\views\emails\uptime.blade.php
```bash
    @component('mail::message')
    # Hi,

    This is to inform you that {{$endpoints->uri}} has been back up since {{$endpoints->status->created_at->diffForHumans()}}.

    Thanks,<br>
    Regards
    @endcomponent

```

#### Step 6: Register event listener
In the EventServiceProvider.php
```bash
    /**
    * The event listener mappings for the application.
    *
    * @var array
    */
    protected $listen = [
        ...
        EndpointIsBackUp::class => [
        "App\Listeners\URLIsBack",
        ],
        EndpointIsDown::class => [
            "App\Listeners\YourEndPointIsDown",
        ],
    ];
 ```

#### Step 6: Register events
In App/Listeners/URLIsBack.php we'll register events to be triggered

```bash
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
        Mail::to(config('generic.genericEmail'))->send(new EndpointUptime($EndpointDetails)); //generic email
        Mail::to($EndpointDetails->notifies->pluck('email'))->send(new EndpointUptime($EndpointDetails)); //other emails to be notified
    }
}

```

In App/Listeners/YourEndPointIsDown.php we'll register events to be triggered when endpoint is down

```bash
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
        Mail::to(config('generic.genericEmail'))->send(new EndpointDowntime($EndpointDetails)); //generic email
        Mail::to($EndpointDetails->notifies->pluck('email'))->send(new EndpointDowntime($EndpointDetails)); //other emails to be notified
    }
}

```


