<?php

namespace App\Helpers;

use Carbon\Carbon;

class SemaineHelper
{
    public static function debutCycle(Carbon $date): Carbon
    {
        $dow = $date->dayOfWeek;

        $reculJours = match ($dow) {
            Carbon::SATURDAY => 0,
            Carbon::SUNDAY   => 1,
            Carbon::MONDAY   => 2,
            Carbon::TUESDAY  => 3,
            Carbon::WEDNESDAY => 4,
            Carbon::THURSDAY => 5,
            Carbon::FRIDAY   => 6,
            default          => 0,
        };

        return $date->copy()->subDays($reculJours)->startOfDay();
    }

    public static function finCycle(Carbon $date): Carbon
    {
        return self::debutCycle($date)->addDays(6)->endOfDay();
    }

    public static function numeroCycle(Carbon $date): int
    {
        return self::debutCycle($date)->isoWeek();
    }

    public static function anneeCycle(Carbon $date): int
    {
        return self::debutCycle($date)->year;
    }

    public static function debutDepuisNumero(int $semaine, int $annee): Carbon
    {
        $lundi = Carbon::now()->setISODate($annee, $semaine, 1)->startOfDay();
        $samedi = $lundi->subDays(2);

        if ($samedi->isoWeek() !== $semaine) {
            for ($i = -7; $i <= 7; $i++) {
                $candidat = $samedi->copy()->addDays($i);
                if ($candidat->dayOfWeek === Carbon::SATURDAY && $candidat->isoWeek() === $semaine && $candidat->year === $annee) {
                    return $candidat->startOfDay();
                }
            }
        }

        return $samedi->startOfDay();
    }

    public static function finDepuisNumero(int $semaine, int $annee): Carbon
    {
        return self::debutDepuisNumero($semaine, $annee)->addDays(6)->endOfDay();
    }

    public static function jours(int $semaine, int $annee): array
    {
        $samedi = self::debutDepuisNumero($semaine, $annee);
        $jours = [];
        for ($i = 0; $i <= 6; $i++) {
            $jours[] = $samedi->copy()->addDays($i)->startOfDay();
        }
        return $jours;
    }

    public static function libelle(int $semaine, int $annee): string
    {
        $sam = self::debutDepuisNumero($semaine, $annee);
        $ven = self::finDepuisNumero($semaine, $annee);

        return 'Semaine ' . $semaine . ' — ' . $sam->locale('fr')->isoFormat('D MMM') . ' au ' . $ven->locale('fr')->isoFormat('D MMM YYYY');
    }

    public static function appartientAuCycle(Carbon $date, int $semaine, int $annee): bool
    {
        return self::numeroCycle($date) === $semaine && self::anneeCycle($date) === $annee;
    }
}
