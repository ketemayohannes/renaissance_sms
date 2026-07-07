<x-admin-layout>
    <div class="space-y-8" x-data="{ createOpen: false, decideId: null, decideAction: null }">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <x-breadcrumb :items="[
                    ['label' => 'Human Resources', 'url' => '#'],
                    ['label' => 'Leave Requests', 'url' => '#']
                ]" />
                <h1 class="text-4xl font-black text-slate-900 dark:text-slate-100 tracking-tight mt-2">Staff Leave Requests</h1>
                <p class="text-slate-500 dark:text-slate-400 font-semibold mt-1">Review, approve, and record staff leave.</p>
            </div>
            <div class="flex items-center gap-3">
                @if($pendingCount > 0)
                    <div class="px-5 py-2.5 bg-amber-50 dark:bg-amber-950/40 border border-amber-100 dark:border-amber-900/30 rounded-2xl flex items-center gap-3">
                        <div class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></div>
                        <span class="text-xs font-black text-amber-700 dark:text-amber-300 uppercase tracking-widest">{{ $pendingCount }} Pending</span>
                    </div>
                @endif
                <button @click="createOpen = !createOpen" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl text-xs font-black uppercase tracking-widest transition-all shadow-md shadow-indigo-200 dark:shadow-none">
                    + Record on Behalf
                </button>
            </div>
        </div>

        @if(session('success'))
            <div class="px-6 py-4 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-100 dark:border-emerald-900/30 rounded-2xl text-emerald-700 dark:text-emerald-300 font-bold text-sm">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="px-6 py-4 bg-rose-50 dark:bg-rose-950/40 border border-rose-100 dark:border-rose-800 rounded-2xl text-rose-700 dark:text-rose-400 font-bold text-sm">{{ session('error') }}</div>
        @endif

        <!-- Record-on-behalf form (for staff without portal access) -->
        <div x-show="createOpen" x-collapse x-cloak>
            <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl shadow-sm p-8">
                <h2 class="text-lg font-black text-slate-800 dark:text-slate-100 mb-6">Record Leave on Behalf of Staff</h2>
                <form method="POST" action="{{ route('admin.hr.leave-requests.store') }}" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4">
                    @csrf
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Employee</label>
                        <select name="employee_id" required class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-950/50 dark:text-slate-200 text-sm font-bold">
                            <option value="">Select…</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}" {{ old('employee_id') == $employee->id ? 'selected' : '' }}>{{ $employee->full_name }} ({{ $employee->employee_id }})</option>
                            @endforeach
                        </select>
                    </div>
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
                        <input type="date" name="start_date" value="{{ old('start_date') }}" required class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-950/50 dark:text-slate-200 text-sm font-bold">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">To</label>
                        <input type="date" name="end_date" value="{{ old('end_date') }}" required class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-950/50 dark:text-slate-200 text-sm font-bold">
                    </div>
                    <div class="xl:col-span-1 md:col-span-2">
                        <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Reason</label>
                        <input type="text" name="reason" value="{{ old('reason') }}" required maxlength="1000" class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-950/50 dark:text-slate-200 text-sm font-bold" placeholder="Reason for leave">
                    </div>
                    <div class="md:col-span-2 xl:col-span-5 flex justify-end">
                        <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-black uppercase tracking-widest transition-all">Save (Pending Approval)</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Filters -->
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Status</label>
                <select name="status" onchange="this.form.submit()" class="rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-950/50 dark:text-slate-200 text-sm font-bold">
                    <option value="">All</option>
                    @foreach(['pending', 'approved', 'rejected'] as $status)
                        <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Type</label>
                <select name="leave_type" onchange="this.form.submit()" class="rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-950/50 dark:text-slate-200 text-sm font-bold">
                    <option value="">All</option>
                    @foreach($leaveTypes as $type)
                        <option value="{{ $type }}" {{ request('leave_type') === $type ? 'selected' : '' }}>{{ ucfirst($type) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Name or ID…" class="rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-950/50 dark:text-slate-200 text-sm font-bold">
            </div>
            <button type="submit" class="px-5 py-2.5 bg-slate-800 dark:bg-slate-700 text-white rounded-xl text-xs font-black uppercase tracking-widest">Filter</button>
        </form>

        <!-- Requests table -->
        <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50/50 dark:bg-slate-800/40 text-slate-400 dark:text-slate-500 uppercase tracking-widest text-[9px] font-black border-b border-slate-100 dark:border-slate-800">
                            <th class="text-left px-6 py-4">Employee</th>
                            <th class="text-left px-4 py-4">Type</th>
                            <th class="text-left px-4 py-4">Dates</th>
                            <th class="text-center px-4 py-4">Days</th>
                            <th class="text-left px-4 py-4">Reason</th>
                            <th class="text-center px-4 py-4">Status</th>
                            <th class="text-right px-6 py-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($requests as $req)
                            <tr class="hover:bg-slate-50/40 dark:hover:bg-slate-800/20 transition-all">
                                <td class="px-6 py-4">
                                    <span class="font-extrabold text-slate-800 dark:text-slate-200">{{ $req->employee->full_name ?? '—' }}</span>
                                    <span class="block text-[10px] font-bold text-slate-400">{{ $req->employee->designation ?? '' }}</span>
                                </td>
                                <td class="px-4 py-4 font-bold text-slate-600 dark:text-slate-300">{{ ucfirst($req->leave_type) }}</td>
                                <td class="px-4 py-4 font-bold text-slate-600 dark:text-slate-300">{{ $req->start_date->format('M j') }} – {{ $req->end_date->format('M j, Y') }}</td>
                                <td class="px-4 py-4 text-center font-black text-slate-800 dark:text-slate-200">{{ $req->total_days }}</td>
                                <td class="px-4 py-4 text-slate-500 dark:text-slate-400 max-w-[240px] truncate" title="{{ $req->reason }}">{{ $req->reason }}</td>
                                <td class="px-4 py-4 text-center">
                                    @php
                                        $chip = match($req->status) {
                                            'pending' => 'bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border-amber-100/50 dark:border-amber-900/30',
                                            'approved' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border-emerald-100/50 dark:border-emerald-900/30',
                                            'rejected' => 'bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 border-rose-100/50 dark:border-rose-900/30',
                                        };
                                    @endphp
                                    <span class="inline-flex px-3 py-1 rounded-xl text-[9px] font-black uppercase tracking-wider border {{ $chip }}">{{ $req->status }}</span>
                                    @if($req->approver)
                                        <span class="block text-[9px] font-bold text-slate-400 mt-1">by {{ $req->approver->name }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    @if($req->status === 'pending')
                                        <div x-show="decideId !== {{ $req->id }}" class="flex justify-end gap-2">
                                            <button @click="decideId = {{ $req->id }}; decideAction = 'approve'" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-[10px] font-black uppercase tracking-widest">Approve</button>
                                            <button @click="decideId = {{ $req->id }}; decideAction = 'reject'" class="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-[10px] font-black uppercase tracking-widest">Reject</button>
                                        </div>
                                        <div x-show="decideId === {{ $req->id }}" x-cloak>
                                            <form method="POST" :action="decideAction === 'approve' ? '{{ route('admin.hr.leave-requests.approve', $req) }}' : '{{ route('admin.hr.leave-requests.reject', $req) }}'" class="flex items-center justify-end gap-2">
                                                @csrf
                                                <input type="text" name="approval_remarks" maxlength="1000" placeholder="Remarks (optional)" class="rounded-lg border-slate-200 dark:border-slate-700 dark:bg-slate-950/50 dark:text-slate-200 text-xs font-bold w-44">
                                                <button type="submit" class="px-3 py-1.5 text-white rounded-lg text-[10px] font-black uppercase tracking-widest" :class="decideAction === 'approve' ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-rose-600 hover:bg-rose-700'" x-text="decideAction === 'approve' ? 'Confirm Approve' : 'Confirm Reject'"></button>
                                                <button type="button" @click="decideId = null" class="px-2 py-1.5 text-slate-400 text-[10px] font-black uppercase">Cancel</button>
                                            </form>
                                        </div>
                                    @else
                                        <span class="text-[10px] font-bold text-slate-300 dark:text-slate-600 uppercase tracking-widest">Decided</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-16 text-center text-slate-400 dark:text-slate-500 font-bold">No leave requests found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{ $requests->links() }}
    </div>
</x-admin-layout>
