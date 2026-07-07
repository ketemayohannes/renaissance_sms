<x-admin-layout>
    <div class="space-y-8">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <x-breadcrumb :items="[
                    ['label' => 'Human Resources', 'url' => '#'],
                    ['label' => 'Staff Attendance', 'url' => '#']
                ]" />
                <h1 class="text-4xl font-black text-slate-900 dark:text-slate-100 tracking-tight mt-2">Staff Attendance Register</h1>
                <p class="text-slate-500 dark:text-slate-400 font-semibold mt-1">Daily in/out register for all staff. Staff on approved leave are locked automatically.</p>
            </div>
            <form method="GET" class="flex items-end gap-3">
                <div>
                    <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Date</label>
                    <input type="date" name="date" value="{{ $date }}" max="{{ now()->toDateString() }}" onchange="this.form.submit()"
                           class="rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-950/50 dark:text-slate-200 text-sm font-bold">
                </div>
            </form>
        </div>

        @if(session('success'))
            <div class="px-6 py-4 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-100 dark:border-emerald-900/30 rounded-2xl text-emerald-700 dark:text-emerald-300 font-bold text-sm">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="px-6 py-4 bg-rose-50 dark:bg-rose-950/40 border border-rose-100 dark:border-rose-800 rounded-2xl text-rose-700 dark:text-rose-400 font-bold text-sm">{{ session('error') }}</div>
        @endif

        <form method="POST" action="{{ route('admin.hr.staff-attendance.store') }}" x-data="{
            markAllPresent() {
                document.querySelectorAll('select[data-attendance-status]:not([disabled])').forEach(el => el.value = 'present');
            }
        }">
            @csrf
            <input type="hidden" name="attendance_date" value="{{ $date }}">

            <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/40 border-b border-slate-100 dark:border-slate-800">
                    <span class="text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest">{{ \Carbon\Carbon::parse($date)->format('l, F j, Y') }} — {{ $employees->count() }} staff</span>
                    <button type="button" @click="markAllPresent()" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-[10px] font-black uppercase tracking-widest">Mark All Present</button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-slate-400 dark:text-slate-500 uppercase tracking-widest text-[9px] font-black border-b border-slate-100 dark:border-slate-800">
                                <th class="text-left px-6 py-4">Employee</th>
                                <th class="text-left px-4 py-4">Designation</th>
                                <th class="text-left px-4 py-4">Status</th>
                                <th class="text-left px-4 py-4">Check-in</th>
                                <th class="text-left px-4 py-4">Check-out</th>
                                <th class="text-left px-6 py-4">Remarks</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach($employees as $employee)
                                @php
                                    $record = $records->get($employee->id);
                                    $lockedOnLeave = $onLeaveIds->has($employee->id);
                                @endphp
                                <tr class="hover:bg-slate-50/40 dark:hover:bg-slate-800/20 transition-all {{ $lockedOnLeave ? 'opacity-60' : '' }}">
                                    <td class="px-6 py-3">
                                        <span class="font-extrabold text-slate-800 dark:text-slate-200">{{ $employee->full_name }}</span>
                                        <span class="block text-[10px] font-bold text-slate-400">{{ $employee->employee_id }}</span>
                                    </td>
                                    <td class="px-4 py-3 font-bold text-slate-500 dark:text-slate-400">{{ $employee->designation }}</td>
                                    <td class="px-4 py-3">
                                        @if($lockedOnLeave)
                                            <span class="inline-flex px-3 py-1.5 rounded-xl text-[9px] font-black uppercase tracking-wider bg-sky-50 text-sky-700 dark:bg-sky-950/60 dark:text-sky-300 border border-sky-100/50 dark:border-sky-900/30">On Approved Leave</span>
                                        @else
                                            <select name="entries[{{ $employee->id }}][status]" data-attendance-status
                                                    class="rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-950/50 dark:text-slate-200 text-xs font-bold">
                                                <option value="">—</option>
                                                @foreach($statuses as $status)
                                                    @continue($status === 'on_leave')
                                                    <option value="{{ $status }}" {{ ($record->status ?? null) === $status ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                                                @endforeach
                                            </select>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        @unless($lockedOnLeave)
                                            <input type="time" name="entries[{{ $employee->id }}][check_in]" value="{{ $record?->check_in ? substr($record->check_in, 0, 5) : '' }}"
                                                   class="rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-950/50 dark:text-slate-200 text-xs font-bold">
                                        @endunless
                                    </td>
                                    <td class="px-4 py-3">
                                        @unless($lockedOnLeave)
                                            <input type="time" name="entries[{{ $employee->id }}][check_out]" value="{{ $record?->check_out ? substr($record->check_out, 0, 5) : '' }}"
                                                   class="rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-950/50 dark:text-slate-200 text-xs font-bold">
                                        @endunless
                                    </td>
                                    <td class="px-6 py-3">
                                        @unless($lockedOnLeave)
                                            <input type="text" name="entries[{{ $employee->id }}][remarks]" value="{{ $record->remarks ?? '' }}" maxlength="500" placeholder="—"
                                                   class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-950/50 dark:text-slate-200 text-xs font-bold">
                                        @endunless
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 bg-slate-50/50 dark:bg-slate-800/40 border-t border-slate-100 dark:border-slate-800 flex justify-end">
                    <button type="submit" class="px-8 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl text-xs font-black uppercase tracking-widest transition-all shadow-md shadow-indigo-200 dark:shadow-none">Save Register</button>
                </div>
            </div>
        </form>
    </div>
</x-admin-layout>
