<?php

namespace Tests\Unit\Services;

use App\Models\Chantier;
use App\Models\Role;
use App\Models\User;
use App\Services\RoleAssignmentService;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class RoleAssignmentServiceTest extends TestCase
{
    private RoleAssignmentService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new RoleAssignmentService();
    }

    public function test_on_peut_cumuler_plusieurs_roles_non_exclusifs(): void
    {
        $user = User::factory()->create();
        $admin = Role::where('nom', 'admin')->first();
        $dt = Role::where('nom', 'directeur_travaux')->first();

        $this->service->synchroniser($user, [['id' => $admin->id], ['id' => $dt->id]]);

        $this->assertEqualsCanonicalizing(['admin', 'directeur_travaux'], $user->fresh('roles')->roles->pluck('nom')->all());
    }

    public function test_un_role_exclusif_ne_peut_pas_etre_combine(): void
    {
        $this->expectException(ValidationException::class);

        $user = User::factory()->create();
        $admin = Role::where('nom', 'admin')->first();
        $chefProjet = Role::where('nom', 'chef_projet')->first();

        $this->service->synchroniser($user, [['id' => $admin->id], ['id' => $chefProjet->id]]);
    }

    public function test_on_ne_peut_pas_avoir_chef_projet_et_pointeur_en_meme_temps(): void
    {
        $this->expectException(ValidationException::class);

        $user = User::factory()->create();
        $chefProjet = Role::where('nom', 'chef_projet')->first();
        $pointeur = Role::where('nom', 'pointeur')->first();

        $this->service->synchroniser($user, [['id' => $chefProjet->id], ['id' => $pointeur->id]]);
    }

    public function test_le_role_daf_doit_avoir_au_moins_une_capacite_activee(): void
    {
        $this->expectException(ValidationException::class);

        $user = User::factory()->create();
        $daf = Role::where('nom', 'daf')->first();

        $this->service->synchroniser($user, [
            ['id' => $daf->id, 'gere_approvisionnements' => false, 'gere_depenses' => false],
        ]);
    }

    public function test_le_role_daf_peut_activer_seulement_les_depenses(): void
    {
        $user = User::factory()->create();
        $daf = Role::where('nom', 'daf')->first();

        $this->service->synchroniser($user, [
            ['id' => $daf->id, 'gere_approvisionnements' => false, 'gere_depenses' => true],
        ]);

        $this->assertFalse($user->fresh()->peutGererApprovisionnements());
        $this->assertTrue($user->fresh()->peutGererDepenses());
    }

    public function test_on_ne_peut_pas_retirer_le_role_chef_projet_si_affecte_a_un_chantier(): void
    {
        $this->expectException(ValidationException::class);

        $user = $this->creerUserAvecRole('chef_projet');
        Chantier::factory()->create(['chef_projet_id' => $user->id]);

        $admin = Role::where('nom', 'admin')->first();

        // Tentative de remplacer chef_projet par admin, sans désaffecter le chantier d'abord
        $this->service->synchroniser($user, [['id' => $admin->id]]);
    }

    public function test_on_peut_retirer_le_role_chef_projet_une_fois_desaffecte(): void
    {
        $user = $this->creerUserAvecRole('chef_projet');
        Chantier::factory()->create(['chef_projet_id' => $user->id]);

        // Désaffectation du chantier
        Chantier::where('chef_projet_id', $user->id)->update(['chef_projet_id' => null]);

        $admin = Role::where('nom', 'admin')->first();
        $this->service->synchroniser($user, [['id' => $admin->id]]);

        $this->assertEquals(['admin'], $user->fresh('roles')->roles->pluck('nom')->all());
    }
}
