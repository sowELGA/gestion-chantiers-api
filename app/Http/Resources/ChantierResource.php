<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChantierResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'nomChantier'         => $this->nomChantier,
            'localisation'        => $this->localisation,
            'budget_prevu'        => $this->budget_prevu !== null ? (float) $this->budget_prevu : null,
            'budget_consomme'     => $this->budget_consomme,
            'budget_restant'      => $this->budget_restant,
            'pourcentage_budget'  => $this->pourcentage_budget,
            'avancement_global'   => $this->avancement_global,
            'est_en_retard'       => $this->est_en_retard,
            'date_debut'          => $this->date_debut?->format('Y-m-d'),
            'date_fin_prevue'     => $this->date_fin_prevue?->format('Y-m-d'),
            'date_fin_reelle'     => $this->date_fin_reelle?->format('Y-m-d'),
            'statut'              => $this->statut,
            'transitions_disponibles' => $this->transitionsDisponibles(),
            'chef_projet'         => $this->whenLoaded('chefProjet', fn() => $this->chefProjet ? [
                'id' => $this->chefProjet->id,
                'nomComplet' => $this->chefProjet->nom_complet,
                'email' => $this->chefProjet->email,
            ] : null),
            'pointeur'            => $this->whenLoaded('pointeur', fn() => $this->pointeur ? [
                'id' => $this->pointeur->id,
                'nomComplet' => $this->pointeur->nom_complet,
                'email' => $this->pointeur->email,
            ] : null),
            'created_at'          => $this->created_at,
            'historique_chefs_projets' => $this->whenLoaded('historiqueChefsProjets', fn() => $this->historiqueChefsProjets->map(fn($a) => [
                'id' => $a->id,
                'debut_affectation' => $a->debut_affectation?->format('Y-m-d'),
                'fin_affectation' => $a->fin_affectation?->format('Y-m-d'),
                'user' => ['nomComplet' => $a->user->nom_complet],
            ])),
            'historique_pointeurs' => $this->whenLoaded('historiquePointeurs', fn() => $this->historiquePointeurs->map(fn($a) => [
                'id' => $a->id,
                'debut_affectation' => $a->debut_affectation?->format('Y-m-d'),
                'fin_affectation' => $a->fin_affectation?->format('Y-m-d'),
                'user' => ['nomComplet' => $a->user->nom_complet],
            ])),
            'phases' => PhaseResource::collection($this->whenLoaded('phases')),
        ];
    }
}
