<?php

use App\Models\Order;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use App\Mail\OrderSuccess;
use App\Mail\Reset;
use App\Models\User;
use App\Models\Agent;
use App\Services\GoogleClient;
use Dadata\DadataClient;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;
use Revolution\Google\Sheets\Facades\Sheets;

if (!env('APP_LOCAL', false)) {
  Schedule::command('app:load-sheet')->everyFifteenMinutes();
  Schedule::command('app:write-sheet')->everyFiveMinutes();
}
// Schedule::command('tts')->everyMinute();

Artisan::command('tt', function() {
  $orders = Order::whereDoesntHave('print')->get();
  foreach ($orders as $order) {
    $data = $order->prepareSheetData();
    GoogleClient::write($data[0]);
  }
});

Artisan::command('ttm', function() {
  Mail::to(User::find(1)->email)->send(new Reset(User::find(1)));
});

Artisan::command('tto', function() {
  foreach (Order::all() as $order) {
    $order->writeSheet();
  }
});

Artisan::command('tts', function() {
  Log::debug('Schedule task');
});