<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BonReceptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'quantite_recue' => (float) $this->quantite_recue,
            'date_reception' => $this->date_reception?->format('Y-m-d'),
            'observation'    => $this->observation,
            'demande' => $this->whenLoaded('demande', fn() => [
                'id' => $this->demande->id,
                'designation' => $this->demande->designation,
                'unite' => $this->demande->unite,
                'quantite_demandee' => (float) $this->demande->quantite_demandee,
                'quantite_restante' => $this->demande->quantite_restante,
            ]),
            'receptionnee_par' => $this->whenLoaded('receptionneePar', fn() => [
                'nomComplet' => $this->receptionneePar->nom_complet,
            ]),
        ];
    }
}
