<?php

namespace App\Services;

use App\Models\Chantier;
use App\Models\UserChantier;
use Illuminate\Support\Facades\DB;

class ChantierService
{
    public function creer(array $data): Chantier
    {
        $chantier = Chantier::create([
            'nomChantier'     => $data['nomChantier'],
            'localisation'    => $data['localisation'],
            'budget_prevu'    => $data['budget_prevu'] ?? null,
            'date_debut'      => $data['date_debut'],
            'date_fin_prevue' => $data['date_fin_prevue'],
            'statut'          => 'en_attente',
            'chef_projet_id'  => null,
            'pointeur_id'     => null,
        ]);

        if (!empty($data['chef_projet_id'])) {
            $this->affecterChefProjet($chantier, $data['chef_projet_id']);
        }

        return $chantier->fresh();
    }

    public function modifier(Chantier $chantier, array $data): Chantier
    {
        $chantier->update([
            'nomChantier'     => $data['nomChantier'],
            'localisation'    => $data['localisation'],
            'budget_prevu'    => $data['budget_prevu'] ?? null,
            'date_debut'      => $data['date_debut'],
            'date_fin_prevue' => $data['date_fin_prevue'],
        ]);

        return $chantier->fresh();
    }

    public function supprimer(Chantier $chantier, bool $confirme = false): void
    {
        $this->verifierSupprimable($chantier);

        // Confirmations requises (l'utilisateur doit valider une fois)
        if (!$confirme) {
            $aDesPhases = $chantier->phases()->exists();
            $aDesOuvriers = $chantier->ouvriers()->exists();

            if ($aDesPhases) {
                throw new \Exception('CONFIRMATION_REQUISE:Ce chantier contient des phases (sans avancement). Confirmez-vous la suppression ?');
            }

            if ($aDesOuvriers) {
                throw new \Exception('CONFIRMATION_REQUISE:Ce chantier a des ouvriers affectés. Confirmez-vous la suppression ?');
            }
        }

        $chantier->delete();
    }

    public function affecterChefProjet(Chantier $chantier, ?int $chefProjetId): void
    {
        DB::transaction(function () use ($chantier, $chefProjetId) {
            $this->cloturerAffectationEnCours($chantier, 'chef_projet');

            if ($chefProjetId) {
                UserChantier::create([
                    'chantier_id'       => $chantier->id,
                    'user_id'           => $chefProjetId,
                    'debut_affectation' => now()->toDateString(),
                    'fin_affectation'   => null,
                ]);
            }

            $chantier->update(['chef_projet_id' => $chefProjetId]);
        });
    }

    public function affecterPointeur(Chantier $chantier, ?int $pointeurId): void
    {
        DB::transaction(function () use ($chantier, $pointeurId) {
            $this->cloturerAffectationEnCours($chantier, 'pointeur');

            if ($pointeurId) {
                UserChantier::create([
                    'chantier_id'       => $chantier->id,
                    'user_id'           => $pointeurId,
                    'debut_affectation' => now()->toDateString(),
                    'fin_affectation'   => null,
                ]);
            }

            $chantier->update(['pointeur_id' => $pointeurId]);
        });
    }

    public function changerStatut(Chantier $chantier, string $nouveauStatut): Chantier
    {
        $this->verifierTransitionAutorisee($chantier->statut, $nouveauStatut);

        $chantier->update(['statut' => $nouveauStatut]);

        if ($nouveauStatut === 'livre') {
            $chantier->update(['date_fin_reelle' => now()->toDateString()]);
        }

        return $chantier->fresh();
    }

    private function verifierTransitionAutorisee(string $statutActuel, string $nouveauStatut): void
    {
        $transitions = Chantier::TRANSITIONS[$statutActuel] ?? [];

        if (!array_key_exists($nouveauStatut, $transitions)) {
            throw new \Exception("Transition invalide : '{$statutActuel}' → '{$nouveauStatut}'.");
        }
    }

    private function verifierSupprimable(Chantier $chantier): void
    {
        if ($chantier->statut !== 'en_attente') {
            throw new \Exception("Impossible de supprimer un chantier qui n'est plus en attente.");
        }

        if ($chantier->depenses()->exists()) {
            throw new \Exception('Impossible de supprimer un chantier ayant des dépenses enregistrées.');
        }

        $phases = $chantier->phases()->with('taches')->get();
        $phaseAvancee = $phases->contains(fn($p) => $p->avancement > 0);

        if ($phaseAvancee) {
            throw new \Exception('Impossible de supprimer ce chantier : au moins une phase a un avancement en cours.');
        }
    }

    // ⚠️ Adapté : whereHas('user.roles', ...) au lieu de whereHas('user', where('role', ...))
    private function cloturerAffectationEnCours(Chantier $chantier, string $roleNom): void
    {
        $chantier->affectations()
            ->whereHas('user.roles', fn($q) => $q->where('nom', $roleNom))
            ->whereNull('fin_affectation')
            ->update(['fin_affectation' => now()->toDateString()]);
    }
}
