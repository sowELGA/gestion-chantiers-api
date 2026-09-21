<?php

namespace App\Http\Controllers\Api\DirecteurTravaux;

use App\Http\Controllers\Controller;
use App\Http\Resources\RapportResource;
use App\Models\Chantier;
use App\Models\RapportChantier;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RapportController extends Controller
{
    public function formOptions()
    {
        return response()->json(Chantier::orderBy('nomChantier')->get(['id', 'nomChantier']));
    }

    public function index(Request $request)
    {
        $filtresDate = $request->validate([
            'date_debut' => 'nullable|date',
            'date_fin'   => 'nullable|date|after_or_equal:date_debut',
        ]);

        $query = RapportChantier::with(['auteur', 'chantier'])->orderByDesc('date_rapport');

        if ($request->filled('chantier_id')) {
            $query->where('chantier_id', $request->input('chantier_id'));
        }

        if ($request->input('type', 'tous') !== 'tous') {
            $query->where('type', $request->input('type'));
        }

        if (!empty($filtresDate['date_debut'])) {
            $query->whereDate('date_rapport', '>=', $filtresDate['date_debut']);
        }

        if (!empty($filtresDate['date_fin'])) {
            $query->whereDate('date_rapport', '<=', $filtresDate['date_fin']);
        }

        if ($request->filled('recherche')) {
            $r = $request->input('recherche');
            $query->where(fn($q) => $q->where('titre', 'like', "%{$r}%")->orWhere('contenu', 'like', "%{$r}%"));
        }

        $rapports = $query->paginate(12)->withQueryString();

        $stats = [
            'total'     => RapportChantier::count(),
            'ce_mois'   => RapportChantier::whereMonth('date_rapport', now()->month)->whereYear('date_rapport', now()->year)->count(),
            'incidents' => RapportChantier::where('type', 'incident')->count(),
            'chantiers' => RapportChantier::distinct()->count('chantier_id'),
        ];

        return response()->json([
            'rapports' => RapportResource::collection($rapports),
            'stats' => $stats,
        ]);
    }

    public function show(RapportChantier $rapport)
    {
        return new RapportResource($rapport->load(['auteur', 'chantier']));
    }

    public function telechargerPdf(RapportChantier $rapport)
    {
        $rapport->load(['auteur', 'chantier']);

        $pdf = Pdf::loadView('pdf.rapport-chantier', compact('rapport'))->setPaper('A4', 'portrait');

        $nomFichier = 'rapport-' . Str::slug($rapport->titre ?: 'sans-titre') . '-' . $rapport->id . '.pdf';

        return $pdf->download($nomFichier);
    }
}
