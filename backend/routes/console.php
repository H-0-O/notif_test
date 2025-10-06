<?php

use App\Facades\Notification;
use Illuminate\Support\Facades\Schedule;

Schedule::call(function(){
    Notification::deliver();
})->everyMinute();