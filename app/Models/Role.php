<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = ['nom', 'libelle', 'exclusif'];

    protected $casts = [
        'exclusif' => 'boolean',
    ];

    // Noms constants pour éviter les fautes de frappe dans le code
    public const ADMIN = 'admin';
    public const DIRECTEUR_TRAVAUX = 'directeur_travaux';
    public const DAF = 'daf';
    public const RESPONSABLE_RH = 'responsable_rh';
    public const CHEF_PROJET = 'chef_projet';
    public const POINTEUR = 'pointeur';

    public const ROLES_EXCLUSIFS = [self::CHEF_PROJET, self::POINTEUR];

    public function users()
    {
        return $this->belongsToMany(User::class)
            ->withPivot(['gere_approvisionnements', 'gere_depenses'])
            ->withTimestamps();
    }
}
