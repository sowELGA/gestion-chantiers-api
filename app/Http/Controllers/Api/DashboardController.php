<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ChantierResource;
use App\Models\Approvisionnement;
use App\Models\Chantier;
use App\Models\DemandeResetMdp;
use App\Models\DepensesChantier;
use App\Models\Ouvrier;
use App\Models\RapportChantier;
use App\Models\RecapHebdomadaire;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $data = [];

        if ($user->hasRole('admin')) {
            $data['admin'] = $this->statsAdmin();
        }

        if ($user->hasRole('directeur_travaux')) {
            $data['directeur_travaux'] = $this->statsDirecteurTravaux();
        }

        if ($user->peutGererApprovisionnements()) {
            $data['daf_approvisionnements'] = $this->statsDafApprovisionnements();
        }

        if ($user->peutGererDepenses()) {
            $data['daf_depenses'] = $this->statsDafDepenses();
        }

        if ($user->hasRole('responsable_rh')) {
            $data['responsable_rh'] = $this->statsResponsableRh();
        }

        return response()->json($data);
    }

    private function statsAdmin(): array
    {
        return [
            'total_users' => User::count(),
            'users_actifs' => User::where('actif', true)->count(),
            'demandes_reset_attente' => DemandeResetMdp::where('statut', 'en_attente')->count(),
        ];
    }

    private function statsDirecteurTravaux(): array
    {
        $chantiers = Chantier::with(['phases', 'chefProjet'])
            ->where('statut', 'en_cours')
            ->get()
            ->map(fn($c) => [
                'chantier' => new ChantierResource($c->load('chefProjet')),
                'avancement' => $c->phases->isEmpty() ? 0 : round($c->phases->avg('avancement')),
                'pct_budget' => $c->budget_prevu > 0 ? round(($c->budget_consomme / $c->budget_prevu) * 100) : 0,
            ]);

        return [
            'chantiers_actifs' => $chantiers->count(),
            'chantiers_en_attente' => Chantier::where('statut', 'en_attente')->count(),
            'chantiers_liste' => $chantiers->values(),
            // Rapports publiés ces 7 derniers jours — pas de vrai système "lu/non lu" en base,
            // ceci alerte le DT sur les rapports récents qu'il n'a pas encore forcément consultés.
            'rapports_recents' => RapportChantier::where('created_at', '>=', now()->subDays(7))->count(),
        ];
    }

    private function statsDafApprovisionnements(): array
    {
        return [
            'en_attente' => Approvisionnement::where('statutAppro', 'en_attente')->count(),
            'urgentes' => Approvisionnement::where('statutAppro', 'en_attente')->where('priorite', 'urgent')->count(),
            'validees_non_commandees' => Approvisionnement::where('statutAppro', 'validee')->count(),
            'en_cours_livraison' => Approvisionnement::whereIn('statutAppro', ['en_cours_livraison', 'partiellement_recue'])->count(),
        ];
    }

    private function statsDafDepenses(): array
    {
        $chantiersEnCours = Chantier::where('statut', 'en_cours')->withSum('depenses', 'montant')->get();

        return [
            'depenses_ce_mois' => (float) DepensesChantier::whereMonth('date_depense', now()->month)->whereYear('date_depense', now()->year)->sum('montant'),
            'chantiers_proche_budget' => $chantiersEnCours->filter(function ($c) {
                if (!$c->budget_prevu) return false;
                return (($c->depenses_sum_montant ?? 0) / $c->budget_prevu) * 100 > 90;
            })->count(),
            'dernieres_depenses' => DepensesChantier::with('chantier')->orderByDesc('date_depense')->take(10)->get()->map(fn($d) => [
                'id' => $d->id,
                'description' => $d->description,
                'categorie' => $d->categorie,
                'montant' => (float) $d->montant,
                'date_depense' => $d->date_depense->format('Y-m-d'),
                'chantier' => ['id' => $d->chantier->id, 'nomChantier' => $d->chantier->nomChantier],
            ]),
        ];
    }

    private function statsResponsableRh(): array
    {
        $ouvriersParChantier = Chantier::whereIn('statut', ['en_cours', 'en_attente'])
            ->withCount(['ouvriers' => fn($q) => $q->where('statutOuvrier', 'actif')])
            ->having('ouvriers_count', '>', 0)
            ->orderByDesc('ouvriers_count')
            ->get(['id', 'nomChantier'])
            ->map(fn($c) => ['chantier' => ['id' => $c->id, 'nomChantier' => $c->nomChantier], 'nb_ouvriers_actifs' => $c->ouvriers_count]);

        return [
            'ouvriers_actifs' => Ouvrier::where('statutOuvrier', 'actif')->count(),
            'ouvriers_inactifs' => Ouvrier::where('statutOuvrier', 'inactif')->count(),
            'fiches_paie_pretes' => RecapHebdomadaire::where('statutRecap', 'validee_cp')->distinct('chantier_id')->count('chantier_id'),
            'ouvriers_par_chantier' => $ouvriersParChantier,
        ];
    }
}
