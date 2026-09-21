<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BonReception extends Model
{
    protected $table = 'bon_receptions';
    protected $fillable = ['quantite_recue', 'date_reception', 'observation', 'demande_id', 'chantier_id', 'receptionnee_par_id'];

    protected $casts = [
        'date_reception' => 'date',
        'quantite_recue' => 'decimal:2',
    ];

    public function demande()
    {
        return $this->belongsTo(Approvisionnement::class, 'demande_id');
    }

    public function chantier()
    {
        return $this->belongsTo(Chantier::class, 'chantier_id');
    }

    public function receptionneePar()
    {
        return $this->belongsTo(User::class, 'receptionnee_par_id');
    }
}
