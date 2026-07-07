<x-admin-layout>
    <div class="space-y-8">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <x-breadcrumb :items="[
                    ['label' => 'Inventory', 'url' => '#'],
                    ['label' => 'Dashboard', 'url' => '#']
                ]" />
                <h1 class="text-4xl font-black text-slate-900 dark:text-slate-100 tracking-tight mt-2">Inventory Dashboard</h1>
                <p class="text-slate-500 dark:text-slate-400 font-semibold mt-1">Assets, supplies, and stock health at a glance.</p>
            </div>
            @can('manage inventory')
            <a href="{{ route('admin.inventory.items.index') }}" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl text-xs font-black uppercase tracking-widest transition-all shadow-md shadow-indigo-200 dark:shadow-none">
                Manage Items
            </a>
            @endcan
        </div>

        <!-- Stat cards -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            @foreach([
                ['label' => 'Active Items', 'value' => $stats['items'], 'accent' => 'text-slate-900 dark:text-slate-100'],
                ['label' => 'Asset Units', 'value' => $stats['assets_total'], 'accent' => 'text-slate-900 dark:text-slate-100'],
                ['label' => 'Assigned', 'value' => $stats['assets_assigned'], 'accent' => 'text-indigo-600 dark:text-indigo-400'],
                ['label' => 'In Maintenance', 'value' => $stats['assets_maintenance'], 'accent' => 'text-amber-600 dark:text-amber-400'],
                ['label' => 'Low Stock', 'value' => $stats['low_stock'], 'accent' => $stats['low_stock'] > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400'],
            ] as $card)
                <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl shadow-sm p-6">
                    <span class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">{{ $card['label'] }}</span>
                    <span class="text-3xl font-black {{ $card['accent'] }}">{{ $card['value'] }}</span>
                </div>
            @endforeach
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
            <!-- Low stock -->
            <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 bg-slate-50/50 dark:bg-slate-800/40 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <span class="text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest">Low Stock</span>
                    <a href="{{ route('admin.inventory.items.index', ['low_stock' => 1]) }}" class="text-[10px] font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-widest">View all</a>
                </div>
                <div class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($lowStockItems as $item)
                        <a href="{{ route('admin.inventory.items.show', $item) }}" class="flex items-center justify-between px-6 py-3 hover:bg-slate-50/40 dark:hover:bg-slate-800/20 transition-all">
                            <div>
                                <span class="font-extrabold text-slate-800 dark:text-slate-200 text-sm">{{ $item->name }}</span>
                                <span class="block text-[10px] font-bold text-slate-400">{{ $item->category->name ?? '' }}</span>
                            </div>
                            <span class="px-3 py-1 rounded-xl text-[10px] font-black bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-100/50 dark:border-rose-900/30">
                                {{ $item->quantity }} / {{ $item->reorder_level }} {{ $item->unit }}
                            </span>
                        </a>
                    @empty
                        <p class="px-6 py-8 text-center text-sm font-bold text-emerald-600 dark:text-emerald-400">All consumables above reorder level.</p>
                    @endforelse
                </div>
            </div>

            <!-- In maintenance -->
            <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 bg-slate-50/50 dark:bg-slate-800/40 border-b border-slate-100 dark:border-slate-800">
                    <span class="text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest">In Maintenance</span>
                </div>
                <div class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($inMaintenance as $asset)
                        <a href="{{ route('admin.inventory.items.show', $asset->inventory_item_id) }}" class="flex items-center justify-between px-6 py-3 hover:bg-slate-50/40 dark:hover:bg-slate-800/20 transition-all">
                            <div>
                                <span class="font-extrabold text-slate-800 dark:text-slate-200 text-sm">{{ $asset->item->name ?? '' }} — {{ $asset->asset_tag }}</span>
                                <span class="block text-[10px] font-bold text-slate-400">Condition: {{ str_replace('_', ' ', $asset->condition) }}</span>
                            </div>
                            <span class="px-3 py-1 rounded-xl text-[10px] font-black bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-100/50 dark:border-amber-900/30 uppercase">Repairing</span>
                        </a>
                    @empty
                        <p class="px-6 py-8 text-center text-sm font-bold text-slate-400 dark:text-slate-500">No units in maintenance.</p>
                    @endforelse
                </div>
            </div>

            <!-- Recent stock movements -->
            <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 bg-slate-50/50 dark:bg-slate-800/40 border-b border-slate-100 dark:border-slate-800">
                    <span class="text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest">Recent Stock Movements</span>
                </div>
                <div class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($recentMovements as $movement)
                        <div class="flex items-center justify-between px-6 py-3">
                            <div>
                                <span class="font-extrabold text-slate-800 dark:text-slate-200 text-sm">{{ $movement->item->name ?? '' }}</span>
                                <span class="block text-[10px] font-bold text-slate-400">{{ $movement->movement_date->format('M j, Y') }} · {{ $movement->recorder->name ?? '' }}</span>
                            </div>
                            <span class="font-black text-sm {{ $movement->direction === 'in' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                {{ $movement->direction === 'in' ? '+' : '−' }}{{ $movement->quantity }}
                            </span>
                        </div>
                    @empty
                        <p class="px-6 py-8 text-center text-sm font-bold text-slate-400 dark:text-slate-500">No stock movements yet.</p>
                    @endforelse
                </div>
            </div>

            <!-- Recent assignments -->
            <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 bg-slate-50/50 dark:bg-slate-800/40 border-b border-slate-100 dark:border-slate-800">
                    <span class="text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest">Currently Assigned</span>
                </div>
                <div class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($recentAssignments as $assignment)
                        <div class="flex items-center justify-between px-6 py-3">
                            <div>
                                <span class="font-extrabold text-slate-800 dark:text-slate-200 text-sm">{{ $assignment->asset->item->name ?? '' }} — {{ $assignment->asset->asset_tag ?? '' }}</span>
                                <span class="block text-[10px] font-bold text-slate-400">{{ $assignment->assigned_at->format('M j, Y') }}</span>
                            </div>
                            <span class="text-xs font-bold text-slate-600 dark:text-slate-300">{{ $assignment->holder_label }}</span>
                        </div>
                    @empty
                        <p class="px-6 py-8 text-center text-sm font-bold text-slate-400 dark:text-slate-500">No active assignments.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
