<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Phase extends Model
{
    protected $fillable = [
        'nomPhase',
        'ordre',
        'typePhase',
        'sous_traitant',
        'date_debut',
        'date_fin_prevue',
        'statutPhase',
        'est_en_retard',
        'chantier_id',
    ];

    protected $casts = [
        'date_debut'      => 'date',
        'date_fin_prevue' => 'date',
    ];

    public function chantier()
    {
        return $this->belongsTo(Chantier::class, 'chantier_id');
    }

    public function taches()
    {
        return $this->hasMany(Tache::class, 'phase_id')->orderBy('date_debut_prevue');
    }

    public function getAvancementAttribute(): int
    {
        return $this->taches->isEmpty() ? 0 : (int) round($this->taches->avg('avancement'));
    }

    public function getEstEnRetardAttribute(): bool
    {
        return $this->statutPhase !== 'terminee' && $this->date_fin_prevue && $this->date_fin_prevue->lt(today());
    }
}
