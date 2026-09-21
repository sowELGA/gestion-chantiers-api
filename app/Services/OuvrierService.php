<?php

namespace App\Services;

use App\Models\Ouvrier;

class OuvrierService
{
    public function creer(array $data): Ouvrier
    {
        return Ouvrier::create([
            'nomOuvrier'    => $data['nomOuvrier'],
            'prenomOuvrier' => $data['prenomOuvrier'],
            'telOuvrier'    => $data['telOuvrier'],
            'statutOuvrier' => 'actif',
            'poste_id'      => $data['poste_id'],
            'chantier_id'   => $data['chantier_id'],
        ]);
    }

    public function modifier(Ouvrier $ouvrier, array $data): Ouvrier
    {
        $ouvrier->update([
            'nomOuvrier'    => $data['nomOuvrier'],
            'prenomOuvrier' => $data['prenomOuvrier'],
            'telOuvrier'    => $data['telOuvrier'],
            'poste_id'      => $data['poste_id'],
            'chantier_id'   => $data['chantier_id'],
        ]);

        return $ouvrier->fresh();
    }

    public function toggleStatut(Ouvrier $ouvrier): Ouvrier
    {
        $ouvrier->update(['statutOuvrier' => $ouvrier->statutOuvrier === 'actif' ? 'inactif' : 'actif']);
        return $ouvrier->fresh();
    }

    public function supprimer(Ouvrier $ouvrier): void
    {
        if ($ouvrier->statutOuvrier === 'actif') {
            throw new \Exception("Impossible de supprimer un ouvrier actif. Désactivez-le d'abord.");
        }

        $ouvrier->delete();
    }
}
