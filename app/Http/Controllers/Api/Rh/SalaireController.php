<?php

namespace App\Http\Controllers\Api\Rh;

use App\Helpers\PointageHelper;
use App\Helpers\SemaineHelper;
use App\Http\Controllers\Controller;
use App\Models\Chantier;
use App\Models\RecapHebdomadaire;
use App\Services\PdfService;
use App\Services\RecapService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SalaireController extends Controller
{
    public function __construct(
        private RecapService $recapService,
        private PdfService $pdfService
    ) {}

    public function index(Request $request)
    {
        $semaine = (int) $request->input('semaine', SemaineHelper::numeroCycle(Carbon::today()));
        $annee = (int) $request->input('annee', SemaineHelper::anneeCycle(Carbon::today()));

        $chantiers = Chantier::with([
            'recapsHebdomadaires' => fn($q) => $q
                ->where('semaine', $semaine)->where('annee', $annee)
                ->whereIn('statutRecap', ['validee_cp', 'envoyee_direction'])
                ->with('ouvrier'),
        ])
            ->whereHas('recapsHebdomadaires', fn($q) => $q
                ->where('semaine', $semaine)->where('annee', $annee)
                ->whereIn('statutRecap', ['validee_cp', 'envoyee_direction']))
            ->get()
            ->map(function ($chantier) use ($semaine, $annee) {
                $recaps = $chantier->recapsHebdomadaires;
                $pointagesSemaine = PointageHelper::pointagesSemaine($chantier->id, $semaine, $annee);

                $totalSalaires = $recaps->sum(function ($recap) use ($pointagesSemaine) {
                    $pointagesOuvrier = $pointagesSemaine->get($recap->ouvrier_id, collect());
                    return PointageHelper::calculerSalaireDepuisPointages($pointagesOuvrier)['salaire_total'];
                });

                return [
                    'chantier' => ['id' => $chantier->id, 'nomChantier' => $chantier->nomChantier],
                    'nb_ouvriers' => $recaps->count(),
                    'total_salaires' => $totalSalaires,
                    'statut' => $recaps->first()?->statutRecap ?? 'validee_cp',
                ];
            })->values();

        return response()->json([
            'chantiers' => $chantiers,
            'semaine' => $semaine,
            'annee' => $annee,
            'semaines' => $this->recapService->getSemainesDisponibles(10),
        ]);
    }

    public function apercu(Request $request, Chantier $chantier)
    {
        $semaine = (int) $request->input('semaine', SemaineHelper::numeroCycle(Carbon::today()));
        $annee = (int) $request->input('annee', SemaineHelper::anneeCycle(Carbon::today()));

        $statut = RecapHebdomadaire::where('chantier_id', $chantier->id)->where('semaine', $semaine)->where('annee', $annee)->value('statutRecap');

        if ($statut === 'validee_cp') {
            $this->recapService->calculerSalaires($chantier->id, $semaine, $annee);
            $statut = 'envoyee_direction';
        }

        $samedi = SemaineHelper::debutDepuisNumero($semaine, $annee);
        $vendredi = SemaineHelper::finDepuisNumero($semaine, $annee);
        $pointagesSemaine = PointageHelper::pointagesSemaine($chantier->id, $semaine, $annee);

        $tousRecaps = $tousRecaps = RecapHebdomadaire::with(['ouvrier' => fn($q) => $q->withTrashed()->with('poste')])
            ->where('chantier_id', $chantier->id)->where('semaine', $semaine)->where('annee', $annee)
            ->whereIn('statutRecap', ['validee_cp', 'envoyee_direction'])
            ->whereHas('ouvrier', fn($q) => $q->withTrashed())
            ->get()
            ->map(function ($recap) use ($pointagesSemaine, $samedi) {
                $pointagesOuvrier = $pointagesSemaine->get($recap->ouvrier_id, collect())->keyBy(fn($p) => Carbon::parse($p->date)->toDateString());
                $salaire = PointageHelper::calculerSalaireDepuisPointages($pointagesOuvrier->values());
                $poste = PointageHelper::posteDepuisPointages($recap->ouvrier, $pointagesOuvrier);

                return [
                    'ouvrier' => ['id' => $recap->ouvrier->id, 'nomComplet' => $recap->ouvrier->nom_complet],
                    'poste' => $poste?->libelle,
                    'jours' => collect(range(0, 6))->map(function ($i) use ($samedi, $pointagesOuvrier) {
                        $date = $samedi->copy()->addDays($i);
                        $p = $pointagesOuvrier->get($date->toDateString());
                        return ['date' => $date->toDateString(), 'statut' => $p?->statutPointage, 'h_sup' => (int) ($p?->heures_sup ?? 0)];
                    }),
                    'jours_presents' => $salaire['jours_presents'],
                    'total_heures_sup' => $salaire['total_heures_sup'],
                    'salaire_base' => $salaire['salaire_base'],
                    'salaire_heures_sup' => $salaire['salaire_heures_sup'],
                    'salaire_total' => $salaire['salaire_total'],
                    '_famille' => $this->familleMetier($poste?->libelle),
                    '_ordre' => $this->ordreMetier($poste?->libelle),
                ];
            });

        $groupes = $tousRecaps->groupBy('_famille')->map(fn($lignes) => $lignes->sortBy('_ordre')->values())->sortKeys();

        return response()->json([
            'chantier' => ['id' => $chantier->id, 'nomChantier' => $chantier->nomChantier, 'localisation' => $chantier->localisation],
            'semaine' => $semaine,
            'annee' => $annee,
            'debut' => $samedi->toDateString(),
            'fin' => $vendredi->toDateString(),
            'jours' => collect(range(0, 6))->map(fn($i) => $samedi->copy()->addDays($i)->toDateString()),
            'groupes' => $groupes->map(fn($lignes, $famille) => ['famille' => $famille, 'lignes' => $lignes->map(fn($l) => collect($l)->except(['_famille', '_ordre']))]),
            'nb_ouvriers' => $tousRecaps->count(),
            'total_general' => $tousRecaps->sum('salaire_total'),
            'total_presents' => $tousRecaps->sum('jours_presents'),
            'total_h_sup' => $tousRecaps->sum('total_heures_sup'),
            'statut' => $statut,
        ]);
    }

    public function genererPdf(Request $request, Chantier $chantier)
    {
        $semaine = (int) $request->input('semaine', SemaineHelper::numeroCycle(Carbon::today()));
        $annee = (int) $request->input('annee', SemaineHelper::anneeCycle(Carbon::today()));

        $existe = RecapHebdomadaire::where('chantier_id', $chantier->id)->where('semaine', $semaine)->where('annee', $annee)
            ->where('statutRecap', 'envoyee_direction')->exists();

        if (!$existe) {
            return response()->json(['message' => "La fiche de paie n'est pas disponible."], 422);
        }

        return $this->pdfService->genererFichePaie($chantier->id, $semaine, $annee);
    }

    private function familleMetier(?string $libellePoste): string
    {
        $poste = strtolower($libellePoste ?? '');
        $famille = preg_replace('/^(chef|aide|sous[\s-]chef|premier)\s+/i', '', $poste);
        return ucwords(trim($famille)) ?: 'Autre';
    }

    private function ordreMetier(?string $libellePoste): int
    {
        $poste = strtolower($libellePoste ?? '');
        if (str_starts_with($poste, 'chef')) return 0;
        if (str_starts_with($poste, 'aide')) return 2;
        return 1;
    }
}
