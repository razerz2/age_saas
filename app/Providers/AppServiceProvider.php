<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Platform\SystemNotification;
use Illuminate\Support\Facades\View;

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
        View::composer('layouts.freedash.partials.notifications', function ($view) {
            $view->with('notifications', SystemNotification::latest('created_at')->take(5)->get());
        });
    }
}
