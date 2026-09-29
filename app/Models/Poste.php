<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Poste extends Model
{
    use HasFactory;

    protected $table = 'postes';
    protected $fillable = ['libelle'];

    public function scopeDisponiblesPourChantier($query, int $chantierId)
    {
        return $query->whereHas('tauxSalaires', fn($q) => $q->where('chantier_id', $chantierId));
    }

    public function ouvriers()
    {
        return $this->hasMany(Ouvrier::class, 'poste_id');
    }

    public function tauxSalaires()
    {
        return $this->hasMany(TauxSalaire::class, 'poste_id');
    }
}
