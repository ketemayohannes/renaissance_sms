<x-admin-layout>
    <div class="space-y-8">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <x-breadcrumb :items="[
                    ['label' => 'Human Resources', 'url' => '#'],
                    ['label' => 'Staff Availability', 'url' => '#']
                ]" />
                <h1 class="text-4xl font-black text-slate-900 dark:text-slate-100 tracking-tight mt-2">Staff Availability</h1>
                <p class="text-slate-500 dark:text-slate-400 font-semibold mt-1">
                    Attendance status and teaching load for {{ $date->format('l, F j, Y') }}{{ $date->isToday() ? ' (today)' : '' }}.
                </p>
            </div>
            <form method="GET" class="flex flex-wrap items-end gap-3">
                <div>
                    <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Date</label>
                    <input type="date" name="date" value="{{ $date->toDateString() }}" onchange="this.form.submit()"
                           class="rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-950/50 dark:text-slate-200 text-sm font-bold">
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Department</label>
                    <select name="department" onchange="this.form.submit()" class="rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-950/50 dark:text-slate-200 text-sm font-bold">
                        <option value="">All</option>
                        @foreach($departments as $department)
                            <option value="{{ $department }}" {{ request('department') === $department ? 'selected' : '' }}>{{ $department }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Designation</label>
                    <select name="designation" onchange="this.form.submit()" class="rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-950/50 dark:text-slate-200 text-sm font-bold">
                        <option value="">All</option>
                        @foreach($designations as $designation)
                            <option value="{{ $designation }}" {{ request('designation') === $designation ? 'selected' : '' }}>{{ $designation }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Name or ID…"
                           class="rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-950/50 dark:text-slate-200 text-sm font-bold">
                </div>
                <button type="submit" class="px-5 py-2.5 bg-slate-800 dark:bg-slate-700 text-white rounded-xl text-xs font-black uppercase tracking-widest">Apply</button>
            </form>
        </div>

        @unless($isSchoolDay)
            <div class="px-6 py-4 bg-amber-50 dark:bg-amber-950/40 border border-amber-100 dark:border-amber-900/30 rounded-2xl text-amber-700 dark:text-amber-300 font-bold text-sm">
                {{ $date->format('l') }} is not a school day — teaching load and free periods are not shown.
            </div>
        @endunless

        <!-- Board -->
        <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50/50 dark:bg-slate-800/40 text-slate-400 dark:text-slate-500 uppercase tracking-widest text-[9px] font-black border-b border-slate-100 dark:border-slate-800">
                            <th class="text-left px-6 py-4 sticky left-0 bg-slate-50 dark:bg-slate-900 z-10">Staff Member</th>
                            <th class="text-left px-4 py-4">Today's Status</th>
                            @if($isSchoolDay)
                                @foreach($periods as $period)
                                    <th class="text-center px-2 py-4 min-w-[86px] {{ $currentPeriodId === $period->id ? 'text-indigo-600 dark:text-indigo-400' : '' }}">
                                        {{ $period->name }}
                                        @if($currentPeriodId === $period->id)
                                            <span class="block text-[8px] text-indigo-500 normal-case">now</span>
                                        @endif
                                    </th>
                                @endforeach
                                <th class="text-center px-4 py-4">Free</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($board as $row)
                            @php
                                $employee = $row['employee'];
                                $status = $row['status'];
                                $teaching = $row['teaching'];
                                $statusChip = match($status['kind']) {
                                    'present' => ['Present', 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border-emerald-100/50 dark:border-emerald-900/30'],
                                    'late' => ['Late', 'bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border-amber-100/50 dark:border-amber-900/30'],
                                    'half_day' => ['Half Day', 'bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border-amber-100/50 dark:border-amber-900/30'],
                                    'absent' => ['Absent', 'bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 border-rose-100/50 dark:border-rose-900/30'],
                                    'on_leave' => ['On Leave', 'bg-sky-50 text-sky-700 dark:bg-sky-950/60 dark:text-sky-300 border-sky-100/50 dark:border-sky-900/30'],
                                    default => ['No Record', 'bg-slate-50 text-slate-500 dark:bg-slate-800/60 dark:text-slate-400 border-slate-100/50 dark:border-slate-700/30'],
                                };
                            @endphp
                            <tr class="hover:bg-slate-50/40 dark:hover:bg-slate-800/20 transition-all">
                                <td class="px-6 py-4 sticky left-0 bg-white dark:bg-slate-900 z-10">
                                    <span class="font-extrabold text-slate-800 dark:text-slate-200">{{ $employee->full_name }}</span>
                                    <span class="block text-[10px] font-bold text-slate-400">{{ $employee->designation }}</span>
                                </td>
                                <td class="px-4 py-4">
                                    <span class="inline-flex px-3 py-1 rounded-xl text-[9px] font-black uppercase tracking-wider border {{ $statusChip[1] }}">{{ $statusChip[0] }}</span>
                                    @if($status['check_in'])
                                        <span class="block text-[10px] font-bold text-slate-400 mt-1">In {{ substr($status['check_in'], 0, 5) }}{{ $status['check_out'] ? ' · Out ' . substr($status['check_out'], 0, 5) : '' }}</span>
                                    @endif
                                    @if($status['detail'])
                                        <span class="block text-[10px] font-bold text-slate-400 mt-1 max-w-[180px]">{{ $status['detail'] }}</span>
                                    @endif
                                </td>
                                @if($isSchoolDay)
                                    @if($teaching['kind'] === 'scheduled')
                                        @foreach($teaching['slots'] as $slot)
                                            <td class="px-2 py-4 text-center {{ $currentPeriodId === $slot['period']->id ? 'bg-indigo-50/50 dark:bg-indigo-950/20' : '' }}">
                                                @if($slot['busy'])
                                                    <span class="inline-block px-2 py-1 rounded-lg bg-indigo-50 dark:bg-indigo-950/50 border border-indigo-100/50 dark:border-indigo-900/30 text-[9px] font-black text-indigo-700 dark:text-indigo-300"
                                                          title="{{ $slot['busy']['subject'] }} — {{ $slot['busy']['section'] }}{{ $slot['busy']['room'] ? ' (Room ' . $slot['busy']['room'] . ')' : '' }}">
                                                        {{ \Illuminate\Support\Str::limit($slot['busy']['subject'], 10, '…') }}
                                                        <span class="block font-bold text-indigo-400 dark:text-indigo-500">{{ \Illuminate\Support\Str::limit($slot['busy']['section'], 12, '…') }}</span>
                                                    </span>
                                                @else
                                                    <span class="inline-block px-2 py-1 rounded-lg bg-emerald-50/60 dark:bg-emerald-950/30 text-[9px] font-black text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">Free</span>
                                                @endif
                                            </td>
                                        @endforeach
                                        <td class="px-4 py-4 text-center">
                                            <span class="font-black text-slate-800 dark:text-slate-200">{{ $teaching['free_count'] }}</span>
                                            <span class="text-[10px] font-bold text-slate-400">/ {{ $periods->count() }}</span>
                                        </td>
                                    @elseif($teaching['kind'] === 'timetable_not_entered')
                                        <td colspan="{{ $periods->count() + 1 }}" class="px-4 py-4 text-center">
                                            <span class="inline-flex px-3 py-1 rounded-xl text-[9px] font-black uppercase tracking-wider bg-amber-50 text-amber-600 dark:bg-amber-950/40 dark:text-amber-400 border border-amber-100/50 dark:border-amber-900/30">
                                                Timetable not entered — free periods unknown
                                            </span>
                                        </td>
                                    @else
                                        <td colspan="{{ $periods->count() + 1 }}" class="px-4 py-4 text-center">
                                            <span class="text-[10px] font-bold text-slate-300 dark:text-slate-600 uppercase tracking-widest">Non-teaching staff</span>
                                        </td>
                                    @endif
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $isSchoolDay ? $periods->count() + 3 : 2 }}" class="px-6 py-16 text-center text-slate-400 dark:text-slate-500 font-bold">No staff match the current filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-admin-layout>
