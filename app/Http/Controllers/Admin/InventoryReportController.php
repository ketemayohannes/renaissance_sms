<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryAsset;
use App\Models\InventoryAssignment;
use App\Models\InventoryCategory;
use App\Models\InventoryItem;

class InventoryReportController extends Controller
{
    public const REPORTS = ['low-stock', 'asset-register', 'assignments', 'valuation'];

    public function index()
    {
        return view('admin.inventory.reports.index', [
            'data' => $this->buildAll(),
        ]);
    }

    public function pdf(string $report)
    {
        abort_unless(in_array($report, self::REPORTS), 404);

        $data = $this->buildAll();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.inventory.reports.pdf', [
            'report' => $report,
            'data' => $data,
            'generatedAt' => now(),
        ])->setPaper('a4', $report === 'asset-register' ? 'landscape' : 'portrait');

        return $pdf->download("inventory-{$report}-" . now()->format('Y-m-d') . '.pdf');
    }

    private function buildAll(): array
    {
        return [
            'lowStock' => InventoryItem::lowStock()
                ->where('is_active', true)
                ->with('category')
                ->orderBy('name')
                ->get(),

            'assetRegister' => InventoryCategory::with([
                'items' => fn ($q) => $q->where('kind', 'asset')->orderBy('name'),
                'items.assets' => fn ($q) => $q->orderBy('asset_tag'),
                'items.assets.activeAssignment.employee',
            ])->orderBy('name')->get()
                ->filter(fn ($category) => $category->items->isNotEmpty()),

            'assignments' => InventoryAssignment::active()
                ->with(['asset.item', 'employee', 'assigner'])
                ->orderByDesc('assigned_at')
                ->get(),

            // Valuation: sum of asset unit costs + consumable stock at latest known unit cost.
            'valuation' => [
                'assets' => InventoryAsset::whereNotNull('unit_cost')->where('status', '!=', 'retired')->sum('unit_cost'),
                'asset_count' => InventoryAsset::where('status', '!=', 'retired')->count(),
                'assets_uncosted' => InventoryAsset::whereNull('unit_cost')->where('status', '!=', 'retired')->count(),
                'consumables' => InventoryItem::where('kind', 'consumable')->where('is_active', true)->get()
                    ->sum(function (InventoryItem $item) {
                        $lastCost = $item->stockMovements()
                            ->where('direction', 'in')
                            ->whereNotNull('unit_cost')
                            ->orderByDesc('movement_date')
                            ->value('unit_cost');

                        return $lastCost ? $item->quantity * (float) $lastCost : 0;
                    }),
            ],
        ];
    }
}
