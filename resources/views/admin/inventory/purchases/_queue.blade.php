{{-- A purchase-approval queue table (shared by the Principal + GM stages). Vars: $rows, $stage, $action --}}
<div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead>
            <tr class="text-slate-400 dark:text-slate-500 uppercase tracking-widest text-[9px] font-black border-b border-slate-100 dark:border-slate-800">
                <th class="text-left px-6 py-4">Requester</th><th class="text-left px-4 py-4">Item</th><th class="text-center px-4 py-4">Qty</th><th class="text-left px-4 py-4">Est. Total</th><th class="text-left px-4 py-4">Justification</th><th class="text-right px-6 py-4">Decision</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
            @forelse($rows as $req)
                @php $isOwn = $req->requested_by === auth()->id(); @endphp
                <tr class="hover:bg-slate-50/40 dark:hover:bg-slate-800/20 transition-all">
                    <td class="px-6 py-4 font-extrabold text-slate-800 dark:text-slate-200">{{ $req->requester->name ?? '—' }}</td>
                    <td class="px-4 py-4 font-bold text-slate-600 dark:text-slate-300">
                        {{ $req->item_name }}
                        @if($req->category)<span class="block text-[10px] font-bold text-slate-400">{{ $req->category->name }}</span>@endif
                    </td>
                    <td class="px-4 py-4 text-center font-black text-slate-800 dark:text-slate-200">{{ $req->quantity }} {{ $req->unit }}</td>
                    <td class="px-4 py-4 font-bold text-slate-600 dark:text-slate-300">{{ $req->estimated_total ? number_format($req->estimated_total, 2) . ' ETB' : '—' }}</td>
                    <td class="px-4 py-4 text-slate-500 dark:text-slate-400 max-w-[220px] truncate" title="{{ $req->justification }}">{{ $req->justification }}</td>
                    <td class="px-6 py-4 text-right">
                        @if($isOwn)
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Your request — needs another approver</span>
                        @else
                            <div x-show="!(decideId === {{ $req->id }} && stage === '{{ $stage }}')" class="flex justify-end gap-2">
                                <button @click="decideId = {{ $req->id }}; stage = '{{ $stage }}'; decideAction = 'approve'" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-[10px] font-black uppercase tracking-widest">Approve</button>
                                <button @click="decideId = {{ $req->id }}; stage = '{{ $stage }}'; decideAction = 'decline'" class="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-[10px] font-black uppercase tracking-widest">Decline</button>
                            </div>
                            <div x-show="decideId === {{ $req->id }} && stage === '{{ $stage }}'" x-cloak>
                                <form method="POST" action="{{ route($action, $req) }}" class="flex items-center justify-end gap-2">
                                    @csrf
                                    <input type="hidden" name="decision" :value="decideAction">
                                    <input type="text" name="remarks" maxlength="1000" placeholder="Comment (required to decline)" class="rounded-lg border-slate-200 dark:border-slate-700 dark:bg-slate-950/50 dark:text-slate-200 text-xs font-bold w-52">
                                    <button type="submit" class="px-3 py-1.5 text-white rounded-lg text-[10px] font-black uppercase tracking-widest" :class="decideAction === 'approve' ? 'bg-emerald-600' : 'bg-rose-600'" x-text="decideAction === 'approve' ? 'Confirm' : 'Confirm Decline'"></button>
                                    <button type="button" @click="decideId = null; stage = null" class="px-2 py-1.5 text-slate-400 text-[10px] font-black uppercase">Cancel</button>
                                </form>
                            </div>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-6 py-10 text-center text-slate-400 dark:text-slate-500 font-bold">Nothing awaiting decision.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
