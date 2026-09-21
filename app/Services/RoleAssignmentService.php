<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class RoleAssignmentService
{
    /**
     * Synchronise les rôles d'un utilisateur.
     *
     * @param  User  $user
     * @param  array<int, array{id: int, gere_approvisionnements?: bool, gere_depenses?: bool}>  $rolesDemandes
     */
    public function synchroniser(User $user, array $rolesDemandes): User
    {
        if (empty($rolesDemandes)) {
            throw ValidationException::withMessages([
                'roles' => 'Un utilisateur doit avoir au moins un rôle.',
            ]);
        }

        $roleIds = collect($rolesDemandes)->pluck('id')->all();
        $roles = Role::whereIn('id', $roleIds)->get();

        if ($roles->count() !== count($roleIds)) {
            throw ValidationException::withMessages([
                'roles' => 'Un ou plusieurs rôles sont invalides.',
            ]);
        }

        $rolesExclusifs = $roles->where('exclusif', true);
        $rolesNonExclusifs = $roles->where('exclusif', false);

        // Règle 1 : impossible de mélanger un rôle exclusif avec un autre rôle
        if ($rolesExclusifs->isNotEmpty() && $roles->count() > 1) {
            throw ValidationException::withMessages([
                'roles' => "Le rôle \"{$rolesExclusifs->first()->libelle}\" ne peut pas être combiné avec d'autres rôles.",
            ]);
        }

        // Règle 2 : impossible d'avoir 2 rôles exclusifs en même temps (chef_projet + pointeur)
        if ($rolesExclusifs->count() > 1) {
            throw ValidationException::withMessages([
                'roles' => 'Un utilisateur ne peut pas être à la fois Chef de Projet et Pointeur.',
            ]);
        }

        // Construction des données pivot (gere_approvisionnements / gere_depenses uniquement pour DAF)
        $syncData = [];
        foreach ($rolesDemandes as $roleDemande) {
            $role = $roles->firstWhere('id', $roleDemande['id']);

            $pivot = [];
            if ($role->nom === Role::DAF) {
                $pivot['gere_approvisionnements'] = $roleDemande['gere_approvisionnements'] ?? false;
                $pivot['gere_depenses'] = $roleDemande['gere_depenses'] ?? false;

                if (!$pivot['gere_approvisionnements'] && !$pivot['gere_depenses']) {
                    throw ValidationException::withMessages([
                        'roles' => 'Le rôle DAF doit gérer au moins les approvisionnements ou les dépenses.',
                    ]);
                }
            }

            $syncData[$role->id] = $pivot;
        }

        $rolesActuels = $user->roles;
        $nouveauxNoms = $roles->pluck('nom')->all();

        $perteChefProjet = $rolesActuels->contains('nom', Role::CHEF_PROJET) && !in_array(Role::CHEF_PROJET, $nouveauxNoms);
        $pertePointeur = $rolesActuels->contains('nom', Role::POINTEUR) && !in_array(Role::POINTEUR, $nouveauxNoms);

        if ($perteChefProjet && \App\Models\Chantier::where('chef_projet_id', $user->id)->exists()) {
            throw ValidationException::withMessages([
                'roles' => 'Impossible de retirer le rôle Chef de Projet : cet utilisateur est encore affecté à un chantier. Désaffectez-le d\'abord.',
            ]);
        }

        if ($pertePointeur && \App\Models\Chantier::where('pointeur_id', $user->id)->exists()) {
            throw ValidationException::withMessages([
                'roles' => 'Impossible de retirer le rôle Pointeur : cet utilisateur est encore affecté à un chantier. Désaffectez-le d\'abord.',
            ]);
        }

        $user->roles()->sync($syncData);

        return $user->fresh('roles');
    }
}
