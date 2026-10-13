<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    // con fillable diciamo quindi quali campi possono essere assegnati tramite operazione di mass assignment
    protected $fillable = [
        'name',
        'vat_number',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
        // il metodo users definisce definisce una relazione uno-a-molti
        // una azienda può avere più users associati
        // Laravel quindi si aspetta company_id sul modello user
    }

    public function managers(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withTimestamps();
    }
}
