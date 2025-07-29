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

Schedule::command('app:load-sheet')->everyMinute();

Artisan::command('tt', function() {
  foreach (Order::all() as $order) {
    $order->writeSheet();
  }
});

Artisan::command('ttp', function() {
  $user = User::where('email', 'youbizz.rus@gmail.com')->first();
  $pwd = User::password_hash('Gavno123');
  dd($pwd, $user->password);
});