<?php

namespace App\Services;

use App\Models\Poste;
use App\Models\TauxSalaire;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TauxSalaireService
{
    public function enregistrerTaux(int $chantierId, array $taux): void
    {
        DB::transaction(function () use ($chantierId, $taux) {
            foreach ($taux as $posteId => $valeurs) {
                $tauxJournalier = $this->valeurOuNull($valeurs['taux_journalier'] ?? null);
                $tauxHeureSup   = $this->valeurOuNull($valeurs['taux_heure_sup'] ?? null);

                if ($tauxJournalier === null && $tauxHeureSup === null) {
                    TauxSalaire::where('chantier_id', $chantierId)->where('poste_id', (int) $posteId)->delete();
                    continue;
                }

                TauxSalaire::updateOrCreate(
                    ['poste_id' => (int) $posteId, 'chantier_id' => $chantierId],
                    ['taux_journalier' => $tauxJournalier ?? 0, 'taux_heure_sup' => $tauxHeureSup ?? 0]
                );
            }
        });
    }

    public function getMatriceTaux(int $chantierId): Collection
    {
        $postes = Poste::orderBy('libelle')->get();
        $tauxExistants = TauxSalaire::where('chantier_id', $chantierId)->get()->keyBy('poste_id');

        return $postes->map(function ($poste) use ($tauxExistants) {
            $taux = $tauxExistants->get($poste->id);
            return [
                'poste_id'        => $poste->id,
                'poste_libelle'   => $poste->libelle,
                'taux_journalier' => $taux->taux_journalier ?? null,
                'taux_heure_sup'  => $taux->taux_heure_sup ?? null,
                'configure'       => $taux !== null,
            ];
        });
    }

    private function valeurOuNull(mixed $valeur): ?float
    {
        if ($valeur === null || $valeur === '') return null;
        return (float) $valeur;
    }
}
