<x-admin-layout>
    <div class="space-y-8" x-data="{ addUnitOpen: false, stockInOpen: false, stockOutOpen: false, assignId: null }">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <x-breadcrumb :items="[
                    ['label' => 'Inventory', 'url' => route('admin.inventory.dashboard')],
                    ['label' => 'Items & Assets', 'url' => route('admin.inventory.items.index')],
                    ['label' => $item->name, 'url' => '#']
                ]" />
                <h1 class="text-4xl font-black text-slate-900 dark:text-slate-100 tracking-tight mt-2">{{ $item->name }}</h1>
                <p class="text-slate-500 dark:text-slate-400 font-semibold mt-1">
                    {{ $item->category->name ?? '' }} · {{ ucfirst($item->kind) }}
                    @if($item->kind === 'consumable')
                        · <span class="{{ $item->is_low_stock ? 'text-rose-600 dark:text-rose-400 font-black' : '' }}">{{ $item->quantity }} {{ $item->unit }} in stock</span>
                        @if($item->reorder_level !== null) (reorder at {{ $item->reorder_level }}) @endif
                    @endif
                </p>
            </div>
            @can('manage inventory')
            <div class="flex items-center gap-3">
                @if($item->kind === 'asset')
                    <button @click="addUnitOpen = !addUnitOpen" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl text-xs font-black uppercase tracking-widest transition-all shadow-md shadow-indigo-200 dark:shadow-none">+ Add Unit</button>
                @else
                    <button @click="stockInOpen = !stockInOpen; stockOutOpen = false" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl text-xs font-black uppercase tracking-widest transition-all">Stock In</button>
                    <button @click="stockOutOpen = !stockOutOpen; stockInOpen = false" class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-2xl text-xs font-black uppercase tracking-widest transition-all">Stock Out</button>
                @endif
            </div>
            @endcan
        </div>

        @if(session('success'))
            <div class="px-6 py-4 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-100 dark:border-emerald-900/30 rounded-2xl text-emerald-700 dark:text-emerald-300 font-bold text-sm">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="px-6 py-4 bg-rose-50 dark:bg-rose-950/40 border border-rose-100 dark:border-rose-800 rounded-2xl text-rose-700 dark:text-rose-400 font-bold text-sm">{{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="px-6 py-4 bg-rose-50 dark:bg-rose-950/40 border border-rose-100 dark:border-rose-800 rounded-2xl text-rose-700 dark:text-rose-400 font-bold text-sm">{{ $errors->first() }}</div>
        @endif

        @can('manage inventory')
        @if($item->kind === 'asset')
            <!-- Add unit form -->
            <div x-show="addUnitOpen" x-collapse x-cloak>
                <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl shadow-sm p-8">
                    <h2 class="text-lg font-black text-slate-800 dark:text-slate-100 mb-6">Add Physical Unit</h2>
                    <form method="POST" action="{{ route('admin.inventory.assets.store', $item) }}" class="grid grid-cols-1 md:grid-cols-3 xl:grid-cols-6 gap-4">
                        @csrf
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Asset Tag</label>
                            <input type="text" name="asset_tag" value="{{ old('asset_tag', $suggestedTag) }}" required maxlength="100" class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-950/50 dark:text-slate-200 text-sm font-bold font-mono">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Serial No.</label>
                            <input type="text" name="serial_number" maxlength="255" class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-950/50 dark:text-slate-200 text-sm font-bold font-mono">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Condition</label>
                            <select name="condition" class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-950/50 dark:text-slate-200 text-sm font-bold">
                                @foreach(\App\Models\InventoryAsset::CONDITIONS as $condition)
                                    <option value="{{ $condition }}">{{ ucfirst(str_replace('_', ' ', $condition)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Purchased</label>
                            <input type="date" name="purchase_date" max="{{ now()->toDateString() }}" class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-950/50 dark:text-slate-200 text-sm font-bold">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Cost (ETB)</label>
                            <input type="number" name="unit_cost" min="0" step="0.01" class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-950/50 dark:text-slate-200 text-sm font-bold">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Supplier</label>
                            <input type="text" name="supplier" maxlength="255" class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-950/50 dark:text-slate-200 text-sm font-bold">
                        </div>
                        <div class="md:col-span-3 xl:col-span-6 flex justify-end">
                            <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-black uppercase tracking-widest">Add Unit</button>
                        </div>
                    </form>
                </div>
            </div>
        @else
            <!-- Stock-in form -->
            <div x-show="stockInOpen" x-collapse x-cloak>
                <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl shadow-sm p-8">
                    <h2 class="text-lg font-black text-slate-800 dark:text-slate-100 mb-6">Record Stock In (Purchase / Donation)</h2>
                    <form method="POST" action="{{ route('admin.inventory.stock.in', $item) }}" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4">
                        @csrf
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Quantity ({{ $item->unit }})</label>
                            <input type="number" name="quantity" min="1" required class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-950/50 dark:text-slate-200 text-sm font-bold">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Unit Cost (ETB)</label>
                            <input type="number" name="unit_cost" min="0" step="0.01" class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-950/50 dark:text-slate-200 text-sm font-bold">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Supplier</label>
                            <input type="text" name="supplier" maxlength="255" class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-950/50 dark:text-slate-200 text-sm font-bold">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Date</label>
                            <input type="date" name="movement_date" value="{{ now()->toDateString() }}" max="{{ now()->toDateString() }}" required class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-950/50 dark:text-slate-200 text-sm font-bold">
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-black uppercase tracking-widest">Record</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Stock-out form -->
            <div x-show="stockOutOpen" x-collapse x-cloak>
                <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl shadow-sm p-8">
                    <h2 class="text-lg font-black text-slate-800 dark:text-slate-100 mb-6">Record Stock Out (Issue)</h2>
                    <form method="POST" action="{{ route('admin.inventory.stock.out', $item) }}" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4">
                        @csrf
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Quantity ({{ $item->unit }})</label>
                            <input type="number" name="quantity" min="1" max="{{ $item->quantity }}" required class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-950/50 dark:text-slate-200 text-sm font-bold">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Issued to Staff</label>
                            <select name="issued_to_employee_id" class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-950/50 dark:text-slate-200 text-sm font-bold">
                                <option value="">—</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Or Dept / Event</label>
                            <input type="text" name="issued_to" maxlength="255" placeholder="e.g. Grade 9 exams" class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-950/50 dark:text-slate-200 text-sm font-bold">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Date</label>
                            <input type="date" name="movement_date" value="{{ now()->toDateString() }}" max="{{ now()->toDateString() }}" required class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-950/50 dark:text-slate-200 text-sm font-bold">
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="px-6 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-black uppercase tracking-widest">Record</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
        @endcan

        @if($item->kind === 'asset')
            <!-- Units table -->
            <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-slate-50/50 dark:bg-slate-800/40 text-slate-400 dark:text-slate-500 uppercase tracking-widest text-[9px] font-black border-b border-slate-100 dark:border-slate-800">
                                <th class="text-left px-6 py-4">Tag</th>
                                <th class="text-left px-4 py-4">Serial</th>
                                <th class="text-center px-4 py-4">Condition</th>
                                <th class="text-center px-4 py-4">Status</th>
                                <th class="text-left px-4 py-4">Held By / Location</th>
                                @can('manage inventory')<th class="text-right px-6 py-4">Actions</th>@endcan
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse($assets as $asset)
                                @php
                                    $statusChip = match($asset->status) {
                                        'available' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border-emerald-100/50 dark:border-emerald-900/30',
                                        'assigned' => 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border-indigo-100/50 dark:border-indigo-900/30',
                                        'in_maintenance' => 'bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border-amber-100/50 dark:border-amber-900/30',
                                        'retired' => 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400 border-slate-200/50 dark:border-slate-700/30',
                                    };
                                @endphp
                                <tr class="hover:bg-slate-50/40 dark:hover:bg-slate-800/20 transition-all {{ $asset->status === 'retired' ? 'opacity-50' : '' }}">
                                    <td class="px-6 py-4 font-black font-mono text-slate-800 dark:text-slate-200">{{ $asset->asset_tag }}</td>
                                    <td class="px-4 py-4 font-mono text-xs text-slate-500 dark:text-slate-400">{{ $asset->serial_number ?? '—' }}</td>
                                    <td class="px-4 py-4 text-center font-bold text-slate-600 dark:text-slate-300">{{ ucfirst(str_replace('_', ' ', $asset->condition)) }}</td>
                                    <td class="px-4 py-4 text-center">
                                        <span class="px-3 py-1 rounded-xl text-[9px] font-black uppercase tracking-wider border {{ $statusChip }}">{{ str_replace('_', ' ', $asset->status) }}</span>
                                    </td>
                                    <td class="px-4 py-4 font-bold text-slate-600 dark:text-slate-300">{{ $asset->activeAssignment?->holder_label ?? '—' }}</td>
                                    @can('manage inventory')
                                    <td class="px-6 py-4 text-right">
                                        @if($asset->status === 'available')
                                            <div x-show="assignId !== {{ $asset->id }}" class="flex justify-end gap-2">
                                                <button @click="assignId = {{ $asset->id }}" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-[10px] font-black uppercase tracking-widest">Assign</button>
                                                <form method="POST" action="{{ route('admin.inventory.assets.status', $asset) }}">
                                                    @csrf
                                                    <input type="hidden" name="status" value="in_maintenance">
                                                    <button type="submit" class="px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-[10px] font-black uppercase tracking-widest">Maintenance</button>
                                                </form>
                                            </div>
                                            <div x-show="assignId === {{ $asset->id }}" x-cloak>
                                                <form method="POST" action="{{ route('admin.inventory.assets.assign', $asset) }}" class="flex items-center justify-end gap-2">
                                                    @csrf
                                                    <select name="employee_id" class="rounded-lg border-slate-200 dark:border-slate-700 dark:bg-slate-950/50 dark:text-slate-200 text-xs font-bold w-40">
                                                        <option value="">— Staff —</option>
                                                        @foreach($employees as $employee)
                                                            <option value="{{ $employee->id }}">{{ $employee->full_name }}</option>
                                                        @endforeach
                                                    </select>
                                                    <input type="text" name="location" placeholder="or location…" class="rounded-lg border-slate-200 dark:border-slate-700 dark:bg-slate-950/50 dark:text-slate-200 text-xs font-bold w-32">
                                                    <button type="submit" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-[10px] font-black uppercase tracking-widest">Confirm</button>
                                                    <button type="button" @click="assignId = null" class="px-2 py-1.5 text-slate-400 text-[10px] font-black uppercase">Cancel</button>
                                                </form>
                                            </div>
                                        @elseif($asset->status === 'assigned')
                                            <form method="POST" action="{{ route('admin.inventory.assets.return', $asset) }}" class="inline">
                                                @csrf
                                                <button type="submit" class="px-3 py-1.5 bg-slate-700 hover:bg-slate-800 text-white rounded-lg text-[10px] font-black uppercase tracking-widest">Return</button>
                                            </form>
                                        @elseif($asset->status === 'in_maintenance')
                                            <div class="flex justify-end gap-2">
                                                <form method="POST" action="{{ route('admin.inventory.assets.status', $asset) }}">
                                                    @csrf
                                                    <input type="hidden" name="status" value="available">
                                                    <input type="hidden" name="condition" value="good">
                                                    <button type="submit" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-[10px] font-black uppercase tracking-widest">Repaired</button>
                                                </form>
                                                <form method="POST" action="{{ route('admin.inventory.assets.status', $asset) }}">
                                                    @csrf
                                                    <input type="hidden" name="status" value="retired">
                                                    <button type="submit" class="px-3 py-1.5 bg-slate-500 hover:bg-slate-600 text-white rounded-lg text-[10px] font-black uppercase tracking-widest">Retire</button>
                                                </form>
                                            </div>
                                        @else
                                            <span class="text-[10px] font-bold text-slate-300 dark:text-slate-600 uppercase tracking-widest">Retired</span>
                                        @endif
                                    </td>
                                    @endcan
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-16 text-center text-slate-400 dark:text-slate-500 font-bold">No physical units yet — add the first one above.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <!-- Stock ledger -->
            <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 bg-slate-50/50 dark:bg-slate-800/40 border-b border-slate-100 dark:border-slate-800">
                    <span class="text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest">Stock Ledger</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-slate-400 dark:text-slate-500 uppercase tracking-widest text-[9px] font-black border-b border-slate-100 dark:border-slate-800">
                                <th class="text-left px-6 py-4">Date</th>
                                <th class="text-center px-4 py-4">Direction</th>
                                <th class="text-center px-4 py-4">Qty</th>
                                <th class="text-left px-4 py-4">Supplier / Issued To</th>
                                <th class="text-left px-4 py-4">Recorded By</th>
                                <th class="text-left px-6 py-4">Remarks</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse($movements as $movement)
                                <tr class="hover:bg-slate-50/40 dark:hover:bg-slate-800/20 transition-all">
                                    <td class="px-6 py-4 font-bold text-slate-600 dark:text-slate-300">{{ $movement->movement_date->format('M j, Y') }}</td>
                                    <td class="px-4 py-4 text-center">
                                        <span class="px-3 py-1 rounded-xl text-[9px] font-black uppercase tracking-wider border {{ $movement->direction === 'in' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border-emerald-100/50 dark:border-emerald-900/30' : 'bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 border-rose-100/50 dark:border-rose-900/30' }}">
                                            {{ $movement->direction === 'in' ? 'Stock In' : 'Stock Out' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 text-center font-black {{ $movement->direction === 'in' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                        {{ $movement->direction === 'in' ? '+' : '−' }}{{ $movement->quantity }}
                                    </td>
                                    <td class="px-4 py-4 font-bold text-slate-600 dark:text-slate-300">
                                        {{ $movement->direction === 'in' ? ($movement->supplier ?? '—') : ($movement->issuedToEmployee->full_name ?? $movement->issued_to ?? '—') }}
                                    </td>
                                    <td class="px-4 py-4 text-slate-500 dark:text-slate-400">{{ $movement->recorder->name ?? '—' }}</td>
                                    <td class="px-6 py-4 text-slate-500 dark:text-slate-400 max-w-[200px] truncate" title="{{ $movement->remarks }}">{{ $movement->remarks ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-16 text-center text-slate-400 dark:text-slate-500 font-bold">No stock movements yet — record a stock-in to set the opening balance.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($movements)
                {{ $movements->links() }}
            @endif
        @endif
    </div>
</x-admin-layout>
