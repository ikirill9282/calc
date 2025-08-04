<?php

use App\Models\Order;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use App\Mail\OrderSuccess;
use App\Models\User;
use App\Models\Agent;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;
use Revolution\Google\Sheets\Facades\Sheets;

Schedule::command('app:load-sheet')->everyFifteenMinutes();
// Schedule::command('tts')->everyMinute();

Artisan::command('tt', function() {
  $o = Order::find(100532);
  dd($o->writeSheet());
  // foreach (Order::all() as $order) {
  //   $order->writeSheet();
  // }
});

Artisan::command('ttp', function() {
  $user = User::where('email', 'youbizz.rus@gmail.com')->first();
});

Artisan::command('tts', function() {
  Log::debug('Schedule task');
});