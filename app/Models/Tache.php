<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tache extends Model
{
    use HasFactory;

    protected $fillable = [
        'nomTache',
        'date_debut_prevue',
        'date_fin_prevue',
        'date_debut_reelle',
        'date_fin_reelle',
        'avancement',
        'statutTache',
        'est_en_retard',
        'chantier_id',
        'phase_id',
        'responsable_id',
        'tache_precedente_id',
    ];

    protected $casts = [
        'date_debut_prevue' => 'date',
        'date_fin_prevue'   => 'date',
        'date_debut_reelle' => 'date',
        'date_fin_reelle'   => 'date',
        'avancement'        => 'integer',
        'est_en_retard'     => 'boolean',
    ];

    public function phase()
    {
        return $this->belongsTo(Phase::class, 'phase_id');
    }

    public function chantier()
    {
        return $this->belongsTo(Chantier::class, 'chantier_id');
    }

    public function responsable()
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }

    public function tachePrecedente()
    {
        return $this->belongsTo(Tache::class, 'tache_precedente_id');
    }

    public function getEstEnRetardAttribute(): bool
    {
        return $this->statutTache !== 'terminee' && $this->date_fin_prevue && $this->date_fin_prevue->lt(today());
    }
}
