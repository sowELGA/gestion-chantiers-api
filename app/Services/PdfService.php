<?php

namespace App\Services;

use App\Helpers\PointageHelper;
use App\Helpers\SemaineHelper;
use App\Models\Chantier;
use App\Models\RecapHebdomadaire;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class PdfService
{
    private function regrouperParCorpsMetier(Collection $recaps): Collection
    {
        return $recaps
            ->groupBy(function ($recap) {
                $poste = strtolower($recap->poste->libelle ?? '');
                $famille = preg_replace('/^(chef|aide|sous[\s-]chef|premier)\s+/i', '', $poste);
                return ucwords(trim($famille));
            })
            ->map(fn($lignes) => $lignes->sortBy(function ($recap) {
                $poste = strtolower($recap->poste->libelle ?? '');
                if (str_starts_with($poste, 'chef')) return 0;
                if (str_starts_with($poste, 'aide')) return 2;
                return 1;
            })->values())
            ->sortKeys();
    }

    public function genererFichePaie(int $chantierId, int $semaine, int $annee)
    {
        $chantier = Chantier::findOrFail($chantierId);
        $samedi = SemaineHelper::debutDepuisNumero($semaine, $annee);
        $vendredi = SemaineHelper::finDepuisNumero($semaine, $annee);

        $pointagesSemaine = PointageHelper::pointagesSemaine($chantierId, $semaine, $annee);

        $tousRecaps = RecapHebdomadaire::with(['ouvrier.poste', 'soumisParUser', 'valideParUser'])
            ->where('chantier_id', $chantierId)->where('semaine', $semaine)->where('annee', $annee)
            ->where('statutRecap', 'envoyee_direction')
            ->get()
            ->map(function ($recap) use ($pointagesSemaine) {
                $pointagesOuvrier = $pointagesSemaine->get($recap->ouvrier_id, collect())->keyBy(fn($p) => Carbon::parse($p->date)->toDateString());
                $salaire = PointageHelper::calculerSalaireDepuisPointages($pointagesOuvrier->values());

                $recap->poste = PointageHelper::posteDepuisPointages($recap->ouvrier, $pointagesOuvrier);
                $recap->jours_presents = $salaire['jours_presents'];
                $recap->total_heures_sup = $salaire['total_heures_sup'];
                $recap->salaire_base = $salaire['salaire_base'];
                $recap->salaire_heures_sup = $salaire['salaire_heures_sup'];
                $recap->salaire_total = $salaire['salaire_total'];
                $recap->pointagesParJour = $pointagesOuvrier;

                return $recap;
            })
            ->filter(fn($recap) => $recap->salaire_total > 0)
            ->values();

        $groupes = $this->regrouperParCorpsMetier($tousRecaps);
        $totalGeneral = $tousRecaps->sum('salaire_total');
        $debutSemaine = $samedi->locale('fr')->isoFormat('D MMMM YYYY');
        $finSemaine = $vendredi->locale('fr')->isoFormat('D MMMM YYYY');

        $pdf = Pdf::loadView('pdf.fiche-paie', compact('chantier', 'groupes', 'semaine', 'annee', 'samedi', 'vendredi', 'debutSemaine', 'finSemaine', 'totalGeneral'))
            ->setPaper('A4', 'landscape');

        $nomFichier = 'fiche-paie-' . str($chantier->nomChantier)->slug() . '-S' . $semaine . '-' . $annee . '.pdf';

        return $pdf->download($nomFichier);
    }
}
