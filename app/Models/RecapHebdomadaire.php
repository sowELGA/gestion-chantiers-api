<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecapHebdomadaire extends Model
{
    use HasFactory;

    protected $table = 'recaps_hebdomadaires';
    protected $fillable = ['semaine', 'annee', 'statutRecap', 'motif_rejet', 'valide_le', 'ouvrier_id', 'chantier_id', 'soumis_par_id', 'valide_par_id'];

    protected $casts = [
        'valide_le' => 'datetime',
    ];

    public function ouvrier()
    {
        return $this->belongsTo(Ouvrier::class, 'ouvrier_id');
    }

    public function chantier()
    {
        return $this->belongsTo(Chantier::class, 'chantier_id');
    }

    public function soumisParUser()
    {
        return $this->belongsTo(User::class, 'soumis_par_id');
    }

    public function valideParUser()
    {
        return $this->belongsTo(User::class, 'valide_par_id');
    }
}
