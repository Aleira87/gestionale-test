<?php

namespace App\Models;

use daacreators\CreatorsTicketing\Models\Ticket as BaseTicket;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ticket extends BaseTicket
{
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(Technician::class);
    }
}
