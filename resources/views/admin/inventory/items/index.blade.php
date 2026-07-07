<x-admin-layout>
    <div class="space-y-8" x-data="{ createItemOpen: false, createCategoryOpen: false }">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <x-breadcrumb :items="[
                    ['label' => 'Inventory', 'url' => route('admin.inventory.dashboard')],
                    ['label' => 'Items & Assets', 'url' => '#']
                ]" />
                <h1 class="text-4xl font-black text-slate-900 dark:text-slate-100 tracking-tight mt-2">Items & Assets</h1>
                <p class="text-slate-500 dark:text-slate-400 font-semibold mt-1">The full catalog — durable assets and consumable supplies.</p>
            </div>
            @can('manage inventory')
            <div class="flex items-center gap-3">
                <button @click="createCategoryOpen = !createCategoryOpen; createItemOpen = false" class="px-5 py-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 rounded-2xl text-xs font-black uppercase tracking-widest transition-all">
                    + Category
                </button>
                <button @click="createItemOpen = !createItemOpen; createCategoryOpen = false" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl text-xs font-black uppercase tracking-widest transition-all shadow-md shadow-indigo-200 dark:shadow-none">
                    + New Item
                </button>
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
        <!-- New category form -->
        <div x-show="createCategoryOpen" x-collapse x-cloak>
            <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl shadow-sm p-8">
                <h2 class="text-lg font-black text-slate-800 dark:text-slate-100 mb-6">New Category</h2>
                <form method="POST" action="{{ route('admin.inventory.categories.store') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @csrf
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Name</label>
                        <input type="text" name="name" required maxlength="255" class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-950/50 dark:text-slate-200 text-sm font-bold">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Description</label>
                        <input type="text" name="description" maxlength="500" class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-950/50 dark:text-slate-200 text-sm font-bold">
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-black uppercase tracking-widest">Save Category</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- New item form -->
        <div x-show="createItemOpen" x-collapse x-cloak>
            <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl shadow-sm p-8" x-data="{ kind: 'asset' }">
                <h2 class="text-lg font-black text-slate-800 dark:text-slate-100 mb-6">New Item</h2>
                <form method="POST" action="{{ route('admin.inventory.items.store') }}" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-6 gap-4">
                    @csrf
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Category</label>
                        <select name="inventory_category_id" required class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-950/50 dark:text-slate-200 text-sm font-bold">
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="xl:col-span-2">
                        <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Name</label>
                        <input type="text" name="name" required maxlength="255" placeholder="e.g. HP Laptop / A4 Paper" class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-950/50 dark:text-slate-200 text-sm font-bold">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Kind</label>
                        <select name="kind" x-model="kind" class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-950/50 dark:text-slate-200 text-sm font-bold">
                            <option value="asset">Asset (per-unit)</option>
                            <option value="consumable">Consumable (quantity)</option>
                        </select>
                    </div>
                    <div x-show="kind === 'consumable'">
                        <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Unit</label>
                        <input type="text" name="unit" maxlength="50" placeholder="pcs / box / ream" class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-950/50 dark:text-slate-200 text-sm font-bold">
                    </div>
                    <div x-show="kind === 'consumable'">
                        <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Reorder Level</label>
                        <input type="number" name="reorder_level" min="0" placeholder="e.g. 10" class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-950/50 dark:text-slate-200 text-sm font-bold">
                    </div>
                    <div class="md:col-span-2 xl:col-span-6 flex justify-end">
                        <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-black uppercase tracking-widest">Create Item</button>
                    </div>
                </form>
            </div>
        </div>
        @endcan

        <!-- Filters -->
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Category</label>
                <select name="category" onchange="this.form.submit()" class="rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-950/50 dark:text-slate-200 text-sm font-bold">
                    <option value="">All</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Kind</label>
                <select name="kind" onchange="this.form.submit()" class="rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-950/50 dark:text-slate-200 text-sm font-bold">
                    <option value="">All</option>
                    <option value="asset" {{ request('kind') === 'asset' ? 'selected' : '' }}>Assets</option>
                    <option value="consumable" {{ request('kind') === 'consumable' ? 'selected' : '' }}>Consumables</option>
                </select>
            </div>
            <label class="flex items-center gap-2 pb-2.5">
                <input type="checkbox" name="low_stock" value="1" onchange="this.form.submit()" {{ request('low_stock') ? 'checked' : '' }} class="rounded border-slate-300 dark:border-slate-600 text-indigo-600">
                <span class="text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest">Low stock only</span>
            </label>
            <div>
                <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Item name…" class="rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-950/50 dark:text-slate-200 text-sm font-bold">
            </div>
            <button type="submit" class="px-5 py-2.5 bg-slate-800 dark:bg-slate-700 text-white rounded-xl text-xs font-black uppercase tracking-widest">Filter</button>
        </form>

        <!-- Items table -->
        <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50/50 dark:bg-slate-800/40 text-slate-400 dark:text-slate-500 uppercase tracking-widest text-[9px] font-black border-b border-slate-100 dark:border-slate-800">
                            <th class="text-left px-6 py-4">Item</th>
                            <th class="text-left px-4 py-4">Category</th>
                            <th class="text-center px-4 py-4">Kind</th>
                            <th class="text-center px-4 py-4">Stock / Units</th>
                            <th class="text-center px-4 py-4">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($items as $item)
                            <tr class="hover:bg-slate-50/40 dark:hover:bg-slate-800/20 transition-all cursor-pointer" onclick="window.location='{{ route('admin.inventory.items.show', $item) }}'">
                                <td class="px-6 py-4">
                                    <span class="font-extrabold text-slate-800 dark:text-slate-200">{{ $item->name }}</span>
                                    @unless($item->is_active)
                                        <span class="ml-2 px-2 py-0.5 rounded text-[9px] font-black bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400 uppercase">Inactive</span>
                                    @endunless
                                </td>
                                <td class="px-4 py-4 font-bold text-slate-500 dark:text-slate-400">{{ $item->category->name ?? '—' }}</td>
                                <td class="px-4 py-4 text-center">
                                    <span class="px-3 py-1 rounded-xl text-[9px] font-black uppercase tracking-wider border {{ $item->kind === 'asset' ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border-indigo-100/50 dark:border-indigo-900/30' : 'bg-teal-50 text-teal-700 dark:bg-teal-950/60 dark:text-teal-300 border-teal-100/50 dark:border-teal-900/30' }}">{{ $item->kind }}</span>
                                </td>
                                <td class="px-4 py-4 text-center font-black text-slate-800 dark:text-slate-200">
                                    @if($item->kind === 'asset')
                                        {{ $item->available_assets_count }} <span class="text-[10px] font-bold text-slate-400">available / {{ $item->assets_count }}</span>
                                    @else
                                        {{ $item->quantity }} <span class="text-[10px] font-bold text-slate-400">{{ $item->unit }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-center">
                                    @if($item->is_low_stock)
                                        <span class="px-3 py-1 rounded-xl text-[9px] font-black uppercase tracking-wider bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-100/50 dark:border-rose-900/30">Low Stock</span>
                                    @else
                                        <span class="px-3 py-1 rounded-xl text-[9px] font-black uppercase tracking-wider bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-100/50 dark:border-emerald-900/30">OK</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-16 text-center text-slate-400 dark:text-slate-500 font-bold">No items found. Create the first one to get started.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{ $items->links() }}
    </div>
</x-admin-layout>
