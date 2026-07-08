<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryCategory;
use App\Models\InventoryItem;
use Illuminate\Http\Request;

class InventoryItemController extends Controller
{
    public function index(Request $request)
    {
        $items = InventoryItem::with('category')
            ->withCount([
                'assets',
                'assets as available_assets_count' => fn ($q) => $q->where('status', 'available'),
            ])
            ->when($request->filled('category'), fn ($q) => $q->where('inventory_category_id', $request->category))
            ->when($request->filled('kind'), fn ($q) => $q->where('kind', $request->kind))
            ->when($request->boolean('low_stock'), fn ($q) => $q->lowStock())
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->when(! $request->boolean('include_inactive'), fn ($q) => $q->where('is_active', true))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $categories = InventoryCategory::orderBy('name')->get();

        return view('admin.inventory.items.index', compact('items', 'categories'));
    }

    public function show(InventoryItem $item)
    {
        $item->load('category');

        $assets = $item->kind === 'asset'
            ? $item->assets()->with('activeAssignment.employee')->orderBy('asset_tag')->get()
            : collect();

        $movements = $item->kind === 'consumable'
            ? $item->stockMovements()->with(['recorder', 'issuedToEmployee'])->orderByDesc('movement_date')->orderByDesc('id')->paginate(20)
            : null;

        $employees = \App\Models\Employee::where('status', 'active')
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'middle_name', 'last_name', 'employee_id']);

        $suggestedTag = $item->kind === 'asset' ? \App\Models\InventoryAsset::suggestTag($item) : null;

        return view('admin.inventory.items.show', compact('item', 'assets', 'movements', 'employees', 'suggestedTag'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'inventory_category_id' => 'required|exists:inventory_categories,id',
            'name' => 'required|string|max:255',
            'kind' => 'required|in:' . implode(',', InventoryItem::KINDS),
            'unit' => 'nullable|string|max:50',
            'reorder_level' => 'nullable|integer|min:0',
            'description' => 'nullable|string|max:1000',
        ]);

        $item = InventoryItem::create($validated + ['quantity' => 0, 'is_active' => true]);

        return redirect()->route('admin.inventory.items.show', $item)
            ->with('success', 'Item created. ' . ($item->kind === 'asset' ? 'Add its physical units below.' : 'Record stock-in to set its starting quantity.'));
    }

    public function update(Request $request, InventoryItem $item)
    {
        $validated = $request->validate([
            'inventory_category_id' => 'required|exists:inventory_categories,id',
            'name' => 'required|string|max:255',
            'unit' => 'nullable|string|max:50',
            'reorder_level' => 'nullable|integer|min:0',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        // 'kind' is deliberately not updatable: switching asset<->consumable would orphan
        // existing units or the stock ledger.
        $item->update($validated + ['is_active' => $request->boolean('is_active', true)]);

        return back()->with('success', 'Item updated.');
    }

    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:inventory_categories,name',
            'description' => 'nullable|string|max:500',
        ]);

        InventoryCategory::create($validated);

        return back()->with('success', 'Category added.');
    }
}
