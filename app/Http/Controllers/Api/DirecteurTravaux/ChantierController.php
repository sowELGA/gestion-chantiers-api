<?php

namespace App\Http\Controllers\Api\DirecteurTravaux;

use App\Http\Controllers\Controller;
use App\Http\Requests\Chantier\AffectationRequest;
use App\Http\Requests\Chantier\ChantierRequest;
use App\Http\Resources\ChantierResource;
use App\Models\Chantier;
use App\Models\User;
use App\Services\ChantierService;
use Illuminate\Http\Request;

class ChantierController extends Controller
{
    public function __construct(private ChantierService $chantierService) {}

    // ── Directeur des travaux ─────────────────────────────────

    public function index(Request $request)
    {
        $statutFiltre = $request->input('statut', 'en_cours'); // 'en_cours' par défaut

        $query = Chantier::with(['chefProjet', 'pointeur']);

        if ($statutFiltre !== 'toutes') {
            $query->where('statut', $statutFiltre);
        }

        if ($request->filled('nomChantier')) {
            $query->where('nomChantier', 'like', '%' . $request->input('nomChantier') . '%');
        }

        $chantiers = $query
            ->orderByRaw("FIELD(statut, 'en_cours', 'en_attente', 'suspendu', 'livre')")
            ->get();

        return response()->json([
            'chantiers' => ChantierResource::collection($chantiers),
            'statutFiltre' => $statutFiltre,
            'stats' => [
                'total'      => Chantier::count(),
                'en_cours'   => Chantier::where('statut', 'en_cours')->count(),
                'en_attente' => Chantier::where('statut', 'en_attente')->count(),
                'livre'      => Chantier::where('statut', 'livre')->count(),
            ],
        ]);
    }

    public function chefsProjetsDisponibles()
    {
        $chefsProjets = User::avecRole('chef_projet')->where('actif', true)->orderBy('nomUser')->get(['id', 'nomUser', 'prenomUser']);

        return response()->json($chefsProjets);
    }

    public function store(ChantierRequest $request)
    {
        $chantier = $this->chantierService->creer($request->validated());

        return response()->json([
            'message'  => 'Chantier créé avec succès.',
            'chantier' => new ChantierResource($chantier->load(['chefProjet', 'pointeur'])),
        ], 201);
    }

    public function show(Chantier $chantier)
    {
        $chantier->load(['phases.taches', 'chefProjet', 'pointeur', 'historiqueChefsProjets.user', 'historiquePointeurs.user']);

        $chefsProjets = User::avecRole('chef_projet')->where('actif', true)->orderBy('nomUser')->get(['id', 'nomUser', 'prenomUser']);
        $pointeurs = User::avecRole('pointeur')->where('actif', true)->doesntHave('chantiersPointes')->orderBy('nomUser')->get(['id', 'nomUser', 'prenomUser']);

        return response()->json([
            'chantier'     => new ChantierResource($chantier),
            'chefsProjets' => $chefsProjets,
            'pointeurs'    => $pointeurs,
        ]);
    }

    public function update(ChantierRequest $request, Chantier $chantier)
    {
        $this->chantierService->modifier($chantier, $request->validated());

        return response()->json([
            'message'  => 'Chantier mis à jour avec succès.',
            'chantier' => new ChantierResource($chantier->fresh(['chefProjet', 'pointeur'])),
        ]);
    }

    public function destroy(Request $request, Chantier $chantier)
    {
        try {
            $this->chantierService->supprimer($chantier, (bool) $request->boolean('confirmer'));
            return response()->json(['message' => 'Chantier supprimé avec succès.']);
        } catch (\Exception $e) {
            if (str_starts_with($e->getMessage(), 'CONFIRMATION_REQUISE:')) {
                return response()->json([
                    'message' => str_replace('CONFIRMATION_REQUISE:', '', $e->getMessage()),
                    'confirmation_requise' => true,
                ], 409);
            }
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function affecterChefProjet(AffectationRequest $request, Chantier $chantier)
    {
        $this->chantierService->affecterChefProjet($chantier, $request->chef_projet_id);

        return response()->json([
            'message'  => 'Chef de projet mis à jour avec succès.',
            'chantier' => new ChantierResource($chantier->fresh(['chefProjet', 'pointeur'])),
        ]);
    }

    public function affecterPointeur(AffectationRequest $request, Chantier $chantier)
    {
        $this->chantierService->affecterPointeur($chantier, $request->pointeur_id);

        return response()->json([
            'message'  => 'Pointeur mis à jour avec succès.',
            'chantier' => new ChantierResource($chantier->fresh(['chefProjet', 'pointeur'])),
        ]);
    }

    public function changerStatut(Request $request, Chantier $chantier, string $statut)
    {
        try {
            $chantier = $this->chantierService->changerStatut($chantier, $statut);
            return response()->json([
                'message'  => 'Statut mis à jour avec succès.',
                'chantier' => new ChantierResource($chantier->load(['chefProjet', 'pointeur'])),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    // ── Chef de Projet (lecture seule sur ses propres chantiers) ──

    public function indexChefProjet(Request $request)
    {
        $statutFiltre = $request->input('statut', 'en_cours');

        $query = Chantier::with('pointeur')->where('chef_projet_id', auth()->id());

        if ($statutFiltre !== 'toutes') {
            $query->where('statut', $statutFiltre);
        }

        $chantiers = $query->orderByRaw("FIELD(statut, 'en_cours', 'en_attente', 'suspendu', 'livre')")->get();

        return response()->json([
            'chantiers' => ChantierResource::collection($chantiers),
            'statutFiltre' => $statutFiltre,
            'stats' => [
                'total'    => Chantier::where('chef_projet_id', auth()->id())->count(),
                'en_cours' => Chantier::where('chef_projet_id', auth()->id())->where('statut', 'en_cours')->count(),
                'livre'    => Chantier::where('chef_projet_id', auth()->id())->where('statut', 'livre')->count(),
            ],
        ]);
    }

    public function showChefProjet(Chantier $chantier)
    {
        abort_if($chantier->chef_projet_id !== auth()->id(), 403);

        $chantier->load('pointeur');

        return response()->json(new ChantierResource($chantier));
    }
}
