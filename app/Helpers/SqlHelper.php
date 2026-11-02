<?php

namespace App\Helpers;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;

class SqlHelper
{
    /**
     * Équivalent portable de MySQL FIELD(colonne, v1, v2, ...).
     * L'ordre des valeurs est conservé : la première vaut 1, la suivante 2, etc.
     */
    public static function ordrePersonnalise(string $colonne, array $valeurs): string
    {
        $colonne = self::wrap($colonne);
        $parts = [];

        foreach (array_values($valeurs) as $index => $valeur) {
            $parts[] = 'WHEN '.$colonne.' = '.self::quote($valeur).' THEN '.($index + 1);
        }

        return 'CASE '.implode(' ', $parts).' ELSE '.(count($valeurs) + 1).' END';
    }

    /**
     * LIKE insensible à la casse : ILIKE sur PostgreSQL, LIKE ailleurs
     * (MySQL/SQLite le sont déjà via leur collation par défaut).
     */
    public static function operateurContient(EloquentBuilder|QueryBuilder $query): string
    {
        return $query->getConnection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
    }

    public static function wrap(string $identifiant): string
    {
        return DB::getQueryGrammar()->wrap($identifiant);
    }

    public static function quote(string $valeur): string
    {
        return "'".str_replace("'", "''", $valeur)."'";
    }
}
