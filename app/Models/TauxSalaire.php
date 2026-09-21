<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TauxSalaire extends Model
{
    protected $table = 'taux_salaires';
    protected $fillable = ['taux_journalier', 'taux_heure_sup', 'poste_id', 'chantier_id'];

    protected $casts = [
        'taux_journalier' => 'decimal:2',
        'taux_heure_sup'  => 'decimal:2',
    ];

    public function poste()
    {
        return $this->belongsTo(Poste::class, 'poste_id');
    }

    public function chantier()
    {
        return $this->belongsTo(Chantier::class, 'chantier_id');
    }
}
