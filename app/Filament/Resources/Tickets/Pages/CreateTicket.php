<?php

namespace App\Filament\Resources\Tickets\Pages;

use App\Filament\Resources\Tickets\TicketResource;
use daacreators\CreatorsTicketing\Filament\Resources\Tickets\Pages\CreateTicket as BaseCreateTicket;

class CreateTicket extends BaseCreateTicket
{
    protected static string $resource = TicketResource::class;
}
