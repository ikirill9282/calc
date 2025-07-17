<?php

use App\Models\Order;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use App\Mail\OrderSuccess;
use App\Models\User;
use App\Models\Agent;
use Illuminate\Support\Facades\Schedule;
use Revolution\Google\Sheets\Facades\Sheets;

Schedule::command('app:load-sheet')->hourly();

Artisan::command('tt', function() {
  Order::find(100502)->writeSheet();
});