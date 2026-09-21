<?php

namespace App\Services;

use App\Models\Poste;

class PosteService
{
    public function creer(array $data): Poste
    {
        return Poste::create(['libelle' => $data['libelle']]);
    }

    public function modifier(Poste $poste, array $data): Poste
    {
        $poste->update(['libelle' => $data['libelle']]);
        return $poste->fresh();
    }

    public function supprimer(Poste $poste): void
    {
        if ($poste->ouvriers()->exists()) {
            throw new \Exception('Impossible de supprimer ce poste : du personnel actif y est encore rattaché.');
        }

        if ($poste->tauxSalaires()->exists()) {
            throw new \Exception('Impossible de supprimer ce poste : des taux salariaux sont configurés pour lui sur au moins un chantier.');
        }

        $poste->delete();
    }
}
