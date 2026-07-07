<x-teacher-layout>
    <div class="space-y-6" x-data="{ formOpen: false }">
        <!-- Page Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100 font-heading">My Leave</h1>
                <p class="text-slate-500 dark:text-slate-400 mt-1">Request leave and track the status of your requests.</p>
            </div>
            <button @click="formOpen = !formOpen" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl text-xs font-black uppercase tracking-widest transition-all shadow-md shadow-indigo-200 dark:shadow-none">
                + Request Leave
            </button>
        </div>

        @if(session('success'))
            <div class="px-6 py-4 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-100 dark:border-emerald-900/30 rounded-2xl text-emerald-700 dark:text-emerald-300 font-bold text-sm">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="px-6 py-4 bg-rose-50 dark:bg-rose-950/40 border border-rose-100 dark:border-rose-800 rounded-2xl text-rose-700 dark:text-rose-400 font-bold text-sm">{{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="px-6 py-4 bg-rose-50 dark:bg-rose-950/40 border border-rose-100 dark:border-rose-800 rounded-2xl text-rose-700 dark:text-rose-400 font-bold text-sm">
                {{ $errors->first() }}
            </div>
        @endif

        <!-- Request form -->
        <div x-show="formOpen" x-collapse x-cloak>
            <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl shadow-sm p-8">
                <h2 class="text-lg font-black text-slate-800 dark:text-slate-100 mb-6">New Leave Request</h2>
                <form method="POST" action="{{ route('teacher.leave.store') }}" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                    @csrf
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Type</label>
                        <select name="leave_type" required class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-950/50 dark:text-slate-200 text-sm font-bold">
                            @foreach($leaveTypes as $type)
                                <option value="{{ $type }}" {{ old('leave_type') == $type ? 'selected' : '' }}>{{ ucfirst($type) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">From</label>
                        <input type="date" name="start_date" value="{{ old('start_date') }}" min="{{ now()->toDateString() }}" required class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-950/50 dark:text-slate-200 text-sm font-bold">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">To</label>
                        <input type="date" name="end_date" value="{{ old('end_date') }}" min="{{ now()->toDateString() }}" required class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-950/50 dark:text-slate-200 text-sm font-bold">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Reason</label>
                        <input type="text" name="reason" value="{{ old('reason') }}" required maxlength="1000" placeholder="Reason for leave" class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-950/50 dark:text-slate-200 text-sm font-bold">
                    </div>
                    <div class="md:col-span-2 xl:col-span-4 flex justify-end">
                        <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-black uppercase tracking-widest transition-all">Submit Request</button>
                    </div>
                </form>
                <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 mt-4 uppercase tracking-widest">Total days count school days (Mon–Fri) only.</p>
            </div>
        </div>

        <!-- History -->
        <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50/50 dark:bg-slate-800/40 text-slate-400 dark:text-slate-500 uppercase tracking-widest text-[9px] font-black border-b border-slate-100 dark:border-slate-800">
                            <th class="text-left px-6 py-4">Type</th>
                            <th class="text-left px-4 py-4">Dates</th>
                            <th class="text-center px-4 py-4">Days</th>
                            <th class="text-left px-4 py-4">Reason</th>
                            <th class="text-center px-4 py-4">Status</th>
                            <th class="text-right px-6 py-4"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($requests as $req)
                            <tr class="hover:bg-slate-50/40 dark:hover:bg-slate-800/20 transition-all">
                                <td class="px-6 py-4 font-extrabold text-slate-800 dark:text-slate-200">{{ ucfirst($req->leave_type) }}</td>
                                <td class="px-4 py-4 font-bold text-slate-600 dark:text-slate-300">{{ $req->start_date->format('M j') }} – {{ $req->end_date->format('M j, Y') }}</td>
                                <td class="px-4 py-4 text-center font-black text-slate-800 dark:text-slate-200">{{ $req->total_days }}</td>
                                <td class="px-4 py-4 text-slate-500 dark:text-slate-400 max-w-[280px] truncate" title="{{ $req->reason }}">{{ $req->reason }}</td>
                                <td class="px-4 py-4 text-center">
                                    @php
                                        $chip = match($req->status) {
                                            'pending' => 'bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border-amber-100/50 dark:border-amber-900/30',
                                            'approved' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border-emerald-100/50 dark:border-emerald-900/30',
                                            'rejected' => 'bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 border-rose-100/50 dark:border-rose-900/30',
                                        };
                                    @endphp
                                    <span class="inline-flex px-3 py-1 rounded-xl text-[9px] font-black uppercase tracking-wider border {{ $chip }}">{{ $req->status }}</span>
                                    @if($req->approval_remarks)
                                        <span class="block text-[10px] font-bold text-slate-400 mt-1 max-w-[160px] mx-auto truncate" title="{{ $req->approval_remarks }}">{{ $req->approval_remarks }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    @if($req->status === 'pending')
                                        <form method="POST" action="{{ route('teacher.leave.cancel', $req) }}" onsubmit="return confirm('Withdraw this leave request?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-3 py-1.5 text-rose-500 hover:text-rose-700 text-[10px] font-black uppercase tracking-widest">Withdraw</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-16 text-center text-slate-400 dark:text-slate-500 font-bold">You haven't requested any leave yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{ $requests->links() }}
    </div>
</x-teacher-layout>
