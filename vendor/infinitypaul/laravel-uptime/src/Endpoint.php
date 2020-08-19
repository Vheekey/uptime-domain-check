<?php

namespace Infinitypaul\LaravelUptime;

use App\Notify;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

class Endpoint extends Model
{
    protected $table;

    protected $fillable = ['uri', 'frequency'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->table = Config::get('uptime.endpoints_table');
    }

    public function statuses()
    {
        return $this->hasMany(Status::class)->orderBy('created_at', 'desc');
    }

    public function status()
    {
        return $this->hasOne(Status::class)->orderBy('created_at', 'desc');
    }

    public function isBackUp()
    {
        return $this->status->isUp() && ($this->statuses->get(1) && $this->statuses->get(1)->isDown());
    }

    public function notifies()
    {
        return $this->hasMany(Notify::class);
    }
}
