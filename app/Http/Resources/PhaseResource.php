<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PhaseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'nomPhase'        => $this->nomPhase,
            'ordre'           => $this->ordre,
            'typePhase'       => $this->typePhase,
            'sous_traitant'   => $this->sous_traitant,
            'date_debut'      => $this->date_debut?->format('Y-m-d'),
            'date_fin_prevue' => $this->date_fin_prevue?->format('Y-m-d'),
            'statutPhase'     => $this->statutPhase,
            'avancement'      => $this->avancement,
            'est_en_retard'   => $this->est_en_retard,
            'taches'          => TacheResource::collection($this->whenLoaded('taches')),
        ];
    }
}
