<?php

namespace App\Observers;

use App\Models\Technician;
use daacreators\CreatorsTicketing\Models\Ticket;
use daacreators\CreatorsTicketing\Models\TicketStatus;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class TicketObserver
{
    public function saving(Ticket $ticket): void
    {
        // 1. Recupera lo stato di default se non è stato specificato.
        if (! $ticket->ticket_status_id) {
            $ticket->ticket_status_id = TicketStatus::query()
                ->where('is_default_for_new', true)
                ->value('id');
        }

        // 2. Verifica che le date siano coerenti.
        if ($ticket->opened_at && $ticket->on_site_at) {
            if (Carbon::parse($ticket->on_site_at)->lt(Carbon::parse($ticket->opened_at))) {
                throw ValidationException::withMessages([
                    'on_site_at' => 'La data On Site non può precedere la data di apertura.',
                ]);
            }
        }

        if ($ticket->closed_at && ! $ticket->opened_at) {
            throw ValidationException::withMessages([
                'opened_at' => 'La data di apertura è obbligatoria per inserire la chiusura.',
            ]);
        }

        if ($ticket->opened_at && $ticket->closed_at) {
            if (Carbon::parse($ticket->closed_at)->lt(Carbon::parse($ticket->opened_at))) {
                throw ValidationException::withMessages([
                    'closed_at' => 'La data di chiusura non può precedere la data di apertura.',
                ]);
            }
        }

        // La data On Site richiede una data di apertura.
        if ($ticket->on_site_at && ! $ticket->opened_at) {
            throw ValidationException::withMessages([
                'opened_at' => 'Inserisci la data di apertura prima della data On Site.',
            ]);
        }

        // La chiusura non può precedere l'intervento On Site.
        if ($ticket->on_site_at && $ticket->closed_at) {
            if (Carbon::parse($ticket->closed_at)->lt(Carbon::parse($ticket->on_site_at))) {
                throw ValidationException::withMessages([
                    'closed_at' => 'La data di chiusura non può precedere la data On Site.',
                ]);
            }
        }

        // 3. Quando viene inserita On Site, imposta In Progress
        // soltanto se lo stato non è stato modificato esplicitamente.

        if ($ticket->on_site_at && $ticket->isDirty('on_site_at')) {

            $statusWasChanged = $ticket->exists && $ticket->isDirty('ticket_status_id');

            $currentStatusIsDefault = TicketStatus::query()
                ->whereKey($ticket->ticket_status_id)
                ->where('is_default_for_new', true)
                ->exists();

            if (! $statusWasChanged && $currentStatusIsDefault) {

                $inProgressId = TicketStatus::query()
                    ->where('slug', 'in-progress')
                    ->value('id');

                if ($inProgressId) {
                    $ticket->ticket_status_id = $inProgressId;
                }
            }
        }

        // 4. Se lo stato è di chiusura, la data di chiusura è obbligatoria.
        $isClosingStatus = TicketStatus::query()
            ->whereKey($ticket->ticket_status_id)
            ->where('is_closing_status', true)
            ->exists();

        if ($isClosingStatus && ! $ticket->closed_at) {
            throw ValidationException::withMessages([
                'closed_at' => 'La data di chiusura è obbligatoria per chiudere il ticket.',
            ]);
        }
        if ($ticket->supplier_id && $ticket->technician_id) {
            $technicianBelongsToSupplier = Technician::query()
                ->whereKey($ticket->technician_id)
                ->where('supplier_id', $ticket->supplier_id)
                ->exists();

            if (! $technicianBelongsToSupplier) {
                throw ValidationException::withMessages([
                    'technician_id' => 'Il tecnico selezionato non appartiene al fornitore indicato.',
                ]);
            }
        }
    }
}
