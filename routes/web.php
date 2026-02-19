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

Route::middleware(config('filament.middleware.auth'))->group(function () {
    Route::get('/admin/orders/{order}/download', [\App\Http\Controllers\Filament\OrderExportController::class, 'show'])
        ->name('filament.resources.orders.export-single');
});

Route::prefix('auth')->controller(AuthController::class)->group(function() {
  Route::match(['get', 'post'], '/logout', 'logout')->name('logout');
  Route::post('/login', 'login')->name('login');
  Route::post('/register', 'register')->name('register');
  Route::match(['get', 'post'], '/reset', 'reset')->name('password.reset');
  Route::post('/change', 'change')->name('password.change');
  Route::get('/verify/{hash}', 'verify')->name('verify');
});
