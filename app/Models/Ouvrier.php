<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ouvrier extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ouvriers';
    protected $fillable = ['nomOuvrier', 'prenomOuvrier', 'telOuvrier', 'statutOuvrier', 'poste_id', 'chantier_id'];

    public function getNomCompletAttribute(): string
    {
        return $this->prenomOuvrier . ' ' . $this->nomOuvrier;
    }

    public function scopeActif($query)
    {
        return $query->where('statutOuvrier', 'actif');
    }

    public function poste()
    {
        return $this->belongsTo(Poste::class, 'poste_id');
    }

    public function chantier()
    {
        return $this->belongsTo(Chantier::class, 'chantier_id');
    }

    public function pointages()
    {
        return $this->hasMany(Pointage::class, 'ouvrier_id');
    }

    public function recapsHebdomadaires()
    {
        return $this->hasMany(RecapHebdomadaire::class, 'ouvrier_id');
    }
}
