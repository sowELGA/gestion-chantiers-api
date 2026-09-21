<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TacheResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'nomTache'            => $this->nomTache,
            'date_debut_prevue'   => $this->date_debut_prevue?->format('Y-m-d'),
            'date_fin_prevue'     => $this->date_fin_prevue?->format('Y-m-d'),
            'date_debut_reelle'   => $this->date_debut_reelle?->format('Y-m-d'),
            'date_fin_reelle'     => $this->date_fin_reelle?->format('Y-m-d'),
            'avancement'          => $this->avancement,
            'statutTache'         => $this->statutTache,
            'est_en_retard'       => $this->est_en_retard,
            'phase_id'            => $this->phase_id,
            'tache_precedente'    => $this->whenLoaded('tachePrecedente', fn() => $this->tachePrecedente ? [
                'id' => $this->tachePrecedente->id,
                'nomTache' => $this->tachePrecedente->nomTache,
            ] : null),
        ];
    }
}
