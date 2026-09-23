<?php

namespace Tests;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Laravel\Sanctum\Sanctum;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    protected function creerUserAvecRole(string|array $noms, array $attributs = []): User
    {
        $user = User::factory()->create($attributs);
        $noms = is_array($noms) ? $noms : [$noms];

        $roles = Role::whereIn('nom', $noms)->get();
        $syncData = $roles->mapWithKeys(fn($r) => [$r->id => $r->nom === 'daf' ? ['gere_approvisionnements' => true, 'gere_depenses' => true] : []]);

        $user->roles()->sync($syncData);

        return $user->fresh('roles');
    }

    protected function connecterEnTantQue(User $user): static
    {
        Sanctum::actingAs($user);
        return $this;
    }
}
