<x-admin-layout>
    <div class="space-y-8" x-data="{ decideId: null, decideAction: null }">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <x-breadcrumb :items="[
                    ['label' => 'Inventory', 'url' => route('admin.inventory.dashboard')],
                    ['label' => 'Item Requests', 'url' => '#']
                ]" />
                <h1 class="text-4xl font-black text-slate-900 dark:text-slate-100 tracking-tight mt-2">Item Requests</h1>
                <p class="text-slate-500 dark:text-slate-400 font-semibold mt-1">Approve or reject staff requests for items from stock.</p>
            </div>
            @if($pendingCount > 0)
                <div class="px-5 py-2.5 bg-amber-50 dark:bg-amber-950/40 border border-amber-100 dark:border-amber-900/30 rounded-2xl flex items-center gap-3">
                    <div class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></div>
                    <span class="text-xs font-black text-amber-700 dark:text-amber-300 uppercase tracking-widest">{{ $pendingCount }} Pending</span>
                </div>
            @endif
        </div>

        @if(session('success'))
            <div class="px-6 py-4 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-100 dark:border-emerald-900/30 rounded-2xl text-emerald-700 dark:text-emerald-300 font-bold text-sm">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="px-6 py-4 bg-rose-50 dark:bg-rose-950/40 border border-rose-100 dark:border-rose-800 rounded-2xl text-rose-700 dark:text-rose-400 font-bold text-sm">{{ session('error') }}</div>
        @endif

        <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50/50 dark:bg-slate-800/40 text-slate-400 dark:text-slate-500 uppercase tracking-widest text-[9px] font-black border-b border-slate-100 dark:border-slate-800">
                            <th class="text-left px-6 py-4">Requester</th><th class="text-left px-4 py-4">Item</th><th class="text-center px-4 py-4">Qty</th><th class="text-left px-4 py-4">Purpose</th><th class="text-center px-4 py-4">Status</th><th class="text-right px-6 py-4">Decision</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($requests as $req)
                            @php $isOwn = $req->requester && $req->requester->user_id === auth()->id(); @endphp
                            <tr class="hover:bg-slate-50/40 dark:hover:bg-slate-800/20 transition-all">
                                <td class="px-6 py-4">
                                    <span class="font-extrabold text-slate-800 dark:text-slate-200">{{ $req->requester->full_name ?? '—' }}</span>
                                    <span class="block text-[10px] font-bold text-slate-400">{{ $req->requester->designation ?? '' }}</span>
                                </td>
                                <td class="px-4 py-4 font-bold text-slate-600 dark:text-slate-300">{{ $req->item->name ?? '—' }}</td>
                                <td class="px-4 py-4 text-center font-black text-slate-800 dark:text-slate-200">{{ $req->quantity }}</td>
                                <td class="px-4 py-4 text-slate-500 dark:text-slate-400 max-w-[220px] truncate" title="{{ $req->purpose }}">{{ $req->purpose }}</td>
                                <td class="px-4 py-4 text-center"><x-inventory.status-chip :status="$req->status" /></td>
                                <td class="px-6 py-4 text-right">
                                    @if($req->status === 'pending')
                                        @if($isOwn)
                                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Your request — needs another approver</span>
                                        @else
                                            <div x-show="decideId !== {{ $req->id }}" class="flex justify-end gap-2">
                                                <button @click="decideId = {{ $req->id }}; decideAction = 'approve'" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-[10px] font-black uppercase tracking-widest">Approve</button>
                                                <button @click="decideId = {{ $req->id }}; decideAction = 'reject'" class="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-[10px] font-black uppercase tracking-widest">Reject</button>
                                            </div>
                                            <div x-show="decideId === {{ $req->id }}" x-cloak>
                                                <form method="POST" :action="decideAction === 'approve' ? '{{ route('admin.inventory.requests.approve', $req) }}' : '{{ route('admin.inventory.requests.reject', $req) }}'" class="flex items-center justify-end gap-2">
                                                    @csrf
                                                    <input type="text" name="decision_remarks" maxlength="1000" placeholder="Remarks (optional)" class="rounded-lg border-slate-200 dark:border-slate-700 dark:bg-slate-950/50 dark:text-slate-200 text-xs font-bold w-44">
                                                    <button type="submit" class="px-3 py-1.5 text-white rounded-lg text-[10px] font-black uppercase tracking-widest" :class="decideAction === 'approve' ? 'bg-emerald-600' : 'bg-rose-600'" x-text="decideAction === 'approve' ? 'Confirm' : 'Confirm Reject'"></button>
                                                    <button type="button" @click="decideId = null" class="px-2 py-1.5 text-slate-400 text-[10px] font-black uppercase">Cancel</button>
                                                </form>
                                            </div>
                                        @endif
                                    @else
                                        <span class="text-[10px] font-bold text-slate-300 dark:text-slate-600 uppercase tracking-widest">{{ $req->decider->name ?? 'Decided' }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-6 py-16 text-center text-slate-400 dark:text-slate-500 font-bold">No item requests.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        {{ $requests->links() }}
    </div>
</x-admin-layout>
