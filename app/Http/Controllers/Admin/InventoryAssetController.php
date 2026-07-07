<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryAsset;
use App\Models\InventoryAssignment;
use App\Models\InventoryItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryAssetController extends Controller
{
    public function store(Request $request, InventoryItem $item)
    {
        if ($item->kind !== 'asset') {
            return back()->with('error', 'Physical units can only be added to asset-kind items.');
        }

        $validated = $request->validate([
            'asset_tag' => 'required|string|max:100|unique:inventory_assets,asset_tag',
            'serial_number' => 'nullable|string|max:255',
            'condition' => 'required|in:' . implode(',', InventoryAsset::CONDITIONS),
            'purchase_date' => 'nullable|date|before_or_equal:today',
            'unit_cost' => 'nullable|numeric|min:0',
            'supplier' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        $item->assets()->create($validated + [
            'status' => $validated['condition'] === 'retired' ? 'retired' : 'available',
        ]);

        return back()->with('success', "Unit {$validated['asset_tag']} added.");
    }

    public function update(Request $request, InventoryAsset $asset)
    {
        $validated = $request->validate([
            'serial_number' => 'nullable|string|max:255',
            'condition' => 'required|in:' . implode(',', InventoryAsset::CONDITIONS),
            'purchase_date' => 'nullable|date|before_or_equal:today',
            'unit_cost' => 'nullable|numeric|min:0',
            'supplier' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        $asset->update($validated);

        return back()->with('success', "Unit {$asset->asset_tag} updated.");
    }

    public function assign(Request $request, InventoryAsset $asset)
    {
        if ($asset->status !== 'available') {
            return back()->with('error', "Unit {$asset->asset_tag} is not available (currently {$asset->status}).");
        }

        $validated = $request->validate([
            'employee_id' => 'nullable|exists:employees,id|required_without:location',
            'location' => 'nullable|string|max:255|required_without:employee_id',
            'notes' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($asset, $validated) {
            InventoryAssignment::create([
                'inventory_asset_id' => $asset->id,
                'employee_id' => $validated['employee_id'] ?? null,
                'location' => $validated['location'] ?? null,
                'assigned_at' => now(),
                'assigned_by' => auth()->id(),
                'notes' => $validated['notes'] ?? null,
            ]);

            $asset->update(['status' => 'assigned']);
        });

        return back()->with('success', "Unit {$asset->asset_tag} assigned.");
    }

    public function returnAsset(InventoryAsset $asset)
    {
        $active = $asset->activeAssignment;
        if (! $active) {
            return back()->with('error', "Unit {$asset->asset_tag} has no active assignment.");
        }

        DB::transaction(function () use ($asset, $active) {
            $active->update(['returned_at' => now()]);
            $asset->update(['status' => 'available']);
        });

        return back()->with('success', "Unit {$asset->asset_tag} returned to store.");
    }

    /**
     * Maintenance / retirement transitions. Assigned units must be returned first so
     * the assignment history stays truthful.
     */
    public function setStatus(Request $request, InventoryAsset $asset)
    {
        $validated = $request->validate([
            'status' => 'required|in:available,in_maintenance,retired',
            'condition' => 'nullable|in:' . implode(',', InventoryAsset::CONDITIONS),
        ]);

        if ($asset->status === 'assigned') {
            return back()->with('error', "Unit {$asset->asset_tag} is assigned — return it before changing its status.");
        }

        $updates = ['status' => $validated['status']];
        if (! empty($validated['condition'])) {
            $updates['condition'] = $validated['condition'];
        }
        if ($validated['status'] === 'retired' && empty($validated['condition'])) {
            $updates['condition'] = 'retired';
        }

        $asset->update($updates);

        return back()->with('success', "Unit {$asset->asset_tag} is now {$validated['status']}.");
    }
}
