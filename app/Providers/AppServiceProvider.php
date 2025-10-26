<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Platform\SystemNotification;
use App\Models\Platform\Invoices;
use App\Observers\InvoiceObserver;
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
        // 🔔 Compartilha as 5 últimas notificações com o layout Freedash
        View::composer('layouts.freedash.partials.notifications', function ($view) {
            $view->with('notifications', SystemNotification::latest('created_at')->take(5)->get());
        });

        // 📡 Registra o observer de faturas (envio automático ao Asaas)
        Invoices::observe(InvoiceObserver::class);
    }
}
