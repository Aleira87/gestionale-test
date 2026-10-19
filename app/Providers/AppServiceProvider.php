<?php

namespace App\Providers;

use App\Models\Ticket;
use App\Observers\TicketObserver;
use daacreators\CreatorsTicketing\Observers\TicketObserver as BaseTicketObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Observer originale del plugin:
        // genera ticket_uid e gestisce i comportamenti originali.
        Ticket::observe(BaseTicketObserver::class);

        // Observer personalizzato:
        // valida date, stati, fornitore e tecnico.
        Ticket::observe(TicketObserver::class);
    }
}
