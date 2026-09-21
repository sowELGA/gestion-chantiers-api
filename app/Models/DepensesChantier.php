<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DepensesChantier extends Model
{
    protected $table = 'depenses_chantiers';

    protected $fillable = ['categorie', 'montant', 'description', 'date_depense', 'chantier_id'];

    protected $casts = [
        'montant' => 'decimal:2',
        'date_depense' => 'date',
    ];

    public function chantier()
    {
        return $this->belongsTo(Chantier::class, 'chantier_id');
    }
}
