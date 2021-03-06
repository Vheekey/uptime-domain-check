# Domain Availability tracking with Laravel
So I worked with this laravel [package](https://github.com/infinitypaul/laravel-uptime) by infinitypaul that helps you track if your domain or endpoint is down and when it comes up. I decided to try it out with some tweaks though.

You can refer to the full package here: https://github.com/infinitypaul/laravel-uptime

This project contains a mailing feature on downtime and yeah uptime too which is a tweak to the original feature of the package. Lets go!

#### Step 1: Clone the repository.
```bash
git clone https://github.com/Vheekey/uptime-domain-check.git
```

#### Step 2: Create custom mailing table
We'll be creating another model with migration to tie endpoint id(s) to email addresses to be notified
```bash
    php artisan make:model Notify -m 
```
In App/Notify.php
```bash
    namespace App;

    use Illuminate\Database\Eloquent\Model;
    use Infinitypaul\Laravel\Uptime\Endpoint;

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

#### Step 3: Configure .env and config for generic email
In our .env file, we'll be adding another constant so our .env must contain the following:
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

#### Step 4: Create Mailing notifications
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

#### Step 5: Register event listener
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
#### Step 7: Create controller
This controller is to add notification email addresses to the table so they can be notified whenever any endpoint is down or back.

```bash
    php artisan make:controller NotifyController
```
In the app\Http\Controllers\NotifyController.php
```bash
    <?php

    namespace App\Http\Controllers;

    use App\Http\Controllers\Controller;
    use App\Notify;
    use Illuminate\Http\Request;
    use Infinitypaul\LaravelUptime\Endpoint;

    class NotifyController extends Controller
    {
        public function createNotifiers(Request $request, Endpoint $endpoint){
            $request->validate([
                'name' => [
                    'required',
                    'string',
                    'min:8',
                    'regex:/^([a-z]+\s[a-z]+(\s[a-z]+)?)$/i'
                ],
                'email' => 'required|email:dns',
            ]);

            $notifiers = new Notify();
            $notifiers->endpoint_id = $endpoint['id'];
            $notifiers->name = $request->name;
            $notifiers->email = $request->email;
            $notifiers->save();

            return response()->json(["message"=>$request->name." successfully added to ".$endpoint['uri']],200);

        }

        public function removeNotifiers(Request $request, Endpoint $endpoint){
            $request->validate([
                'email' => 'required|email:dns',
            ]);
            $deleted = Notify::where('email', $request->email)
                    ->where('endpoint_id', $endpoint['id'])
                    ->delete();

            return response()->json(['message'=>$request->email.' successfully deleted from '.$endpoint['uri'], 200]);
        }
    }

```

#### Step 8: Add api endpoints
In routes\api.php

```bash
    Route::post('notifiers/{endpoint}', 'NotifyController@createNotifiers');
    Route::post('remove/{endpoint}', 'NotifyController@removeNotifiers');
```

##### Pinging endpoints
Now when we run:
```bash
    php artisan uptime:run
```
Endpoints will be pinged and emails sent with status of endpoints to email addresses attached.

##### To check the status of endpoints
```bash
    php artisan uptime:status
```

![Status](https://raw.githubusercontent.com/infinitypaul/laravel-uptime/master/screen.jpeg)

And we're done!!!
