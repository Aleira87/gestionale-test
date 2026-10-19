<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Observers\TicketObserver;
use daacreators\CreatorsTicketing\Models\Ticket;

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
        Ticket::observe(TicketObserver::class);
    }
}
