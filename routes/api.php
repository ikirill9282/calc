<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;

Route::prefix('api')->group(function() {
  Route::post('/theme', function(Request $request) {
    $valid = $request->validate(['darkMode' => 'required|boolean']);
    Session::put('darkMode', $valid['darkMode']);
  });
});