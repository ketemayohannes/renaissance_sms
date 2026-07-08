<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\InventoryItem;
use App\Models\InventoryItemRequest;
use App\Models\InventoryPurchaseRequest;
use App\Models\User;
use App\Notifications\InventoryItemRequestUpdate;
use App\Notifications\InventoryPurchaseRequestUpdate;
use Illuminate\Support\Facades\Notification;
use RuntimeException;

/**
 * The two inventory request workflows (item fulfilment and purchase procurement),
 * including their approval chains, notifications, and the self-approval guard that
 * matters now that admins can both submit and approve.
 */
class InventoryRequestService
{
    public function __construct(private InventoryService $inventory) {}

    // ─────────────────────────────────────── Item requests ───────────────────────────────

    public function submitItemRequest(Employee $requester, array $data): InventoryItemRequest
    {
        $request = InventoryItemRequest::create([
            'requester_employee_id' => $requester->id,
            'inventory_item_id' => $data['inventory_item_id'],
            'quantity' => $data['quantity'],
            'purpose' => $data['purpose'],
            'status' => 'pending',
        ]);

        $this->notifyRoles(['Principal', 'Super Admin'], new InventoryItemRequestUpdate($request, 'submitted'));

        return $request;
    }

    public function approveItemRequest(InventoryItemRequest $request, User $decider, ?string $remarks = null): void
    {
        $this->assertPending($request->status);
        $this->assertNotSelfApproval($request->requester?->user_id, $decider->id);

        $request->update([
            'status' => 'approved',
            'decided_by' => $decider->id,
            'decision_remarks' => $remarks,
            'decided_at' => now(),
        ]);

        // Requester (if they have a login) + Inventory Managers.
        $recipients = $this->roleUsers(['Inventory Manager']);
        if ($user = $request->requester?->user) {
            $recipients->push($user);
        }
        Notification::send($recipients->unique('id'), new InventoryItemRequestUpdate($request->fresh('requester'), 'approved'));
    }

    public function rejectItemRequest(InventoryItemRequest $request, User $decider, ?string $remarks = null): void
    {
        $this->assertPending($request->status);
        $this->assertNotSelfApproval($request->requester?->user_id, $decider->id);

        $request->update([
            'status' => 'rejected',
            'decided_by' => $decider->id,
            'decision_remarks' => $remarks,
            'decided_at' => now(),
        ]);

        $this->notifyRequesterUser($request->requester?->user, new InventoryItemRequestUpdate($request->fresh('requester'), 'rejected'));
    }

    /**
     * Inventory Manager hands the item over: consumables become a guarded stock-out,
     * assets become an assignment to the requester. Reuses InventoryService so the
     * negative-stock / availability guards are identical to the manual screens.
     *
     * @throws RuntimeException when stock/units are unavailable
     */
    public function fulfilItemRequest(InventoryItemRequest $request, User $manager): void
    {
        if ($request->status !== 'approved') {
            throw new RuntimeException('Only approved requests can be fulfilled.');
        }

        $item = $request->item;
        $updates = ['status' => 'fulfilled', 'fulfilled_by' => $manager->id, 'fulfilled_at' => now()];

        if ($item->kind === 'consumable') {
            $movement = $this->inventory->issueStock($item, [
                'quantity' => $request->quantity,
                'issued_to_employee_id' => $request->requester_employee_id,
                'movement_date' => now()->toDateString(),
                'remarks' => 'Fulfilment of request #' . $request->id,
            ], $manager->id);
            $updates['stock_movement_id'] = $movement->id;
        } else {
            $unit = $this->inventory->firstAvailableUnit($item);
            if (! $unit) {
                throw new RuntimeException("No available unit of {$item->name} to hand over.");
            }
            $assignment = $this->inventory->assignAsset($unit, [
                'employee_id' => $request->requester_employee_id,
                'notes' => 'Fulfilment of request #' . $request->id,
            ], $manager->id);
            $updates['assignment_id'] = $assignment->id;
        }

        $request->update($updates);

        $this->notifyRequesterUser($request->requester?->user, new InventoryItemRequestUpdate($request->fresh('requester'), 'fulfilled'));
    }

