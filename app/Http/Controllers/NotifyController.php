<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Notify;
use Illuminate\Http\Request;
use Infinitypaul\LaravelUptime\Endpoint;

class NotifyController extends Controller
{
    //
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
}
