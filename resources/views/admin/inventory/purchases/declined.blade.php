<x-admin-layout>
    <div class="space-y-8">
        <div>
            <x-breadcrumb :items="[
                ['label' => 'Inventory', 'url' => route('admin.inventory.dashboard')],
                ['label' => 'Purchase Approvals', 'url' => route('admin.inventory.purchases.index')],
                ['label' => 'Decline List', 'url' => '#']
            ]" />
            <h1 class="text-4xl font-black text-slate-900 dark:text-slate-100 tracking-tight mt-2">Decline List</h1>
            <p class="text-slate-500 dark:text-slate-400 font-semibold mt-1">Declined at the Principal or General Manager stage, with the reason.</p>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-slate-800/40 text-slate-400 dark:text-slate-500 uppercase tracking-widest text-[9px] font-black border-b border-slate-100 dark:border-slate-800">
                        <th class="text-left px-6 py-4">Item</th><th class="text-center px-4 py-4">Qty</th><th class="text-left px-4 py-4">Requested By</th><th class="text-center px-4 py-4">Declined At</th><th class="text-left px-6 py-4">Comment</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($requests as $req)
                        <tr class="hover:bg-slate-50/40 dark:hover:bg-slate-800/20 transition-all">
                            <td class="px-6 py-4 font-extrabold text-slate-800 dark:text-slate-200">{{ $req->item_name }}</td>
                            <td class="px-4 py-4 text-center font-black text-slate-800 dark:text-slate-200">{{ $req->quantity }} {{ $req->unit }}</td>
                            <td class="px-4 py-4 text-slate-500 dark:text-slate-400">{{ $req->requester->name ?? '—' }}</td>
                            <td class="px-4 py-4 text-center">
                                <span class="px-3 py-1 rounded-xl text-[9px] font-black uppercase tracking-wider border {{ $req->status === 'principal_declined' ? 'bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border-amber-100/50 dark:border-amber-900/30' : 'bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 border-rose-100/50 dark:border-rose-900/30' }}">
                                    {{ $req->status === 'principal_declined' ? 'Principal' : 'General Manager' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-slate-600 dark:text-slate-300 max-w-[320px]">{{ $req->status === 'principal_declined' ? $req->principal_remarks : $req->gm_remarks }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-16 text-center text-slate-400 dark:text-slate-500 font-bold">No declined purchase requests.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $requests->links() }}
    </div>
</x-admin-layout>
