<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SiteController;
use App\Models\Order;

include __DIR__ . '/api.php';

Route::get('/', [SiteController::class, 'index'])->name('home');

Route::get('/mail/{view}', function($view) {
  return view("mail.$view", ['order' => Order::find(100500)]);
});

Route::middleware('auth:web')->group(function() {
  Route::get('/history', [SiteController::class, 'history'])->name('history');
  Route::get('/agents', [SiteController::class, 'agents'])->name('agents');
  Route::get('/success', [SiteController::class, 'success'])->name('success');
});


Route::prefix('auth')->controller(AuthController::class)->group(function() {
  Route::match(['get', 'post'], '/logout', 'logout')->name('logout');
  Route::post('/login', 'login')->name('login');
  Route::post('/reset-password', 'reset')->name('password.reset');
  Route::post('/register', 'register')->name('register');
  Route::get('/verify/{hash}', 'verify')->name('verify');
});
