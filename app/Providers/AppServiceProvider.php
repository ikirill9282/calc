<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Model;
use Opcodes\LogViewer\Facades\LogViewer;
use App\Models\Order;
use App\Observers\OrderObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::unguard();
        Order::observe(OrderObserver::class);
        LogViewer::auth(function ($request) {
          return $request->user() && in_array($request->user()->email, [
            'service882211777@gmail.com',
            'errewer123@gmail.com',
            'ikirillmol2018@gmail.com',
          ]);
        });
    }
}
