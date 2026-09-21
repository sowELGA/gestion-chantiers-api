<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pointage extends Model
{
    protected $table = 'pointages';
    protected $fillable = ['date', 'statutPointage', 'heures_sup', 'ouvrier_id', 'chantier_id', 'poste_id', 'taux_journalier', 'taux_heure_sup'];

    protected $casts = [
        'date'            => 'date',
        'heures_sup'      => 'decimal:1',
        'taux_journalier' => 'decimal:2',
        'taux_heure_sup'  => 'decimal:2',
    ];

    public function ouvrier()
    {
        return $this->belongsTo(Ouvrier::class, 'ouvrier_id');
    }

    public function chantier()
    {
        return $this->belongsTo(Chantier::class, 'chantier_id');
    }

    public function poste()
    {
        return $this->belongsTo(Poste::class, 'poste_id');
    }
}
