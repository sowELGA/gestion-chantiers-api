<?php

namespace App\Services;

use App\Mail\CompteCreeMail;
use App\Mail\MdpReinitialiseMail;
use App\Models\DemandeResetMdp;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class UserService
{
    public function __construct(
        protected RoleAssignmentService $roleAssignmentService
    ) {}

    /**
     * Crée un utilisateur avec ses rôles.
     *
     * @param  array  $data  nomUser, prenomUser, email, telUser
     * @param  array  $rolesDemandes  [['id' => 1], ['id' => 3, 'gere_approvisionnements' => true, ...]]
     */
    public function creer(array $data, array $rolesDemandes): array
    {
        return DB::transaction(function () use ($data, $rolesDemandes) {
            $motDePasseTemporaire = $this->genererMotDePasse();

            $user = User::create([
                'nomUser'            => $data['nomUser'],
                'prenomUser'         => $data['prenomUser'],
                'email'              => $data['email'],
                'telUser'            => $data['telUser'],
                'password'           => Hash::make($motDePasseTemporaire),
                'premiere_connexion' => true,
                'actif'              => true,
            ]);

            $this->roleAssignmentService->synchroniser($user, $rolesDemandes);

            Mail::to($user->email)->send(new CompteCreeMail($user, $motDePasseTemporaire));

            return [
                'user'                  => $user->fresh('roles'),
                'mot_de_passe_temporaire' => $motDePasseTemporaire,
            ];
        });
    }

    /**
     * Met à jour les infos + les rôles d'un utilisateur.
     */
    public function modifier(User $user, array $data, array $rolesDemandes): User
    {
        return DB::transaction(function () use ($user, $data, $rolesDemandes) {
            $user->update([
                'nomUser'    => $data['nomUser'],
                'prenomUser' => $data['prenomUser'],
                'email'      => $data['email'],
                'telUser'    => $data['telUser'],
            ]);

            $this->roleAssignmentService->synchroniser($user, $rolesDemandes);

            return $user->fresh('roles');
        });
    }

    /**
     * Active / désactive un compte.
     */
    public function toggleActif(User $user): User
    {
        $user->update(['actif' => !$user->actif]);

        return $user->fresh('roles');
    }

    /**
     * Réinitialise le mot de passe d'un utilisateur (génère un nouveau mot de passe temporaire).
     */
    public function reinitialiserMotDePasse(User $user): string
    {
        $nouveauMotDePasse = $this->genererMotDePasse();

        $user->update([
            'password'           => Hash::make($nouveauMotDePasse),
            'premiere_connexion' => true,
        ]);

        Mail::to($user->email)->send(new MdpReinitialiseMail($user, $nouveauMotDePasse));

        return $nouveauMotDePasse;
    }

    /**
     * Génère un mot de passe temporaire lisible (ex: "Xk7Pq2mN").
     */
    protected function genererMotDePasse(): string
    {
        return Str::password(10, symbols: false);
    }

    /**
     * Résout une demande de réinitialisation : régénère le mot de passe du user concerné
     * et marque la demande comme traitée.
     */
    public function resoudreDemandeReset(DemandeResetMdp $demande): array
    {
        $user = User::where('email', $demande->email)->firstOrFail();

        $nouveauMotDePasse = $this->reinitialiserMotDePasse($user);

        $demande->update([
            'statut' => 'traitee',
            'traitee_le' => now(),
        ]);

        return [
            'user' => $user->fresh('roles'),
            'mot_de_passe_temporaire' => $nouveauMotDePasse,
        ];
    }
}
