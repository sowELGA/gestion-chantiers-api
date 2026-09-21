<?php

namespace App\Http\Controllers\Api\ChefProjet;

use App\Http\Controllers\Controller;
use App\Http\Requests\Rapport\RapportRequest;
use App\Http\Resources\RapportResource;
use App\Models\Chantier;
use App\Models\RapportChantier;
use Illuminate\Http\Request;

class RapportController extends Controller
{
    public function formOptions()
    {
        $chantiers = Chantier::where('chef_projet_id', auth()->id())
            ->whereIn('statut', ['en_cours', 'suspendu'])
            ->orderBy('nomChantier')
            ->get(['id', 'nomChantier']);

        return response()->json($chantiers);
    }

    public function index(Request $request)
    {
        $query = RapportChantier::with(['auteur', 'chantier'])
            ->where('auteur_id', auth()->id())
            ->orderByDesc('date_rapport');

        if ($request->filled('chantier_id')) {
            $query->where('chantier_id', $request->input('chantier_id'));
        }

        if ($request->input('type', 'tous') !== 'tous') {
            $query->where('type', $request->input('type'));
        }

        $rapports = $query->paginate(10)->withQueryString();

        return RapportResource::collection($rapports);
    }

    public function show(RapportChantier $rapport)
    {
        abort_if($rapport->auteur_id !== auth()->id(), 403);
        return new RapportResource($rapport->load(['auteur', 'chantier']));
    }

    public function store(RapportRequest $request)
    {
        $rapport = RapportChantier::create(array_merge($request->validated(), ['auteur_id' => auth()->id()]));

        return response()->json(['message' => 'Rapport ajouté avec succès.', 'rapport' => new RapportResource($rapport->load(['auteur', 'chantier']))], 201);
    }

    public function update(RapportRequest $request, RapportChantier $rapport)
    {
        abort_if($rapport->auteur_id !== auth()->id(), 403);

        $rapport->update($request->validated());

        return response()->json(['message' => 'Rapport modifié avec succès.', 'rapport' => new RapportResource($rapport->fresh(['auteur', 'chantier']))]);
    }

    public function destroy(RapportChantier $rapport)
    {
        abort_if($rapport->auteur_id !== auth()->id(), 403);

        $rapport->delete();

        return response()->json(['message' => 'Rapport supprimé.']);
    }
}
