<?php

namespace App\Http\Controllers\Api\ChefProjet;

use App\Helpers\SemaineHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Pointage\RejetRecapRequest;
use App\Models\Chantier;
use App\Services\RecapService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ValidationController extends Controller
{
    public function __construct(private RecapService $recapService) {}

    public function index(Request $request, Chantier $chantier)
    {
        abort_if($chantier->chef_projet_id !== auth()->id(), 403);

        $today = Carbon::today();
        $semaine = (int) $request->input('semaine', SemaineHelper::numeroCycle($today));
        $annee = (int) $request->input('annee', SemaineHelper::anneeCycle($today));
        $page = (int) $request->input('page', 1);

        $infos = $this->recapService->getInfosSemaine($semaine, $annee);
        $statut = $this->recapService->getStatutSemaine($chantier->id, $semaine, $annee);
        $donnees = $this->recapService->getLignesRecap($chantier->id, $semaine, $annee, $page);
        $totaux = $this->recapService->getTotauxSemaine($chantier->id, $semaine, $annee);
        $semaines = $this->recapService->getSemainesDisponibles(5);

        $lignesSansSalaire = collect($donnees['lignes'])->map(function ($ligne) {
            unset($ligne['salaire_base'], $ligne['salaire_h_sup'], $ligne['salaire_total']);
            return $ligne;
        });

        return response()->json([
            'chantier' => ['id' => $chantier->id, 'nomChantier' => $chantier->nomChantier],
            'semaine' => $infos['semaine'],
            'annee' => $infos['annee'],
            'debut' => $infos['debut']->toDateString(),
            'fin' => $infos['fin']->toDateString(),
            'jours' => collect($infos['jours'])->map(fn($j) => $j->toDateString()),
            'lignes' => $lignesSansSalaire,
            'pagination' => $donnees['pagination'],
            'statut' => $statut['statut'],
            'motif_rejet' => $statut['motif_rejet'],
            'totaux' => [
                'totaux_par_jour' => $totaux['totaux_par_jour'],
            ],
            'peutValider' => $statut['statut'] === 'soumise',
            'semaines' => $semaines,
        ]);
    }

    public function valider(Request $request, Chantier $chantier)
    {
        abort_if($chantier->chef_projet_id !== auth()->id(), 403);

        $today = Carbon::today();
        $semaine = (int) $request->input('semaine', SemaineHelper::numeroCycle($today));
        $annee = (int) $request->input('annee', SemaineHelper::anneeCycle($today));

        $this->recapService->validerSemaine($chantier->id, $semaine, $annee, auth()->id());

        return response()->json(['message' => 'Fiche validée et transmise à la direction.']);
    }

    public function rejeter(RejetRecapRequest $request, Chantier $chantier)
    {
        abort_if($chantier->chef_projet_id !== auth()->id(), 403);

        $today = Carbon::today();
        $semaine = (int) $request->input('semaine', SemaineHelper::numeroCycle($today));
        $annee = (int) $request->input('annee', SemaineHelper::anneeCycle($today));

        $this->recapService->rejeterSemaine($chantier->id, $semaine, $annee, auth()->id(), $request->motif_rejet);

        return response()->json(['message' => 'Fiche rejetée. Le pointeur peut corriger.']);
    }
}
