<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\InventoryAsset;
use App\Models\InventoryCategory;
use App\Models\InventoryItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class InventoryModuleTest extends TestCase
{
    use RefreshDatabase;

    protected User $manager;
    protected User $viewer;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['view inventory', 'manage inventory'] as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $managerRole = Role::firstOrCreate(['name' => 'Inventory Manager']);
        $managerRole->givePermissionTo(['view inventory', 'manage inventory']);

        $viewerRole = Role::firstOrCreate(['name' => 'Principal']);
        $viewerRole->givePermissionTo(['view inventory']);

        $this->manager = User::factory()->create();
        $this->manager->assignRole('Inventory Manager');

        $this->viewer = User::factory()->create();
        $this->viewer->assignRole('Principal');
    }

    /** @test */
    public function inventory_manager_can_create_items_and_asset_units(): void
    {
        $category = InventoryCategory::factory()->create();

        $this->actingAs($this->manager)->post(route('admin.inventory.items.store'), [
            'inventory_category_id' => $category->id,
            'name' => 'HP Laptop',
            'kind' => 'asset',
        ])->assertRedirect();

        $item = InventoryItem::where('name', 'HP Laptop')->first();
        $this->assertNotNull($item);

        $this->actingAs($this->manager)->post(route('admin.inventory.assets.store', $item), [
            'asset_tag' => 'HPL-0001',
            'condition' => 'good',
        ])->assertSessionHas('success');

        $this->assertSame('available', $item->assets()->first()->status);

        // Duplicate tag is rejected.
        $this->actingAs($this->manager)->post(route('admin.inventory.assets.store', $item), [
            'asset_tag' => 'HPL-0001',
            'condition' => 'good',
        ])->assertSessionHasErrors('asset_tag');
    }

    /** @test */
    public function asset_assignment_lifecycle_is_enforced(): void
    {
        $asset = InventoryAsset::factory()->create();
        $employee = Employee::factory()->create();

        // Assign to employee.
        $this->actingAs($this->manager)->post(route('admin.inventory.assets.assign', $asset), [
            'employee_id' => $employee->id,
        ])->assertSessionHas('success');

        $asset->refresh();
        $this->assertSame('assigned', $asset->status);
        $this->assertSame($employee->id, $asset->activeAssignment->employee_id);

        // Double-assign blocked.
        $this->actingAs($this->manager)->post(route('admin.inventory.assets.assign', $asset), [
            'location' => 'Room 204',
        ])->assertSessionHas('error');

        // Status change while assigned blocked.
        $this->actingAs($this->manager)->post(route('admin.inventory.assets.status', $asset), [
            'status' => 'in_maintenance',
        ])->assertSessionHas('error');

        // Return closes the assignment and frees the unit.
        $this->actingAs($this->manager)->post(route('admin.inventory.assets.return', $asset))
            ->assertSessionHas('success');
        $asset->refresh();
        $this->assertSame('available', $asset->status);
        $this->assertNotNull($asset->assignments()->first()->returned_at);

        // Now maintenance works.
        $this->actingAs($this->manager)->post(route('admin.inventory.assets.status', $asset), [
            'status' => 'in_maintenance',
        ])->assertSessionHas('success');
        $this->assertSame('in_maintenance', $asset->fresh()->status);
    }

    /** @test */
    public function stock_ledger_updates_balance_and_blocks_negative_stock(): void
    {
        $item = InventoryItem::factory()->consumable(0, 10)->create();

        $this->actingAs($this->manager)->post(route('admin.inventory.stock.in', $item), [
            'quantity' => 50,
            'unit_cost' => 12.50,
            'supplier' => 'ABC Supplies',
            'movement_date' => now()->toDateString(),
        ])->assertSessionHas('success');

        $this->assertSame(50, $item->fresh()->quantity);

        $this->actingAs($this->manager)->post(route('admin.inventory.stock.out', $item), [
            'quantity' => 45,
            'issued_to' => 'Grade 9 exams',
            'movement_date' => now()->toDateString(),
        ])->assertSessionHas('success');

        $this->assertSame(5, $item->fresh()->quantity);

        // Over-issue is blocked, balance untouched.
        $this->actingAs($this->manager)->post(route('admin.inventory.stock.out', $item), [
            'quantity' => 6,
            'movement_date' => now()->toDateString(),
        ])->assertSessionHas('error');

        $this->assertSame(5, $item->fresh()->quantity);

        // 5 <= reorder level 10 => low stock.
        $this->assertTrue($item->fresh()->is_low_stock);
        $this->assertSame(1, InventoryItem::lowStock()->count());
    }

    /** @test */
    public function viewer_can_read_but_not_write_and_teachers_are_denied(): void
    {
        $item = InventoryItem::factory()->consumable(20, 5)->create();

        // Principal (view inventory): dashboard, items, reports OK.
        $this->actingAs($this->viewer)->get(route('admin.inventory.dashboard'))->assertOk();
        $this->actingAs($this->viewer)->get(route('admin.inventory.items.index'))->assertOk();
        $this->actingAs($this->viewer)->get(route('admin.inventory.items.show', $item))->assertOk();
        $this->actingAs($this->viewer)->get(route('admin.inventory.reports.index'))->assertOk();

        // …but cannot write.
        $this->actingAs($this->viewer)->post(route('admin.inventory.stock.in', $item), [
            'quantity' => 5,
            'movement_date' => now()->toDateString(),
        ])->assertForbidden();

        // A teacher can't get in at all.
        Role::firstOrCreate(['name' => 'Teacher']);
        $teacher = User::factory()->create();
        $teacher->assignRole('Teacher');
        $this->actingAs($teacher)->get(route('admin.inventory.dashboard'))->assertForbidden();
    }

    /** @test */
    public function inventory_manager_lands_on_inventory_dashboard_at_login(): void
    {
        $this->actingAs($this->manager)
            ->get('/dashboard')
            ->assertRedirect(route('admin.inventory.dashboard'));
    }
}
