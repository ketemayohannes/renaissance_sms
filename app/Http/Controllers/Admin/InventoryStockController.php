<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\InventoryStockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryStockController extends Controller
{
    public function __construct(private \App\Services\InventoryService $inventory) {}

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

        $this->inventory->receiveStock($item, $validated, auth()->id());

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
            $this->inventory->issueStock($item, $validated, auth()->id());
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "Stock-out recorded (−{$validated['quantity']} {$item->unit}).");
    }
}
