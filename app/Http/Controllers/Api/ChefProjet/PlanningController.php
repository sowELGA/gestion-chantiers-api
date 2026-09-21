<?php

namespace App\Http\Controllers\Api\ChefProjet;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tache\PhaseRequest;
use App\Http\Requests\Tache\TacheRequest;
use App\Http\Resources\PhaseResource;
use App\Http\Resources\TacheResource;
use App\Models\Chantier;
use App\Models\Phase;
use App\Models\Tache;
use App\Services\TacheService;
use Illuminate\Http\Request;

class PlanningController extends Controller
{
    public function __construct(private TacheService $tacheService) {}

    // ── PHASES ────────────────────────────────────────────
    public function indexPhases(Request $request, Chantier $chantier)
    {
        abort_if($chantier->chef_projet_id !== auth()->id(), 403);

        $statutFiltre = $request->input('statut', 'actives');

        $phases = Phase::with('taches')->where('chantier_id', $chantier->id)->orderBy('ordre')->get();

        $phasesAffichees = match ($statutFiltre) {
            'toutes'     => $phases,
            'en_attente' => $phases->where('statutPhase', 'en_attente')->values(),
            'en_cours'   => $phases->where('statutPhase', 'en_cours')->values(),
            'terminee'   => $phases->where('statutPhase', 'terminee')->values(),
            default      => $phases->whereIn('statutPhase', ['en_attente', 'en_cours'])->values(),
        };

        return response()->json([
            'phases' => PhaseResource::collection($phasesAffichees),
            'statutFiltre' => $statutFiltre,
        ]);
    }

    public function prochainOrdre(Chantier $chantier)
    {
        abort_if($chantier->chef_projet_id !== auth()->id(), 403);
        return response()->json(['prochain_ordre' => Phase::where('chantier_id', $chantier->id)->max('ordre') + 1]);
    }

    public function storePhase(PhaseRequest $request, Chantier $chantier)
    {
        abort_if($chantier->chef_projet_id !== auth()->id(), 403);
        $this->verifierChantierModifiable($chantier);

        $phase = $this->tacheService->creerPhase($request->validated(), $chantier->id);

        return response()->json(['message' => 'Phase créée avec succès.', 'phase' => new PhaseResource($phase)], 201);
    }

    public function updatePhase(PhaseRequest $request, Chantier $chantier, Phase $phase)
    {
        abort_if($chantier->chef_projet_id !== auth()->id(), 403);
        abort_if($phase->chantier_id !== $chantier->id, 404);
        $this->verifierChantierModifiable($chantier);

        try {
            $phase = $this->tacheService->modifierPhase($phase, $request->validated());
            return response()->json(['message' => 'Phase modifiée avec succès.', 'phase' => new PhaseResource($phase)]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function destroyPhase(Chantier $chantier, Phase $phase)
    {
        abort_if($chantier->chef_projet_id !== auth()->id(), 403);
        abort_if($phase->chantier_id !== $chantier->id, 404);
        $this->verifierChantierModifiable($chantier);

        try {
            $this->tacheService->supprimerPhase($phase);
            return response()->json(['message' => 'Phase supprimée.']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    // ── TÂCHES ────────────────────────────────────────────
    public function indexTaches(Chantier $chantier, Phase $phase)
    {
        abort_if($chantier->chef_projet_id !== auth()->id(), 403);
        abort_if($phase->chantier_id !== $chantier->id, 404);

        $taches = Tache::with('tachePrecedente')->where('phase_id', $phase->id)->orderBy('date_debut_prevue')->get();

        return response()->json([
            'phase' => new PhaseResource($phase),
            'taches' => TacheResource::collection($taches),
        ]);
    }

    public function tachesDisponibles(Chantier $chantier, Phase $phase)
    {
        abort_if($chantier->chef_projet_id !== auth()->id(), 403);
        $taches = Tache::where('phase_id', $phase->id)->get(['id', 'nomTache']);
        return response()->json($taches);
    }

    public function storeTache(TacheRequest $request, Chantier $chantier, Phase $phase)
    {
        abort_if($chantier->chef_projet_id !== auth()->id(), 403);
        abort_if($phase->chantier_id !== $chantier->id, 404);
        $this->verifierChantierModifiable($chantier);

        $data = $request->validated();
        $data['responsable_id'] = auth()->id();
        $data['phase_id'] = $phase->id;

        $tache = $this->tacheService->creerTache($data, $chantier->id);

        return response()->json(['message' => 'Tâche créée avec succès.', 'tache' => new TacheResource($tache)], 201);
    }

    public function updateTache(TacheRequest $request, Chantier $chantier, Phase $phase, Tache $tache)
    {
        abort_if($chantier->chef_projet_id !== auth()->id(), 403);
        abort_if($phase->chantier_id !== $chantier->id, 404);
        abort_if($tache->phase_id !== $phase->id, 404);
        $this->verifierChantierModifiable($chantier);

        if ($tache->statutTache === 'terminee') {
            return response()->json(['message' => 'Impossible de modifier une tâche terminée.'], 422);
        }

        $data = $request->validated();
        $data['responsable_id'] = auth()->id();
        $data['phase_id'] = $phase->id;

        try {
            $tache = $this->tacheService->modifierTache($tache, $data);
            return response()->json(['message' => 'Tâche modifiée.', 'tache' => new TacheResource($tache)]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function destroyTache(Chantier $chantier, Phase $phase, Tache $tache)
    {
        abort_if($chantier->chef_projet_id !== auth()->id(), 403);
        abort_if($phase->chantier_id !== $chantier->id, 404);
        abort_if($tache->phase_id !== $phase->id, 404);
        $this->verifierChantierModifiable($chantier);

        try {
            $this->tacheService->supprimerTache($tache);
            return response()->json(['message' => 'Tâche supprimée.']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function mettreAJourAvancement(Request $request, Chantier $chantier, Phase $phase, Tache $tache)
    {
        abort_if($chantier->chef_projet_id !== auth()->id(), 403);
        abort_if($phase->chantier_id !== $chantier->id, 404);
        abort_if($tache->phase_id !== $phase->id, 404);
        $this->verifierChantierModifiable($chantier);

        $request->validate(['avancement' => 'required|integer|min:0|max:100']);

        try {
            $tache = $this->tacheService->mettreAJourAvancement($tache, (int) $request->input('avancement'));
            return response()->json(['message' => 'Avancement mis à jour.', 'tache' => new TacheResource($tache)]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    // ── HELPER ────────────────────────────────────────────
    private function verifierChantierModifiable(Chantier $chantier): void
    {
        if ($chantier->statut === 'livre') {
            abort(403, 'Ce chantier est livré — la planification est verrouillée.');
        }
    }
}
