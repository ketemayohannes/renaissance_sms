<x-admin-layout>
    <div class="space-y-8" x-data="{ decideId: null, decideAction: null, stage: null }">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <x-breadcrumb :items="[
                    ['label' => 'Inventory', 'url' => route('admin.inventory.dashboard')],
                    ['label' => 'Purchase Approvals', 'url' => '#']
                ]" />
                <h1 class="text-4xl font-black text-slate-900 dark:text-slate-100 tracking-tight mt-2">Purchase Approvals</h1>
                <p class="text-slate-500 dark:text-slate-400 font-semibold mt-1">Principal approves first, then the General Manager makes the final call.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.inventory.purchases.list') }}" class="px-4 py-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 rounded-2xl text-xs font-black uppercase tracking-widest">Purchase List</a>
                <a href="{{ route('admin.inventory.purchases.declined') }}" class="px-4 py-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 rounded-2xl text-xs font-black uppercase tracking-widest">Decline List</a>
            </div>
        </div>

        @if(session('success'))
            <div class="px-6 py-4 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-100 dark:border-emerald-900/30 rounded-2xl text-emerald-700 dark:text-emerald-300 font-bold text-sm">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="px-6 py-4 bg-rose-50 dark:bg-rose-950/40 border border-rose-100 dark:border-rose-800 rounded-2xl text-rose-700 dark:text-rose-400 font-bold text-sm">{{ session('error') }}</div>
        @endif

        @can('approve inventory requests')
        <!-- Stage 1: Principal -->
        <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 bg-slate-50/50 dark:bg-slate-800/40 border-b border-slate-100 dark:border-slate-800">
                <span class="text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest">Awaiting Principal ({{ $awaitingPrincipal->count() }})</span>
            </div>
            @include('admin.inventory.purchases._queue', ['rows' => $awaitingPrincipal, 'stage' => 'principal', 'action' => 'admin.inventory.purchases.principal'])
        </div>
        @endcan

        @can('approve inventory purchases')
        <!-- Stage 2: General Manager -->
        <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 bg-slate-50/50 dark:bg-slate-800/40 border-b border-slate-100 dark:border-slate-800">
                <span class="text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest">Awaiting General Manager ({{ $awaitingGm->count() }})</span>
            </div>
            @include('admin.inventory.purchases._queue', ['rows' => $awaitingGm, 'stage' => 'gm', 'action' => 'admin.inventory.purchases.gm'])
        </div>
        @endcan
    </div>
</x-admin-layout>
