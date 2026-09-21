<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Approvisionnement extends Model
{
    protected $table = 'approvisionnements';
    protected $fillable = [
        'designation',
        'quantite_demandee',
        'unite',
        'priorite',
        'statutAppro',
        'date_livraison_souhaitee',
        'date_commande',
        'date_livraison_prevue',
        'chantier_id',
        'demandeur_id',
    ];

    protected $casts = [
        'date_commande'            => 'date',
        'date_livraison_souhaitee' => 'date',
        'date_livraison_prevue'    => 'date',
        'quantite_demandee'        => 'decimal:2',
    ];

    public function getQuantiteRestanteAttribute(): float
    {
        $recu = $this->bonReceptions->sum('quantite_recue');
        return max(0, (float) $this->quantite_demandee - $recu);
    }

    public function getStatutAttribute()
    {
        return $this->attributes['statutAppro'];
    }

    public function chantier()
    {
        return $this->belongsTo(Chantier::class, 'chantier_id');
    }

    public function demandeur()
    {
        return $this->belongsTo(User::class, 'demandeur_id');
    }

    public function bonReceptions()
    {
        return $this->hasMany(BonReception::class, 'demande_id');
    }
}
