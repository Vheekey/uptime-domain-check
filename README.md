# Domain Availability tracking with Laravel
So I came across this laravel package that helps you track if your domain or endpoint is down and when it comes up. I decided to try it out with some tweaks though. 

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
#### Step 2: Run Migration files
```bash
php artisan migrate
```
After doing those, you would notice the following:
- uptime.php would be created in /config
- endpoints table for storing endpoints
- statuses table for storing the ping status of the endpoints

#### Step 3: Add Endpoints
```bash
 php artisan endpoint:add <endpoint url> -f <frequency>
 ```
 e.g
 ```bash
 php artisan endpoint:add https://www.example.com -f 5
 ```
 PS: Frequency in minutes

#### Step 4: Register event listener
In the Event service provider
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

