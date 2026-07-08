<?php

namespace App\Services;

use App\Models\InventoryAsset;
use App\Models\InventoryAssignment;
use App\Models\InventoryItem;
use App\Models\InventoryStockMovement;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Single guarded home for the two operations that actually move inventory —
 * issuing consumable stock and assigning an asset unit. Both the manual admin
 * screens and the request-fulfilment path call these, so the negative-stock
 * guard and status transitions can never diverge between the two.
 */
class InventoryService
{
    /**
     * Issue consumable stock (a stock-out movement) inside a row-locked transaction
     * so concurrent issues can never take the balance negative.
     *
     * @throws RuntimeException when stock is insufficient
     */
    public function issueStock(InventoryItem $item, array $data, int $recordedBy): InventoryStockMovement
    {
        if ($item->kind !== 'consumable') {
            throw new RuntimeException('Stock movements only apply to consumable items.');
        }

        return DB::transaction(function () use ($item, $data, $recordedBy) {
            $fresh = InventoryItem::whereKey($item->id)->lockForUpdate()->first();

            if ($fresh->quantity < $data['quantity']) {
                throw new RuntimeException("Only {$fresh->quantity} {$fresh->unit} in stock — cannot issue {$data['quantity']}.");
            }

            $movement = InventoryStockMovement::create([
                'inventory_item_id' => $item->id,
                'direction' => 'out',
                'quantity' => $data['quantity'],
                'issued_to_employee_id' => $data['issued_to_employee_id'] ?? null,
                'issued_to' => $data['issued_to'] ?? null,
                'movement_date' => $data['movement_date'] ?? now()->toDateString(),
                'recorded_by' => $recordedBy,
                'remarks' => $data['remarks'] ?? null,
            ]);

            $fresh->decrement('quantity', $data['quantity']);

            return $movement;
        });
    }

    /**
     * Receive consumable stock (a stock-in movement) and bump the balance.
     */
    public function receiveStock(InventoryItem $item, array $data, int $recordedBy): InventoryStockMovement
    {
        if ($item->kind !== 'consumable') {
            throw new RuntimeException('Stock movements only apply to consumable items.');
        }

        return DB::transaction(function () use ($item, $data, $recordedBy) {
            $movement = InventoryStockMovement::create([
                'inventory_item_id' => $item->id,
                'direction' => 'in',
                'quantity' => $data['quantity'],
                'unit_cost' => $data['unit_cost'] ?? null,
                'supplier' => $data['supplier'] ?? null,
                'movement_date' => $data['movement_date'] ?? now()->toDateString(),
                'recorded_by' => $recordedBy,
                'remarks' => $data['remarks'] ?? null,
            ]);

            $item->increment('quantity', $data['quantity']);

            return $movement;
        });
    }

    /**
     * Assign an available asset unit to an employee or a location.
     *
     * @throws RuntimeException when the unit is not available
     */
    public function assignAsset(InventoryAsset $asset, array $data, int $assignedBy): InventoryAssignment
    {
        if ($asset->status !== 'available') {
            throw new RuntimeException("Unit {$asset->asset_tag} is not available (currently {$asset->status}).");
        }

        return DB::transaction(function () use ($asset, $data, $assignedBy) {
            $assignment = InventoryAssignment::create([
                'inventory_asset_id' => $asset->id,
                'employee_id' => $data['employee_id'] ?? null,
                'location' => $data['location'] ?? null,
                'assigned_at' => now(),
                'assigned_by' => $assignedBy,
                'notes' => $data['notes'] ?? null,
            ]);

            $asset->update(['status' => 'assigned']);

            return $assignment;
        });
    }

    /**
     * First available unit of an asset-kind item, or null if none free.
     */
    public function firstAvailableUnit(InventoryItem $item): ?InventoryAsset
    {
        return $item->assets()->where('status', 'available')->orderBy('asset_tag')->first();
    }
}
