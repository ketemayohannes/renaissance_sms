<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * Access is controlled from role management: only Super Admin / IT / System Admin
 * bypass permission checks; every other role (Registrar and General Manager included)
 * gets exactly the permissions ticked for it, and the sidebar follows the same checks.
 */
class RoleManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        $this->seed(RolePermissionSeeder::class);
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function grant(string $role, array $permissions): void
    {
        $superAdmin = $this->userWithRole('Super Admin');
        $roleModel = Role::findByName($role);

        $this->actingAs($superAdmin)->put("/admin/roles/{$roleModel->id}", [
            'name' => $role,
            'permissions' => array_merge($roleModel->permissions->pluck('name')->all(), $permissions),
        ])->assertRedirect(route('admin.roles.index'));

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function test_registrar_and_general_manager_no_longer_pass_every_permission_check(): void
    {
        $registrar = $this->userWithRole('Registrar');
        $gm = $this->userWithRole('General Manager');

        foreach ([$registrar, $gm] as $user) {
            $this->assertFalse($user->can('delete students'));
            $this->assertFalse($user->can('manage roles'));
            $this->actingAs($user)->get('/admin/roles')->assertForbidden();
            $this->actingAs($user)->get('/admin/promotions')->assertForbidden();
        }

        $student = Student::factory()->create();
        $this->actingAs($registrar)->delete("/admin/students/{$student->id}")->assertForbidden();
        $this->assertNotNull($student->fresh());

        // What each role IS given still works.
        $this->assertTrue($registrar->can('create students'));
        $this->assertTrue($gm->can('approve inventory purchases'));
    }

    public function test_super_admin_and_it_admin_still_have_everything(): void
    {
        foreach (['Super Admin', 'IT / System Admin'] as $role) {
            $user = $this->userWithRole($role);
            $this->assertTrue($user->can('delete students'));
            $this->assertTrue($user->can('manage roles'));
        }
    }

    public function test_access_granted_on_the_roles_page_takes_effect(): void
    {
        $registrar = $this->userWithRole('Registrar');
        $student = Student::factory()->create();

        $this->grant('Registrar', ['delete students', 'manage attendance']);

        $this->actingAs($registrar)->delete("/admin/students/{$student->id}")->assertRedirect();
        $this->assertModelMissing($student);
        $this->actingAs($registrar)->get('/admin/attendance')->assertOk();
    }

    public function test_sidebar_links_follow_the_same_permissions_as_their_pages(): void
    {
        $registrar = $this->userWithRole('Registrar');

        $this->actingAs($registrar)->get('/admin/dashboard')->assertOk()
            ->assertDontSee('title="Attendance"', false)
            ->assertDontSee('title="Promotions"', false)
            ->assertSee('title="Active Students"', false); // has view students

        $this->grant('Registrar', ['manage attendance', 'promote students']);

        $this->actingAs($registrar)->get('/admin/dashboard')->assertOk()
            ->assertSee('title="Attendance"', false)
            ->assertSee('title="Promotions"', false);
    }

    public function test_roles_page_groups_permissions_by_feature_and_explains_them(): void
    {
        Permission::firstOrCreate(['name' => 'some future permission']);
        $superAdmin = $this->userWithRole('Super Admin');
        $registrar = Role::findByName('Registrar');

        $this->actingAs($superAdmin)->get("/admin/roles/{$registrar->id}/edit")->assertOk()
            ->assertSee('Students &amp; Families', false)
            ->assertSee('Run end-of-year promotion and enroll promoted students.')
            ->assertSee('Not used by any page yet')
            ->assertSee('some future permission');
    }

    public function test_unticking_every_permission_clears_the_role(): void
    {
        $superAdmin = $this->userWithRole('Super Admin');
        $registrar = Role::findByName('Registrar');
        $this->assertNotEmpty($registrar->permissions);

        $this->actingAs($superAdmin)->put("/admin/roles/{$registrar->id}", ['name' => 'Registrar'])
            ->assertRedirect(route('admin.roles.index'));

        $this->assertCount(0, $registrar->fresh()->permissions);
    }

    public function test_privileged_roles_cannot_be_renamed_deleted_or_edited_by_others(): void
    {
        $superAdminRole = Role::findByName('Super Admin');
        $itRole = Role::findByName('IT / System Admin');
        $superAdmin = $this->userWithRole('Super Admin');

        $this->actingAs($superAdmin)->put("/admin/roles/{$superAdminRole->id}", ['name' => 'Owner'])
            ->assertSessionHas('error');
        $this->actingAs($superAdmin)->delete("/admin/roles/{$itRole->id}")->assertSessionHas('error');
        $this->assertNotNull(Role::where('name', 'Super Admin')->first());
        $this->assertNotNull(Role::where('name', 'IT / System Admin')->first());

        // Someone given "manage roles" cannot touch the privileged roles.
        $this->grant('Registrar', ['manage roles']);
        $registrar = $this->userWithRole('Registrar');
        $this->actingAs($registrar)->get("/admin/roles/{$superAdminRole->id}/edit")->assertForbidden();
        $this->actingAs($registrar)->put("/admin/roles/{$itRole->id}", ['name' => 'IT / System Admin', 'permissions' => []])
            ->assertForbidden();
    }

    public function test_finance_and_health_pages_load_and_are_permission_gated(): void
    {
        $superAdmin = $this->userWithRole('Super Admin');
        $this->actingAs($superAdmin)->get('/admin/finance/fees')->assertOk();
        $this->actingAs($superAdmin)->get('/admin/finance/payroll')->assertOk();
        $this->actingAs($superAdmin)->get('/admin/portals/health')->assertOk();

        $registrar = $this->userWithRole('Registrar');
        $this->actingAs($registrar)->get('/admin/portals/health')->assertForbidden();
        $this->actingAs($registrar)->get('/admin/finance/fees')->assertForbidden();
    }
}
