<x-admin-layout>
    <div class="space-y-8">
        <div>
            <x-breadcrumb :items="[
                ['label' => 'Inventory', 'url' => route('admin.inventory.dashboard')],
                ['label' => 'Purchase Approvals', 'url' => route('admin.inventory.purchases.index')],
                ['label' => 'Purchase List', 'url' => '#']
            ]" />
            <h1 class="text-4xl font-black text-slate-900 dark:text-slate-100 tracking-tight mt-2">Purchase List</h1>
            <p class="text-slate-500 dark:text-slate-400 font-semibold mt-1">Fully approved — cleared to be bought.</p>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-slate-800/40 text-slate-400 dark:text-slate-500 uppercase tracking-widest text-[9px] font-black border-b border-slate-100 dark:border-slate-800">
                        <th class="text-left px-6 py-4">Item</th><th class="text-center px-4 py-4">Qty</th><th class="text-left px-4 py-4">Est. Total</th><th class="text-left px-4 py-4">Requested By</th><th class="text-left px-4 py-4">Approved</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($requests as $req)
                        <tr class="hover:bg-slate-50/40 dark:hover:bg-slate-800/20 transition-all">
                            <td class="px-6 py-4">
                                <span class="font-extrabold text-slate-800 dark:text-slate-200">{{ $req->item_name }}</span>
                                @if($req->category)<span class="block text-[10px] font-bold text-slate-400">{{ $req->category->name }}</span>@endif
                            </td>
                            <td class="px-4 py-4 text-center font-black text-slate-800 dark:text-slate-200">{{ $req->quantity }} {{ $req->unit }}</td>
                            <td class="px-4 py-4 font-bold text-slate-600 dark:text-slate-300">{{ $req->estimated_total ? number_format($req->estimated_total, 2) . ' ETB' : '—' }}</td>
                            <td class="px-4 py-4 text-slate-500 dark:text-slate-400">{{ $req->requester->name ?? '—' }}</td>
                            <td class="px-4 py-4 text-slate-500 dark:text-slate-400">
                                {{ optional($req->gm_decided_at)->format('M j, Y') }}
                                <span class="block text-[10px] font-bold text-slate-400">P: {{ $req->principal->name ?? '' }} · GM: {{ $req->generalManager->name ?? '' }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-16 text-center text-slate-400 dark:text-slate-500 font-bold">Purchase list is empty.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $requests->links() }}
    </div>
</x-admin-layout>
