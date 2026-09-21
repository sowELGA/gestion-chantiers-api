<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chantier extends Model
{
    use HasFactory;

    protected $fillable = [
        'nomChantier',
        'localisation',
        'budget_prevu',
        'date_debut',
        'date_fin_prevue',
        'date_fin_reelle',
        'statut',
        'chef_projet_id',
        'pointeur_id',
    ];

    protected $casts = [
        'date_debut'      => 'date',
        'date_fin_prevue' => 'date',
        'date_fin_reelle' => 'date',
        'budget_prevu'    => 'decimal:2',
    ];

    // ── Accesseurs ──────────────────────────────────────────
    public function getBudgetConsommeAttribute(): float
    {
        if ($this->relationLoaded('depenses')) {
            return (float) $this->depenses->sum('montant');
        }
        return (float) $this->depenses()->sum('montant');
    }

    public function getBudgetRestantAttribute(): ?float
    {
        if ($this->budget_prevu === null) {
            return null;
        }
        return (float) $this->budget_prevu - $this->budget_consomme;
    }

    public function getPourcentageBudgetAttribute(): ?float
    {
        if ($this->budget_prevu === null || (float) $this->budget_prevu == 0) {
            return null;
        }
        return round(($this->budget_consomme / $this->budget_prevu) * 100, 2);
    }

    public function getAvancementGlobalAttribute(): float
    {
        if ($this->relationLoaded('taches')) {
            $taches = $this->taches;
            return $taches->isEmpty() ? 0 : round($taches->avg('avancement'), 2);
        }
        $moyenne = $this->taches()->avg('avancement');
        return $moyenne !== null ? round((float) $moyenne, 2) : 0;
    }

    public function getEstEnRetardAttribute(): bool
    {
        return $this->date_fin_prevue->lt(today()) && $this->statut !== 'livre';
    }

    public const TRANSITIONS = [
        'en_attente' => ['en_cours' => 'Démarrer le chantier'],
        'en_cours'   => ['suspendu' => 'Suspendre', 'livre' => 'Marquer comme livré'],
        'suspendu'   => ['en_cours' => 'Reprendre le chantier'],
        'livre'      => [],
    ];

    public function transitionsDisponibles(): array
    {
        return self::TRANSITIONS[$this->statut] ?? [];
    }

    // ── Relations ───────────────────────────────────────────
    public function chefProjet()
    {
        return $this->belongsTo(User::class, 'chef_projet_id');
    }

    public function pointeur()
    {
        return $this->belongsTo(User::class, 'pointeur_id');
    }

    public function phases()
    {
        return $this->hasMany(Phase::class, 'chantier_id')->orderBy('ordre');
    }

    public function taches()
    {
        return $this->hasMany(Tache::class, 'chantier_id');
    }

    public function depenses()
    {
        return $this->hasMany(DepensesChantier::class, 'chantier_id');
    }

    public function ouvriers()
    {
        return $this->hasMany(Ouvrier::class, 'chantier_id');
    }

    public function tauxSalaires()
    {
        return $this->hasMany(TauxSalaire::class, 'chantier_id');
    }

    public function pointages()
    {
        return $this->hasMany(Pointage::class, 'chantier_id');
    }

    public function recapsHebdomadaires()
    {
        return $this->hasMany(RecapHebdomadaire::class, 'chantier_id');
    }

    public function approvisionnements()
    {
        return $this->hasMany(Approvisionnement::class, 'chantier_id');
    }

    public function bonReceptions()
    {
        return $this->hasMany(BonReception::class, 'chantier_id');
    }

    public function rapports()
    {
        return $this->hasMany(RapportChantier::class, 'chantier_id');
    }

    public function affectations()
    {
        return $this->hasMany(UserChantier::class);
    }

    // ⚠️ Adapté au nouveau système de rôles : on vérifie via la relation
    // roles() du user affecté, plus via une colonne 'role' qui n'existe plus.
    public function historiqueChefsProjets()
    {
        return $this->affectations()
            ->whereHas('user.roles', fn($q) => $q->where('nom', 'chef_projet'))
            ->with('user')
            ->orderByDesc('debut_affectation');
    }

    public function historiquePointeurs()
    {
        return $this->affectations()
            ->whereHas('user.roles', fn($q) => $q->where('nom', 'pointeur'))
            ->with('user')
            ->orderByDesc('debut_affectation');
    }
}
