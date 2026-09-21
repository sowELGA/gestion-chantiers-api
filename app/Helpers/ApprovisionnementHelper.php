<?php

namespace App\Helpers;

use App\Models\Approvisionnement;
use Carbon\Carbon;

class ApprovisionnementHelper
{
    public static function calculerPriorite(string $dateLivraison): string
    {
        $date = Carbon::parse($dateLivraison)->startOfDay();
        $aujourdHui = today();

        if ($date->lte($aujourdHui)) {
            return 'urgent';
        }

        return $aujourdHui->diffInDays($date) <= 2 ? 'urgent' : 'normal';
    }

    public static function totalRecu(Approvisionnement $demande): float
    {
        $bons = $demande->relationLoaded('bonReceptions') ? $demande->bonReceptions : $demande->bonReceptions()->get();
        return (float) $bons->sum('quantite_recue');
    }

    public static function quantiteRestante(Approvisionnement $demande): float
    {
        return max(0, (float) ($demande->quantite_demandee - self::totalRecu($demande)));
    }

    public static function determinerStatutApresReception(Approvisionnement $demande): string
    {
        return self::totalRecu($demande) >= $demande->quantite_demandee ? 'cloturee' : 'partiellement_recue';
    }
}
