<?php

namespace App\Filament\Resources\Tickets\Pages;

use App\Filament\Resources\Tickets\TicketResource;
use daacreators\CreatorsTicketing\Filament\Resources\Tickets\Pages\ListTickets as BaseListTickets;

class ListTickets extends BaseListTickets
{
    protected static string $resource = TicketResource::class;
}
