<?php

use App\Models\Order;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use App\Mail\OrderSuccess;
use App\Mail\Reset;
use App\Models\User;
use App\Models\Agent;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;
use Revolution\Google\Sheets\Facades\Sheets;

if (!env('APP_LOCAL', false)) {
  Schedule::command('app:load-sheet')->everyFifteenMinutes();
}
// Schedule::command('tts')->everyMinute();

Artisan::command('tt', function() {
  dd(User::find(1)->makeResetUrl());
});

Artisan::command('ttm', function() {
  Mail::to(User::find(1)->email)->send(new Reset(User::find(1)));
});

Artisan::command('tts', function() {
  Log::debug('Schedule task');
});