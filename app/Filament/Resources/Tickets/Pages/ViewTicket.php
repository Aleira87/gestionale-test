<?php

namespace App\Filament\Resources\Tickets\Pages;

use App\Filament\Resources\Tickets\TicketResource;
use daacreators\CreatorsTicketing\Filament\Resources\Tickets\Pages\ViewTicket as BaseViewTicket;

class ViewTicket extends BaseViewTicket
{
    protected static string $resource = TicketResource::class;
}
