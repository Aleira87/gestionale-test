<?php

namespace App\Filament\Resources\Tickets;

use App\Models\Ticket;
use App\Filament\Resources\Tickets\Pages;
use daacreators\CreatorsTicketing\Filament\Resources\Tickets\TicketResource as BaseTicketResource;

class TicketResource extends BaseTicketResource
{
    protected static ?string $model = Ticket::class;

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTickets::route('/'),
            'create' => Pages\CreateTicket::route('/create'),
            'view' => Pages\ViewTicket::route('/{record}'),
        ];
    }
}
