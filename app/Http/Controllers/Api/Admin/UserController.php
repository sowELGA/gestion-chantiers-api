<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserService;

class UserController extends Controller
{
    public function __construct(protected UserService $userService) {}

    public function index()
    {
        $users = User::with('roles')->orderBy('nomUser')->paginate(20);

        return UserResource::collection($users);
    }

    public function show(User $user)
    {
        return new UserResource($user->load('roles'));
    }

    public function store(StoreUserRequest $request)
    {
        $resultat = $this->userService->creer(
            $request->only(['nomUser', 'prenomUser', 'email', 'telUser']),
            $request->input('roles')
        );

        return response()->json([
            'message' => 'Utilisateur créé avec succès.',
            'user'    => new UserResource($resultat['user']),
        ], 201);
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $user = $this->userService->modifier(
            $user,
            $request->only(['nomUser', 'prenomUser', 'email', 'telUser']),
            $request->input('roles')
        );

        return response()->json([
            'message' => 'Utilisateur modifié avec succès.',
            'user'    => new UserResource($user),
        ]);
    }

    public function toggleActif(User $user)
    {
        $user = $this->userService->toggleActif($user);

        return response()->json([
            'message' => $user->actif ? 'Compte activé.' : 'Compte désactivé.',
            'user'    => new UserResource($user),
        ]);
    }

    public function reinitialiserMotDePasse(User $user)
    {
        $motDePasse = $this->userService->reinitialiserMotDePasse($user);

        return response()->json([
            'message' => 'Mot de passe réinitialisé avec succès. Le nouveau mot de passe a été envoyé par email à l\'utilisateur.',
        ]);
    }
}
