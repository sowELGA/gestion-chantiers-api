<?php

namespace App\Http\Controllers\Api\Pointeur;

use App\Helpers\SemaineHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Pointage\ModifierJourRequest;
use App\Http\Requests\Pointage\PointageRequest;
use App\Models\Chantier;
use App\Models\Pointage;
use App\Services\PointageService;
use App\Services\RecapService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PointageController extends Controller
{
    public function __construct(
        private PointageService $pointageService,
        private RecapService $recapService
    ) {}

    private function chantierDuPointeur(): Chantier
    {
        return Chantier::where('pointeur_id', auth()->id())->firstOrFail();
    }

    public function ficheJour()
    {
        $chantier = $this->chantierDuPointeur();

        if ($chantier->statut !== 'en_cours') {
            return response()->json(['bloque' => true, 'message' => 'Ce chantier n\'est pas en cours.'], 423);
        }

        $donnees = $this->pointageService->getPointagesDuJour($chantier->id);
        $tousPersonnel = $this->pointageService->getToutPersonnel($chantier->id);
        $modifiable = $this->pointageService->semaineModifiable($chantier->id);

        $personnel = $tousPersonnel->map(fn($o) => [
            'id' => $o->id,
            'nomComplet' => $o->nom_complet,
            'poste' => $o->poste->libelle,
            'statutPointage' => $donnees['pointages']->get($o->id)?->statutPointage ?? 'absent',
            'heures_sup' => (int) ($donnees['pointages']->get($o->id)?->heures_sup ?? 0),
        ]);

        return response()->json([
            'chantier' => ['id' => $chantier->id, 'nomChantier' => $chantier->nomChantier],
            'date' => $donnees['date']->toDateString(),
            'personnel' => $personnel,
            'ficheExiste' => $donnees['ficheExiste'],
            'modifiable' => $modifiable,
        ]);
    }

    public function enregistrerFiche(PointageRequest $request)
    {
        $chantier = $this->chantierDuPointeur();

        try {
            $this->pointageService->enregistrerFiche($request->validated()['pointages'], $chantier->id, $this->recapService);
            return response()->json(['message' => 'Fiche du jour enregistrée. Le récap a été mis à jour.']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function recapSemaine(Request $request)
    {
        $chantier = $this->chantierDuPointeur();

        if (!in_array($chantier->statut, ['en_cours', 'suspendu'])) {
            return response()->json(['bloque' => true, 'message' => 'Ce chantier n\'est pas actif.'], 423);
        }

        $today = Carbon::today();
        $semaine = (int) $request->input('semaine', SemaineHelper::numeroCycle($today));
        $annee = (int) $request->input('annee', SemaineHelper::anneeCycle($today));
        $page = (int) $request->input('page', 1);

        $infos = $this->recapService->getInfosSemaine($semaine, $annee);
        $statut = $this->recapService->getStatutSemaine($chantier->id, $semaine, $annee);
        $recap = $this->recapService->getRecapComplet($chantier->id, $semaine, $annee, $page);
        $semaines = $this->recapService->getSemainesDisponibles(5);

        $modifiable = $statut['statut'] === 'rejetee';
        $soumettable = in_array($statut['statut'], ['en_attente', 'rejetee']) && $chantier->statut === 'en_cours';

        $lignesSansSalaire = collect($recap['lignes'])->map(function ($ligne) {
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
            'pagination' => $recap['pagination'],
            'statut' => $statut['statut'],
            'motif_rejet' => $statut['motif_rejet'],
            'totaux' => [
                'totaux_par_jour' => $recap['totaux_par_jour'],
            ],
            'modifiable' => $modifiable,
            'soumettable' => $soumettable,
            'semaines' => $semaines,
        ]);
    }

    public function soumettreSemaine(Request $request)
    {
        $chantier = $this->chantierDuPointeur();

        $today = Carbon::today();
        $semaine = (int) $request->input('semaine', SemaineHelper::numeroCycle($today));
        $annee = (int) $request->input('annee', SemaineHelper::anneeCycle($today));

        $this->recapService->soumettreSemaine($chantier->id, auth()->id(), $semaine, $annee);

        return response()->json(['message' => 'Fiche soumise au chef de projet.']);
    }

    public function modifierJour(string $date)
    {
        $chantier = $this->chantierDuPointeur();

        $dateCarbon = Carbon::parse($date);
        $semaine = SemaineHelper::numeroCycle($dateCarbon);
        $annee = SemaineHelper::anneeCycle($dateCarbon);
        $statut = $this->recapService->getStatutSemaine($chantier->id, $semaine, $annee);

        if ($statut['statut'] !== 'rejetee') {
            return response()->json(['message' => "Cette fiche n'est pas modifiable."], 422);
        }

        $tousPersonnel = $this->pointageService->getToutPersonnel($chantier->id);
        $pointagesJour = Pointage::where('chantier_id', $chantier->id)->whereDate('date', $date)->get()->keyBy('ouvrier_id');

        $personnel = $tousPersonnel->map(fn($o) => [
            'id' => $o->id,
            'nomComplet' => $o->nom_complet,
            'poste' => $o->poste->libelle,
            'statutPointage' => $pointagesJour->get($o->id)?->statutPointage ?? 'absent',
            'heures_sup' => (int) ($pointagesJour->get($o->id)?->heures_sup ?? 0),
        ]);

        return response()->json([
            'chantier' => ['id' => $chantier->id, 'nomChantier' => $chantier->nomChantier],
            'date' => $dateCarbon->toDateString(),
            'personnel' => $personnel,
            'motif_rejet' => $statut['motif_rejet'],
            'semaine' => $semaine,
            'annee' => $annee,
        ]);
    }

    public function enregistrerModificationJour(ModifierJourRequest $request)
    {
        $chantier = $this->chantierDuPointeur();

        try {
            $this->pointageService->modifierPointageJour($chantier->id, $request->date, $request->validated()['pointages'], $this->recapService);
            return response()->json(['message' => 'Pointage mis à jour avec succès.']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
