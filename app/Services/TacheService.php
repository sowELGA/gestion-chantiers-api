<?php

namespace App\Services;

use App\Models\Phase;
use App\Models\Tache;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TacheService
{
    // ── PHASES ────────────────────────────────────────────
    public function creerPhase(array $data, int $chantierId): Phase
    {
        return Phase::create([
            'nomPhase'        => $data['nomPhase'],
            'ordre'           => $data['ordre'],
            'typePhase'       => $data['typePhase'],
            'sous_traitant'   => $data['sous_traitant'] ?? null,
            'date_debut'      => $data['date_debut'],
            'date_fin_prevue' => $data['date_fin_prevue'],
            'est_en_retard'   => false,
            'statutPhase'     => 'en_attente',
            'chantier_id'     => $chantierId,
        ]);
    }

    public function modifierPhase(Phase $phase, array $data): Phase
    {
        $phase->update([
            'nomPhase'        => $data['nomPhase'],
            'ordre'           => $data['ordre'],
            'typePhase'       => $data['typePhase'],
            'sous_traitant'   => $data['sous_traitant'] ?? null,
            'date_debut'      => $data['date_debut'],
            'date_fin_prevue' => $data['date_fin_prevue'],
        ]);
        return $phase->fresh();
    }

    public function supprimerPhase(Phase $phase): void
    {
        $this->verifierPhaseSupprimable($phase);
        $phase->delete();
    }

    // ── TÂCHES ────────────────────────────────────────────
    public function creerTache(array $data, int $chantierId): Tache
    {
        return DB::transaction(function () use ($data, $chantierId) {
            $tache = Tache::create([
                'nomTache'            => $data['nomTache'],
                'date_debut_prevue'   => $data['date_debut_prevue'],
                'date_fin_prevue'     => $data['date_fin_prevue'],
                'date_debut_reelle'   => null,
                'date_fin_reelle'     => null,
                'avancement'          => 0,
                'statutTache'         => 'en_attente',
                'est_en_retard'       => false,
                'chantier_id'         => $chantierId,
                'phase_id'            => $data['phase_id'],
                'responsable_id'      => $data['responsable_id'] ?? null,
                'tache_precedente_id' => $data['tache_precedente_id'] ?? null,
            ]);

            $this->recalculerAvancementPhase($tache->phase);
            return $tache;
        });
    }

    public function modifierTache(Tache $tache, array $data): Tache
    {
        return DB::transaction(function () use ($tache, $data) {
            $this->verifierTacheModifiable($tache);
            $tache->update([
                'nomTache'            => $data['nomTache'],
                'date_debut_prevue'   => $data['date_debut_prevue'],
                'date_fin_prevue'     => $data['date_fin_prevue'],
                'phase_id'            => $data['phase_id'],
                'responsable_id'      => $data['responsable_id'] ?? null,
                'tache_precedente_id' => $data['tache_precedente_id'] ?? null,
            ]);

            $this->recalculerAvancementPhase($tache->fresh()->phase);
            return $tache->fresh();
        });
    }

    public function supprimerTache(Tache $tache): void
    {
        DB::transaction(function () use ($tache) {
            $this->verifierTacheModifiable($tache);
            $phase = $tache->phase;
            $tache->delete();
            $this->recalculerAvancementPhase($phase);
        });
    }

    public function mettreAJourAvancement(Tache $tache, int $avancement): Tache
    {
        return DB::transaction(function () use ($tache, $avancement) {
            $this->verifierTacheModifiable($tache);
            if ($avancement === 100) {
                $this->validerTache($tache);
            } else {
                $this->mettreAJourProgression($tache, $avancement);
            }
            $this->recalculerAvancementPhase($tache->fresh()->phase);
            return $tache->fresh();
        });
    }

    public function changerStatut(Tache $tache, string $statut): Tache
    {
        return DB::transaction(function () use ($tache, $statut) {
            $this->verifierTacheModifiable($tache);
            if ($statut === 'en_cours') {
                $this->demarrerTache($tache);
            } else {
                $tache->update(['statutTache' => $statut]);
            }
            $this->recalculerAvancementPhase($tache->fresh()->phase);
            return $tache->fresh();
        });
    }

    // ── VÉRIFICATIONS ─────────────────────────────────────
    private function verifierTacheModifiable(Tache $tache): void
    {
        if ($tache->statutTache === 'terminee') {
            throw new \Exception('Impossible de modifier une tâche validée.');
        }
    }

    private function verifierPhaseSupprimable(Phase $phase): void
    {
        if ($phase->taches()->whereIn('statutTache', ['en_cours', 'terminee'])->exists()) {
            throw new \Exception('Impossible de supprimer une phase ayant des tâches en cours ou terminées.');
        }
    }

    // ── ACTIONS PRIVÉES ───────────────────────────────────
    private function validerTache(Tache $tache): void
    {
        $tache->update(['avancement' => 100, 'statutTache' => 'terminee', 'date_fin_reelle' => now()->toDateString()]);
    }

    private function mettreAJourProgression(Tache $tache, int $avancement): void
    {
        $tache->update(['avancement' => $avancement, 'statutTache' => $avancement > 0 ? 'en_cours' : 'en_attente']);
    }

    private function demarrerTache(Tache $tache): void
    {
        $tache->update(['statutTache' => 'en_cours', 'date_debut_reelle' => $tache->date_debut_reelle ?? now()->toDateString()]);
    }

    private function recalculerAvancementPhase(Phase $phase): void
    {
        $taches = $phase->taches;
        if ($taches->isEmpty()) return;

        $avancement  = (int) round($taches->avg('avancement'));
        $statut      = $this->determinerStatutPhase($taches, $avancement);
        $estEnRetard = $statut !== 'terminee' && $phase->date_fin_prevue && $phase->date_fin_prevue->lt(today());

        $phase->update(['statutPhase' => $statut, 'est_en_retard' => $estEnRetard]);
    }

    private function determinerStatutPhase(Collection $taches, int $avancement): string
    {
        if ($avancement === 0) return 'en_attente';
        if ($taches->every(fn($t) => $t->statutTache === 'terminee')) return 'terminee';
        return 'en_cours';
    }
}
