<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SiteController;

Route::get('/', [SiteController::class, 'index'])->name('home');
Route::get('/history', [SiteController::class, 'history'])->name('history');
Route::get('/agents', [SiteController::class, 'agents'])->name('agents');
