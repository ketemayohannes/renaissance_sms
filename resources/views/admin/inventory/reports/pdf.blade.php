<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Inventory Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1e293b; }
        h1 { font-size: 16px; margin-bottom: 2px; }
        .meta { color: #64748b; font-size: 10px; margin-bottom: 14px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        th, td { border: 1px solid #cbd5e1; padding: 5px 7px; text-align: left; }
        th { background: #f1f5f9; font-size: 9px; text-transform: uppercase; letter-spacing: 0.05em; }
        h2 { font-size: 13px; margin: 14px 0 6px; }
        .num { text-align: right; }
        .low { color: #e11d48; font-weight: bold; }
    </style>
</head>
<body>
    @php
        $titles = [
            'low-stock' => 'Low Stock Report',
            'asset-register' => 'Asset Register',
            'assignments' => 'Active Assignments Register',
            'valuation' => 'Inventory Valuation',
        ];
    @endphp
    <h1>{{ $titles[$report] }}</h1>
    <div class="meta">Renaissance School — generated {{ $generatedAt->format('F j, Y g:i A') }}</div>

    @if($report === 'low-stock')
        <table>
            <thead>
                <tr><th>Item</th><th>Category</th><th>Unit</th><th class="num">In Stock</th><th class="num">Reorder Level</th><th class="num">Shortfall</th></tr>
            </thead>
            <tbody>
                @forelse($data['lowStock'] as $item)
                    <tr>
                        <td>{{ $item->name }}</td>
                        <td>{{ $item->category->name ?? '' }}</td>
                        <td>{{ $item->unit }}</td>
                        <td class="num low">{{ $item->quantity }}</td>
                        <td class="num">{{ $item->reorder_level }}</td>
                        <td class="num">{{ max(0, $item->reorder_level - $item->quantity) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6">No consumables are below their reorder level.</td></tr>
                @endforelse
            </tbody>
        </table>
    @elseif($report === 'asset-register')
        @forelse($data['assetRegister'] as $category)
            <h2>{{ $category->name }}</h2>
            <table>
                <thead>
                    <tr><th>Item</th><th>Tag</th><th>Serial</th><th>Condition</th><th>Status</th><th>Held By / Location</th><th class="num">Cost (ETB)</th></tr>
                </thead>
                <tbody>
                    @foreach($category->items as $item)
                        @foreach($item->assets as $asset)
                            <tr>
                                <td>{{ $item->name }}</td>
                                <td>{{ $asset->asset_tag }}</td>
                                <td>{{ $asset->serial_number ?? '—' }}</td>
                                <td>{{ str_replace('_', ' ', $asset->condition) }}</td>
                                <td>{{ str_replace('_', ' ', $asset->status) }}</td>
                                <td>{{ $asset->activeAssignment?->holder_label ?? '—' }}</td>
                                <td class="num">{{ $asset->unit_cost ? number_format($asset->unit_cost, 2) : '—' }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        @empty
            <p>No assets recorded.</p>
        @endforelse
    @elseif($report === 'assignments')
        <table>
            <thead>
                <tr><th>Asset</th><th>Tag</th><th>Held By / Location</th><th>Since</th><th>Assigned By</th><th>Notes</th></tr>
            </thead>
            <tbody>
                @forelse($data['assignments'] as $assignment)
                    <tr>
                        <td>{{ $assignment->asset->item->name ?? '' }}</td>
                        <td>{{ $assignment->asset->asset_tag ?? '' }}</td>
                        <td>{{ $assignment->holder_label }}</td>
                        <td>{{ $assignment->assigned_at->format('M j, Y') }}</td>
                        <td>{{ $assignment->assigner->name ?? '' }}</td>
                        <td>{{ $assignment->notes ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6">No active assignments.</td></tr>
                @endforelse
            </tbody>
        </table>
    @else
        <table>
            <thead>
                <tr><th>Component</th><th class="num">Value (ETB)</th></tr>
            </thead>
            <tbody>
                <tr>
                    <td>Assets ({{ $data['valuation']['asset_count'] }} non-retired units{{ $data['valuation']['assets_uncosted'] > 0 ? ', ' . $data['valuation']['assets_uncosted'] . ' without recorded cost' : '' }})</td>
                    <td class="num">{{ number_format($data['valuation']['assets'], 2) }}</td>
                </tr>
                <tr>
                    <td>Consumable stock (at last purchase cost)</td>
                    <td class="num">{{ number_format($data['valuation']['consumables'], 2) }}</td>
                </tr>
                <tr>
                    <th>Total</th>
                    <th class="num">{{ number_format($data['valuation']['assets'] + $data['valuation']['consumables'], 2) }}</th>
                </tr>
            </tbody>
        </table>
    @endif
</body>
</html>
