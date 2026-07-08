<x-admin-layout>
    <div class="space-y-8">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <x-breadcrumb :items="[
                    ['label' => 'Inventory', 'url' => route('admin.inventory.dashboard')],
                    ['label' => 'Reports', 'url' => '#']
                ]" />
                <h1 class="text-4xl font-black text-slate-900 dark:text-slate-100 tracking-tight mt-2">Inventory Reports</h1>
                <p class="text-slate-500 dark:text-slate-400 font-semibold mt-1">Low stock, asset register, active assignments, and valuation.</p>
            </div>
        </div>

        <!-- Valuation summary -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl shadow-sm p-6">
                <span class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Asset Value (costed, non-retired)</span>
                <span class="text-3xl font-black text-slate-900 dark:text-slate-100">{{ number_format($data['valuation']['assets'], 2) }} <span class="text-sm font-bold text-slate-400">ETB</span></span>
                @if($data['valuation']['assets_uncosted'] > 0)
                    <span class="block text-[10px] font-bold text-amber-500 mt-1">{{ $data['valuation']['assets_uncosted'] }} unit(s) have no recorded cost</span>
                @endif
            </div>
            <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl shadow-sm p-6">
                <span class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Consumable Stock Value (last-cost)</span>
                <span class="text-3xl font-black text-slate-900 dark:text-slate-100">{{ number_format($data['valuation']['consumables'], 2) }} <span class="text-sm font-bold text-slate-400">ETB</span></span>
            </div>
            <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl shadow-sm p-6">
                <span class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Active Asset Units</span>
                <span class="text-3xl font-black text-slate-900 dark:text-slate-100">{{ $data['valuation']['asset_count'] }}</span>
            </div>
        </div>

        <!-- Report cards with PDF downloads -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl shadow-sm p-8">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-black text-slate-800 dark:text-slate-100">Low Stock ({{ $data['lowStock']->count() }})</h2>
                    <a href="{{ route('admin.inventory.reports.pdf', 'low-stock') }}" class="px-4 py-2 bg-slate-800 dark:bg-slate-700 text-white rounded-xl text-[10px] font-black uppercase tracking-widest">Download PDF</a>
                </div>
                <p class="text-sm text-slate-500 dark:text-slate-400 font-semibold">Consumables at or below their reorder level — the purchasing list.</p>
            </div>
            <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl shadow-sm p-8">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-black text-slate-800 dark:text-slate-100">Asset Register</h2>
                    <a href="{{ route('admin.inventory.reports.pdf', 'asset-register') }}" class="px-4 py-2 bg-slate-800 dark:bg-slate-700 text-white rounded-xl text-[10px] font-black uppercase tracking-widest">Download PDF</a>
                </div>
                <p class="text-sm text-slate-500 dark:text-slate-400 font-semibold">Every physical unit by category, with tag, condition, status, and holder.</p>
            </div>
            <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl shadow-sm p-8">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-black text-slate-800 dark:text-slate-100">Active Assignments ({{ $data['assignments']->count() }})</h2>
                    <a href="{{ route('admin.inventory.reports.pdf', 'assignments') }}" class="px-4 py-2 bg-slate-800 dark:bg-slate-700 text-white rounded-xl text-[10px] font-black uppercase tracking-widest">Download PDF</a>
                </div>
                <p class="text-sm text-slate-500 dark:text-slate-400 font-semibold">Who currently holds which asset, and since when — the accountability register.</p>
            </div>
            <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl shadow-sm p-8">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-black text-slate-800 dark:text-slate-100">Valuation</h2>
                    <a href="{{ route('admin.inventory.reports.pdf', 'valuation') }}" class="px-4 py-2 bg-slate-800 dark:bg-slate-700 text-white rounded-xl text-[10px] font-black uppercase tracking-widest">Download PDF</a>
                </div>
                <p class="text-sm text-slate-500 dark:text-slate-400 font-semibold">Total recorded value of assets plus consumable stock at last purchase cost.</p>
            </div>
        </div>
    </div>
</x-admin-layout>
