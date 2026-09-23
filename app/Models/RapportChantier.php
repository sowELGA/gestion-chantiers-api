<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RapportChantier extends Model
{
    use HasFactory;

    protected $table = 'rapports_chantiers';
    protected $fillable = ['date_rapport', 'titre', 'type', 'contenu', 'chantier_id', 'auteur_id'];

    protected $casts = [
        'date_rapport' => 'date',
    ];

    public function chantier()
    {
        return $this->belongsTo(Chantier::class, 'chantier_id');
    }

    public function auteur()
    {
        return $this->belongsTo(User::class, 'auteur_id');
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'avancement' => 'Avancement',
            'incident'   => 'Incident',
            'livraison'  => 'Livraison',
            'reunion'    => 'Réunion',
            default      => 'Autre',
        };
    }
}
