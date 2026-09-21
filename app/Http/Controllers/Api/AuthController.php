<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use App\Mail\DemandeResetNotifMail;
use App\Models\DemandeResetMdp;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Identifiants incorrects.',
                'errors'  => ['email' => ['Identifiants incorrects.']],
            ], 422);
        }

        /** @var User $user */
        $user = Auth::user();

        if (!$user->actif) {
            Auth::logout();
            return response()->json([
                'message' => 'Votre compte est désactivé. Contactez la direction.',
                'errors'  => ['email' => ['Votre compte est désactivé. Contactez la direction.']],
            ], 422);
        }

        // Supprime les anciens tokens (optionnel, évite l'accumulation)
        $user->tokens()->delete();

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => new UserResource($user->load('roles')),
        ]);
    }

    public function logout()
    {
        auth()->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Déconnecté.']);
    }

    public function me()
    {
        return new UserResource(auth()->user()->load('roles'));
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        $user = auth()->user();

        $user->update([
            'password'           => Hash::make($request->validated()['password']),
            'premiere_connexion' => false,
        ]);

        return response()->json([
            'message' => 'Mot de passe mis à jour avec succès.',
            'user'    => new UserResource($user->fresh('roles')),
        ]);
    }

    // ── Mot de passe oublié ─────────────────────────────────
    public function motDePasseOublie(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ], [
            'email.required' => "L'email est obligatoire.",
            'email.email'    => "Format d'email invalide.",
            'email.exists'   => 'Aucun compte trouvé avec cet email.',
        ]);

        $dejaEnAttente = DemandeResetMdp::where('email', $request->email)
            ->where('statut', 'en_attente')
            ->exists();

        if ($dejaEnAttente) {
            return response()->json([
                'message' => 'Une demande est déjà en cours. La direction la traitera bientôt.',
            ]);
        }

        $demande = DemandeResetMdp::create([
            'email'  => $request->email,
            'statut' => 'en_attente',
        ]);

        $userDemandeur = User::where('email', $request->email)->first();
        $admins = User::avecRole(\App\Models\Role::ADMIN)->where('actif', true)->get();

        foreach ($admins as $admin) {
            Mail::to($admin->email)->send(new DemandeResetNotifMail($userDemandeur, $demande));
        }

        return response()->json([
            'message' => 'Votre demande a été transmise à l\'administrateur. Vous recevrez un email avec vos nouveaux identifiants.',
        ]);
    }
}
