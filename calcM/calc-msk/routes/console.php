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
use Illuminate\Support\Facades\Log;
use App\Services\GoogleClient;

Schedule::command('app:load-sheet')->everyFifteenMinutes();
Schedule::command('app:write-sheet')->everyFiveMinutes();

Artisan::command('tt', function() {
  // $orders = Order::whereDoesntHave('print')->get();
  foreach (Order::all() as $order) {
    $data = $order->prepareSheetData();
    GoogleClient::write($data[0]);
  }
});

Artisan::command('ttp', function() {
  $user = User::where('email', 'youbizz.rus@gmail.com')->first();
});

Artisan::command('tts', function() {
  Log::debug('Schedule task');
});