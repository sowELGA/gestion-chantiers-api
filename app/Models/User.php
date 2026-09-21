<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'nomUser',
        'prenomUser',
        'email',
        'telUser',
        'password',
        'premiere_connexion',
        'actif',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'premiere_connexion' => 'boolean',
        'actif'              => 'boolean',
        'password'           => 'hashed',
    ];

    public function getNomCompletAttribute(): string
    {
        return $this->prenomUser . ' ' . $this->nomUser;
    }

    // ── Relations ────────────────────────────────────────────
    public function roles()
    {
        return $this->belongsToMany(Role::class)
            ->withPivot(['gere_approvisionnements', 'gere_depenses'])
            ->withTimestamps();
    }

    public function chantiersGeres()
    {
        return $this->hasMany(Chantier::class, 'chef_projet_id');
    }

    public function chantiersPointes()
    {
        return $this->hasMany(Chantier::class, 'pointeur_id');
    }

    // ── Scopes ───────────────────────────────────────────────
    public function scopeActif($query)
    {
        return $query->where('actif', true);
    }

    public function scopeInactif($query)
    {
        return $query->where('actif', false);
    }

    public function scopeAvecRole($query, string $nomRole)
    {
        return $query->whereHas('roles', fn($q) => $q->where('nom', $nomRole));
    }

    // ── Vérification des rôles ───────────────────────────────
    public function hasRole(string $nom): bool
    {
        return $this->relationLoaded('roles')
            ? $this->roles->contains('nom', $nom)
            : $this->roles()->where('nom', $nom)->exists();
    }

    public function hasAnyRole(array $noms): bool
    {
        return $this->relationLoaded('roles')
            ? $this->roles->whereIn('nom', $noms)->isNotEmpty()
            : $this->roles()->whereIn('nom', $noms)->exists();
    }

    public function isChefProjet(): bool
    {
        return $this->hasRole(Role::CHEF_PROJET);
    }

    public function isPointeur(): bool
    {
        return $this->hasRole(Role::POINTEUR);
    }

    // ── Capacités spécifiques DAF ─────────────────────────────
    public function peutGererApprovisionnements(): bool
    {
        $pivot = $this->roles->firstWhere('nom', Role::DAF)?->pivot;
        return (bool) ($pivot?->gere_approvisionnements);
    }

    public function peutGererDepenses(): bool
    {
        $pivot = $this->roles->firstWhere('nom', Role::DAF)?->pivot;
        return (bool) ($pivot?->gere_depenses);
    }

    public function getRoleNomsAttribute(): array
    {
        return $this->roles->pluck('nom')->toArray();
    }
}
