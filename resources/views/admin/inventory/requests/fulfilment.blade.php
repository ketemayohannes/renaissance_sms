<x-admin-layout>
    <div class="space-y-8">
        <div>
            <x-breadcrumb :items="[
                ['label' => 'Inventory', 'url' => route('admin.inventory.dashboard')],
                ['label' => 'Fulfilment', 'url' => '#']
            ]" />
            <h1 class="text-4xl font-black text-slate-900 dark:text-slate-100 tracking-tight mt-2">Ready for Collection</h1>
            <p class="text-slate-500 dark:text-slate-400 font-semibold mt-1">Approved requests awaiting hand-over. Fulfilling issues stock (consumables) or assigns a unit (assets).</p>
        </div>

        @if(session('success'))
            <div class="px-6 py-4 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-100 dark:border-emerald-900/30 rounded-2xl text-emerald-700 dark:text-emerald-300 font-bold text-sm">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="px-6 py-4 bg-rose-50 dark:bg-rose-950/40 border border-rose-100 dark:border-rose-800 rounded-2xl text-rose-700 dark:text-rose-400 font-bold text-sm">{{ session('error') }}</div>
        @endif

        <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-slate-800/40 text-slate-400 dark:text-slate-500 uppercase tracking-widest text-[9px] font-black border-b border-slate-100 dark:border-slate-800">
                        <th class="text-left px-6 py-4">Requester</th><th class="text-left px-4 py-4">Item</th><th class="text-center px-4 py-4">Qty</th><th class="text-center px-4 py-4">Kind</th><th class="text-center px-4 py-4">Available</th><th class="text-right px-6 py-4"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($requests as $req)
                        @php
                            $item = $req->item;
                            $available = $item->kind === 'consumable' ? $item->quantity : $item->assets()->where('status', 'available')->count();
                            $canFulfil = $item->kind === 'consumable' ? $available >= $req->quantity : $available >= 1;
                        @endphp
                        <tr class="hover:bg-slate-50/40 dark:hover:bg-slate-800/20 transition-all">
                            <td class="px-6 py-4 font-extrabold text-slate-800 dark:text-slate-200">{{ $req->requester->full_name ?? '—' }}</td>
                            <td class="px-4 py-4 font-bold text-slate-600 dark:text-slate-300">{{ $item->name ?? '—' }}</td>
                            <td class="px-4 py-4 text-center font-black text-slate-800 dark:text-slate-200">{{ $req->quantity }}</td>
                            <td class="px-4 py-4 text-center font-bold text-slate-500">{{ $item->kind }}</td>
                            <td class="px-4 py-4 text-center font-black {{ $canFulfil ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">{{ $available }}</td>
                            <td class="px-6 py-4 text-right">
                                @if($canFulfil)
                                    <form method="POST" action="{{ route('admin.inventory.requests.fulfil', $req) }}" onsubmit="return confirm('Hand over {{ $req->quantity }} × {{ $item->name }} to {{ $req->requester->full_name ?? 'requester' }}?')">
                                        @csrf
                                        <button class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-[10px] font-black uppercase tracking-widest">Mark Collected</button>
                                    </form>
                                @else
                                    <span class="text-[10px] font-bold text-rose-500 uppercase tracking-widest">Insufficient stock</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-6 py-16 text-center text-slate-400 dark:text-slate-500 font-bold">Nothing awaiting collection.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-admin-layout>
