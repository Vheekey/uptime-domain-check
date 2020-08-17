<?php

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
