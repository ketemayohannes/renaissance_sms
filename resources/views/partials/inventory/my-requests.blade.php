{{--
    Shared "My Requests" body — included by both the admin and teacher portals so the two
    submission paths render identically. The including view passes route-name strings.

    Vars: $itemRequests, $purchaseRequests, $items, $categories, $hasEmployee,
          $itemStoreRouteName, $itemCancelRouteName, $purchaseStoreRouteName, $purchaseCancelRouteName
--}}
<div x-data="{ tab: 'item', itemFormOpen: false, purchaseFormOpen: false }" class="space-y-6">
    @if(session('success'))
        <div class="px-6 py-4 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-100 dark:border-emerald-900/30 rounded-2xl text-emerald-700 dark:text-emerald-300 font-bold text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="px-6 py-4 bg-rose-50 dark:bg-rose-950/40 border border-rose-100 dark:border-rose-800 rounded-2xl text-rose-700 dark:text-rose-400 font-bold text-sm">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="px-6 py-4 bg-rose-50 dark:bg-rose-950/40 border border-rose-100 dark:border-rose-800 rounded-2xl text-rose-700 dark:text-rose-400 font-bold text-sm">{{ $errors->first() }}</div>
    @endif

    @unless($hasEmployee)
        <div class="px-6 py-4 bg-amber-50 dark:bg-amber-950/40 border border-amber-100 dark:border-amber-900/30 rounded-2xl text-amber-700 dark:text-amber-300 font-bold text-sm">
            Your account has no linked employee record, so item requests are unavailable. You can still submit purchase requests.
        </div>
    @endunless

    <div class="flex items-center gap-2">
        <button type="button" @click="tab = 'item'" :class="tab === 'item' ? 'bg-indigo-600 text-white' : 'bg-white dark:bg-slate-900 text-slate-500 dark:text-slate-400 border border-slate-100 dark:border-slate-800'" class="px-5 py-2.5 rounded-2xl font-black text-xs uppercase tracking-widest transition-all">Item Requests</button>
        <button type="button" @click="tab = 'purchase'" :class="tab === 'purchase' ? 'bg-indigo-600 text-white' : 'bg-white dark:bg-slate-900 text-slate-500 dark:text-slate-400 border border-slate-100 dark:border-slate-800'" class="px-5 py-2.5 rounded-2xl font-black text-xs uppercase tracking-widest transition-all">Purchase Requests</button>
    </div>

    {{-- ITEM REQUESTS --}}
    <div x-show="tab === 'item'" x-cloak class="space-y-4">
        @if($hasEmployee)
            <div class="flex justify-end">
                <button @click="itemFormOpen = !itemFormOpen" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl text-xs font-black uppercase tracking-widest">+ Request an Item</button>
            </div>
            <div x-show="itemFormOpen" x-collapse x-cloak>
                <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl shadow-sm p-8">
                    <form method="POST" action="{{ route($itemStoreRouteName) }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        @csrf
                        <div class="md:col-span-2">
                            <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Item</label>
                            <select name="inventory_item_id" required class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-950/50 dark:text-slate-200 text-sm font-bold">
                                <option value="">Select…</option>
                                @foreach($items as $it)
                                    <option value="{{ $it->id }}">{{ $it->name }} ({{ $it->kind }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Quantity</label>
                            <input type="number" name="quantity" min="1" required class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-950/50 dark:text-slate-200 text-sm font-bold">
                        </div>
                        <div class="md:col-span-4">
                            <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Purpose</label>
                            <input type="text" name="purpose" required maxlength="1000" class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-950/50 dark:text-slate-200 text-sm font-bold" placeholder="What it's for">
                        </div>
                        <div class="md:col-span-4 flex justify-end">
                            <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-black uppercase tracking-widest">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-slate-800/40 text-slate-400 dark:text-slate-500 uppercase tracking-widest text-[9px] font-black border-b border-slate-100 dark:border-slate-800">
                        <th class="text-left px-6 py-4">Item</th><th class="text-center px-4 py-4">Qty</th><th class="text-left px-4 py-4">Purpose</th><th class="text-center px-4 py-4">Status</th><th class="text-right px-6 py-4"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($itemRequests as $req)
                        <tr class="hover:bg-slate-50/40 dark:hover:bg-slate-800/20 transition-all">
                            <td class="px-6 py-4 font-extrabold text-slate-800 dark:text-slate-200">{{ $req->item->name ?? '—' }}</td>
                            <td class="px-4 py-4 text-center font-black text-slate-800 dark:text-slate-200">{{ $req->quantity }}</td>
                            <td class="px-4 py-4 text-slate-500 dark:text-slate-400 max-w-[240px] truncate" title="{{ $req->purpose }}">{{ $req->purpose }}</td>
                            <td class="px-4 py-4 text-center"><x-inventory.status-chip :status="$req->status" /></td>
                            <td class="px-6 py-4 text-right">
                                @if($req->status === 'pending')
                                    <form method="POST" action="{{ route($itemCancelRouteName, $req) }}" onsubmit="return confirm('Withdraw this request?')">@csrf @method('DELETE')
                                        <button class="px-3 py-1.5 text-rose-500 hover:text-rose-700 text-[10px] font-black uppercase tracking-widest">Withdraw</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-12 text-center text-slate-400 dark:text-slate-500 font-bold">No item requests yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- PURCHASE REQUESTS --}}
    <div x-show="tab === 'purchase'" x-cloak class="space-y-4">
        <div class="flex justify-end">
            <button @click="purchaseFormOpen = !purchaseFormOpen" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl text-xs font-black uppercase tracking-widest">+ Request a Purchase</button>
        </div>
        <div x-show="purchaseFormOpen" x-collapse x-cloak>
            <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl shadow-sm p-8">
                <form method="POST" action="{{ route($purchaseStoreRouteName) }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    @csrf
                    <div class="md:col-span-2">
                        <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Item to Buy</label>
                        <input type="text" name="item_name" required maxlength="255" placeholder="e.g. Projector, A4 paper" class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-950/50 dark:text-slate-200 text-sm font-bold">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Category</label>
                        <select name="inventory_category_id" class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-950/50 dark:text-slate-200 text-sm font-bold">
                            <option value="">—</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Quantity</label>
                        <input type="number" name="quantity" min="1" required class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-950/50 dark:text-slate-200 text-sm font-bold">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Unit</label>
                        <input type="text" name="unit" maxlength="50" placeholder="pcs / box" class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-950/50 dark:text-slate-200 text-sm font-bold">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Est. Unit Cost (ETB)</label>
                        <input type="number" name="estimated_unit_cost" min="0" step="0.01" class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-950/50 dark:text-slate-200 text-sm font-bold">
                    </div>
                    <div class="md:col-span-4">
                        <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Justification</label>
                        <input type="text" name="justification" required maxlength="1000" placeholder="Why this purchase is needed" class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-950/50 dark:text-slate-200 text-sm font-bold">
                    </div>
                    <div class="md:col-span-4 flex justify-end">
                        <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-black uppercase tracking-widest">Submit</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-slate-800/40 text-slate-400 dark:text-slate-500 uppercase tracking-widest text-[9px] font-black border-b border-slate-100 dark:border-slate-800">
                        <th class="text-left px-6 py-4">Item</th><th class="text-center px-4 py-4">Qty</th><th class="text-left px-4 py-4">Est. Cost</th><th class="text-center px-4 py-4">Status</th><th class="text-left px-4 py-4">Remarks</th><th class="text-right px-6 py-4"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($purchaseRequests as $req)
                        <tr class="hover:bg-slate-50/40 dark:hover:bg-slate-800/20 transition-all">
                            <td class="px-6 py-4 font-extrabold text-slate-800 dark:text-slate-200">{{ $req->item_name }}</td>
                            <td class="px-4 py-4 text-center font-black text-slate-800 dark:text-slate-200">{{ $req->quantity }}</td>
                            <td class="px-4 py-4 font-bold text-slate-600 dark:text-slate-300">{{ $req->estimated_total ? number_format($req->estimated_total, 2) . ' ETB' : '—' }}</td>
                            <td class="px-4 py-4 text-center"><x-inventory.status-chip :status="$req->status" /></td>
                            <td class="px-4 py-4 text-slate-500 dark:text-slate-400 max-w-[200px] truncate">{{ $req->gm_remarks ?? $req->principal_remarks ?? '—' }}</td>
                            <td class="px-6 py-4 text-right">
                                @if($req->status === 'pending')
                                    <form method="POST" action="{{ route($purchaseCancelRouteName, $req) }}" onsubmit="return confirm('Withdraw this request?')">@csrf @method('DELETE')
                                        <button class="px-3 py-1.5 text-rose-500 hover:text-rose-700 text-[10px] font-black uppercase tracking-widest">Withdraw</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-6 py-12 text-center text-slate-400 dark:text-slate-500 font-bold">No purchase requests yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
