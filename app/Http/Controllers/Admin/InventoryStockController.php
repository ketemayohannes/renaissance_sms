<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\InventoryStockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryStockController extends Controller
{
    public function stockIn(Request $request, InventoryItem $item)
    {
        if ($item->kind !== 'consumable') {
            return back()->with('error', 'Stock movements only apply to consumable items.');
        }

        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
            'unit_cost' => 'nullable|numeric|min:0',
            'supplier' => 'nullable|string|max:255',
            'movement_date' => 'required|date|before_or_equal:today',
            'remarks' => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($item, $validated) {
            InventoryStockMovement::create($validated + [
                'inventory_item_id' => $item->id,
                'direction' => 'in',
                'recorded_by' => auth()->id(),
            ]);

            $item->increment('quantity', $validated['quantity']);
        });

        return back()->with('success', "Stock-in recorded (+{$validated['quantity']} {$item->unit}).");
    }

    public function stockOut(Request $request, InventoryItem $item)
    {
        if ($item->kind !== 'consumable') {
            return back()->with('error', 'Stock movements only apply to consumable items.');
        }

        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
            'issued_to_employee_id' => 'nullable|exists:employees,id',
            'issued_to' => 'nullable|string|max:255',
            'movement_date' => 'required|date|before_or_equal:today',
            'remarks' => 'nullable|string|max:500',
        ]);

        try {
            DB::transaction(function () use ($item, $validated) {
                // Lock the row so concurrent issues can't take the balance negative.
                $fresh = InventoryItem::whereKey($item->id)->lockForUpdate()->first();

                if ($fresh->quantity < $validated['quantity']) {
                    throw new \RuntimeException("Only {$fresh->quantity} {$fresh->unit} in stock — cannot issue {$validated['quantity']}.");
                }

                InventoryStockMovement::create($validated + [
                    'inventory_item_id' => $item->id,
                    'direction' => 'out',
                    'recorded_by' => auth()->id(),
                ]);

                $fresh->decrement('quantity', $validated['quantity']);
            });
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "Stock-out recorded (−{$validated['quantity']} {$item->unit}).");
    }
}