    // ───────────────────────────────────── Purchase requests ─────────────────────────────

    public function submitPurchaseRequest(User $requester, array $data): InventoryPurchaseRequest
    {
        $request = InventoryPurchaseRequest::create([
            'requested_by' => $requester->id,
            'inventory_item_id' => $data['inventory_item_id'] ?? null,
            'inventory_category_id' => $data['inventory_category_id'] ?? null,
            'item_name' => $data['item_name'],
            'quantity' => $data['quantity'],
            'unit' => $data['unit'] ?? null,
            'estimated_unit_cost' => $data['estimated_unit_cost'] ?? null,
            'justification' => $data['justification'],
            'status' => 'pending',
        ]);

        $this->notifyRoles(['Principal', 'Super Admin'], new InventoryPurchaseRequestUpdate($request, 'submitted'));

        return $request;
    }

    public function principalDecidePurchase(InventoryPurchaseRequest $request, User $principal, string $decision, ?string $remarks = null): void
    {
        if ($request->status !== 'pending') {
            throw new RuntimeException('This request is no longer awaiting the Principal.');
        }
        $this->assertNotSelfApproval($request->requested_by, $principal->id);

        $request->update([
            'status' => $decision === 'approve' ? 'pending_gm' : 'principal_declined',
            'principal_id' => $principal->id,
            'principal_remarks' => $remarks,
            'principal_decided_at' => now(),
        ]);

        if ($decision === 'approve') {
            $this->notifyRoles(['General Manager', 'Super Admin'], new InventoryPurchaseRequestUpdate($request->fresh(), 'principal_approved'));
        } else {
            $this->notifyRequesterUser($request->requester, new InventoryPurchaseRequestUpdate($request->fresh(), 'principal_declined'));
        }
    }

    public function gmDecidePurchase(InventoryPurchaseRequest $request, User $gm, string $decision, ?string $remarks = null): void
    {
        if ($request->status !== 'pending_gm') {
            throw new RuntimeException('This request is not awaiting the General Manager.');
        }
        $this->assertNotSelfApproval($request->requested_by, $gm->id);

        $request->update([
            'status' => $decision === 'approve' ? 'approved' : 'declined',
            'gm_id' => $gm->id,
            'gm_remarks' => $remarks,
            'gm_decided_at' => now(),
        ]);

        if ($decision === 'approve') {
            $recipients = $this->roleUsers(['Inventory Manager']);
            if ($user = $request->requester) {
                $recipients->push($user);
            }
            Notification::send($recipients->unique('id'), new InventoryPurchaseRequestUpdate($request->fresh(), 'approved'));
        } else {
            $this->notifyRequesterUser($request->requester, new InventoryPurchaseRequestUpdate($request->fresh(), 'declined'));
        }
    }

    // ─────────────────────────────────────────── Guards ──────────────────────────────────

    private function assertPending(string $status): void
    {
        if ($status !== 'pending') {
            throw new RuntimeException('This request has already been decided.');
        }
    }

    /**
     * The person deciding a request must not be the person who submitted it.
     */
    private function assertNotSelfApproval(?int $requesterUserId, int $deciderId): void
    {
        if ($requesterUserId !== null && $requesterUserId === $deciderId) {
            throw new RuntimeException('You cannot decide your own request — another approver must handle it.');
        }
    }

    // ──────────────────────────────────────── Notifications ──────────────────────────────

    private function notifyRoles(array $roles, $notification): void
    {
        Notification::send($this->roleUsers($roles), $notification);
    }

    private function roleUsers(array $roles)
    {
        // Guard against a role name that isn't seeded (Spatie throws otherwise).
        $existing = \Spatie\Permission\Models\Role::whereIn('name', $roles)->pluck('name')->all();

        return empty($existing) ? collect() : User::role($existing)->get();
    }

    private function notifyRequesterUser(?User $user, $notification): void
    {
        if ($user) {
            $user->notify($notification);
        }
    }
}
