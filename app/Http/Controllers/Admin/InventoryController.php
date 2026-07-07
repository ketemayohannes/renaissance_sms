<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryAsset;
use App\Models\InventoryAssignment;
use App\Models\InventoryItem;
use App\Models\InventoryStockMovement;

class InventoryController extends Controller
{
    /**
     * Inventory dashboard: stock health + asset state at a glance. This is the
     * Inventory Manager's landing page after login.
     */
    public function dashboard()
    {
        $stats = [
            'items' => InventoryItem::where('is_active', true)->count(),
            'assets_total' => InventoryAsset::count(),
            'assets_assigned' => InventoryAsset::where('status', 'assigned')->count(),
            'assets_maintenance' => InventoryAsset::where('status', 'in_maintenance')->count(),
            'low_stock' => InventoryItem::lowStock()->where('is_active', true)->count(),
        ];

        $lowStockItems = InventoryItem::lowStock()
            ->where('is_active', true)
            ->with('category')
            ->orderByRaw('quantity - reorder_level')
            ->take(10)
            ->get();

        $inMaintenance = InventoryAsset::where('status', 'in_maintenance')
            ->with('item')
            ->orderByDesc('updated_at')
            ->take(10)
            ->get();

        $recentMovements = InventoryStockMovement::with(['item', 'recorder'])
            ->orderByDesc('movement_date')
            ->orderByDesc('id')
            ->take(10)
            ->get();

        $recentAssignments = InventoryAssignment::active()
            ->with(['asset.item', 'employee'])
            ->orderByDesc('assigned_at')
            ->take(10)
            ->get();

        return view('admin.inventory.dashboard', compact('stats', 'lowStockItems', 'inMaintenance', 'recentMovements', 'recentAssignments'));
    }
}
