<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\InventoryAsset;
use App\Models\InventoryItem;
use App\Models\InventoryItemRequest;
use App\Models\InventoryPurchaseRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class InventoryRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach ([
            'view inventory', 'manage inventory', 'request inventory',
            'approve inventory requests', 'approve inventory purchases',
        ] as $p) {
            Permission::firstOrCreate(['name' => $p]);
        }

        Role::firstOrCreate(['name' => 'Principal'])->givePermissionTo(['view inventory', 'approve inventory requests', 'request inventory']);
        Role::firstOrCreate(['name' => 'General Manager'])->givePermissionTo(['view inventory', 'approve inventory purchases']);
        Role::firstOrCreate(['name' => 'Inventory Manager'])->givePermissionTo(['view inventory', 'manage inventory', 'request inventory']);
        Role::firstOrCreate(['name' => 'Teacher'])->givePermissionTo(['request inventory']);
    }

    private function userWithRole(string $role, bool $withEmployee = false): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);
        if ($withEmployee) {
            Employee::factory()->create(['user_id' => $user->id]);
        }

        return $user;
    }

    /** @test */
    public function full_item_request_flow_consumable_fulfilment_updates_stock(): void
    {
        Notification::fake();

        $teacher = $this->userWithRole('Teacher', withEmployee: true);
        $principal = $this->userWithRole('Principal');
        $manager = $this->userWithRole('Inventory Manager');
        $item = InventoryItem::factory()->consumable(100, 10)->create();

        // Submit
        $this->actingAs($teacher)->post(route('teacher.inventory-requests.item.store'), [
            'inventory_item_id' => $item->id,
            'quantity' => 30,
            'purpose' => 'Class handouts',
        ])->assertSessionHas('success');

        $req = InventoryItemRequest::first();
        $this->assertSame('pending', $req->status);

        // Principal approves
        $this->actingAs($principal)->post(route('admin.inventory.requests.approve', $req))->assertSessionHas('success');
        $this->assertSame('approved', $req->fresh()->status);

        // IM fulfils -> stock drops 100 -> 70, movement linked
        $this->actingAs($manager)->post(route('admin.inventory.requests.fulfil', $req))->assertSessionHas('success');
        $req->refresh();
        $this->assertSame('fulfilled', $req->status);
        $this->assertSame(70, $item->fresh()->quantity);
        $this->assertNotNull($req->stock_movement_id);
    }

    /** @test */
    public function item_request_for_asset_assigns_a_unit_on_fulfilment(): void
    {
        $teacher = $this->userWithRole('Teacher', withEmployee: true);
        $principal = $this->userWithRole('Principal');
        $manager = $this->userWithRole('Inventory Manager');

        $item = InventoryItem::factory()->create(); // asset kind
        $unit = InventoryAsset::factory()->create(['inventory_item_id' => $item->id, 'status' => 'available']);

        $req = InventoryItemRequest::create([
            'requester_employee_id' => $teacher->student ? null : Employee::where('user_id', $teacher->id)->first()->id,
            'inventory_item_id' => $item->id,
            'quantity' => 1,
            'purpose' => 'Classroom projector',
            'status' => 'pending',
        ]);

        $this->actingAs($principal)->post(route('admin.inventory.requests.approve', $req))->assertSessionHas('success');
        $this->actingAs($manager)->post(route('admin.inventory.requests.fulfil', $req))->assertSessionHas('success');

        $this->assertSame('assigned', $unit->fresh()->status);
        $this->assertNotNull($req->fresh()->assignment_id);
    }

    /** @test */
    public function a_principal_cannot_approve_their_own_item_request(): void
    {
        $principal = $this->userWithRole('Principal', withEmployee: true);
        $item = InventoryItem::factory()->consumable(50, 5)->create();

        $req = InventoryItemRequest::create([
            'requester_employee_id' => Employee::where('user_id', $principal->id)->first()->id,
            'inventory_item_id' => $item->id,
            'quantity' => 5,
            'purpose' => 'Own use',
            'status' => 'pending',
        ]);

        $this->actingAs($principal)->post(route('admin.inventory.requests.approve', $req))->assertSessionHas('error');
        $this->assertSame('pending', $req->fresh()->status);
    }

    /** @test */
    public function full_purchase_flow_principal_then_gm_lands_on_purchase_list(): void
    {
        Notification::fake();

        $teacher = $this->userWithRole('Teacher', withEmployee: true);
        $principal = $this->userWithRole('Principal');
        $gm = $this->userWithRole('General Manager');

        $this->actingAs($teacher)->post(route('teacher.inventory-requests.purchase.store'), [
            'item_name' => '3D Printer',
            'quantity' => 1,
            'unit' => 'pcs',
            'estimated_unit_cost' => 45000,
            'justification' => 'STEM lab',
        ])->assertSessionHas('success');

        $req = InventoryPurchaseRequest::first();
        $this->assertSame('pending', $req->status);

        // Stage 1: Principal approves -> pending_gm
        $this->actingAs($principal)->post(route('admin.inventory.purchases.principal', $req), ['decision' => 'approve'])->assertSessionHas('success');
        $this->assertSame('pending_gm', $req->fresh()->status);

        // Stage 2: GM approves -> approved (purchase list)
        $this->actingAs($gm)->post(route('admin.inventory.purchases.gm', $req), ['decision' => 'approve'])->assertSessionHas('success');
        $this->assertSame('approved', $req->fresh()->status);
        $this->assertSame(1, InventoryPurchaseRequest::approved()->count());
    }

    /** @test */
    public function gm_decline_requires_a_comment_and_lands_on_decline_list(): void
    {
        $requester = $this->userWithRole('Inventory Manager');
        $principal = $this->userWithRole('Principal');
        $gm = $this->userWithRole('General Manager');

        $req = InventoryPurchaseRequest::factory()->create(['requested_by' => $requester->id, 'status' => 'pending_gm', 'principal_id' => $principal->id]);

        // No comment -> blocked
        $this->actingAs($gm)->post(route('admin.inventory.purchases.gm', $req), ['decision' => 'decline'])->assertSessionHas('error');
        $this->assertSame('pending_gm', $req->fresh()->status);

        // With comment -> declined
        $this->actingAs($gm)->post(route('admin.inventory.purchases.gm', $req), ['decision' => 'decline', 'remarks' => 'Over budget this term'])->assertSessionHas('success');
        $this->assertSame('declined', $req->fresh()->status);
        $this->assertSame(1, InventoryPurchaseRequest::declined()->count());
    }

    /** @test */
    public function gm_cannot_decide_a_request_still_awaiting_the_principal(): void
    {
        $requester = $this->userWithRole('Teacher', withEmployee: true);
        $gm = $this->userWithRole('General Manager');

        $req = InventoryPurchaseRequest::factory()->create(['requested_by' => $requester->id, 'status' => 'pending']);

        $this->actingAs($gm)->post(route('admin.inventory.purchases.gm', $req), ['decision' => 'approve'])->assertSessionHas('error');
        $this->assertSame('pending', $req->fresh()->status);
    }

    /** @test */
    public function teacher_cannot_reach_approval_screens(): void
    {
        $teacher = $this->userWithRole('Teacher', withEmployee: true);

        $this->actingAs($teacher)->get(route('admin.inventory.requests.index'))->assertForbidden();
        $this->actingAs($teacher)->get(route('admin.inventory.requests.fulfilment'))->assertForbidden();
    }

    /** @test */
    public function requester_pages_render_in_both_portals(): void
    {
        // The teacher layout's sidebar metrics need an active year to render.
        \App\Models\AcademicYear::factory()->active()->create();
        InventoryItem::factory()->consumable(10, 5)->create();

        $teacher = $this->userWithRole('Teacher', withEmployee: true);
        $this->actingAs($teacher)->get(route('teacher.inventory-requests.index'))
            ->assertOk()->assertSee('Purchase Requests');

        // Admin-role requester (Principal here) uses the mirror page.
        $principal = $this->userWithRole('Principal', withEmployee: true);
        $this->actingAs($principal)->get(route('admin.inventory.my-requests.index'))
            ->assertOk()->assertSee('My Requests');
    }
}
