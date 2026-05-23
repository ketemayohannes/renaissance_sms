<x-parent-layout header="{{ $student->full_name }}'s Attendance Report">
    <div class="space-y-6">
        <!-- Attendance Stats Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
            <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl p-4 shadow-sm text-center">
                <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Attendance Rate</span>
                <span class="block text-2xl font-extrabold text-indigo-600 mt-2">
                    {{ $attendanceCount > 0 ? round((($presentCount + $lateCount) / $attendanceCount) * 100, 1) : 100 }}%
                </span>
            </div>
            
            <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl p-4 shadow-sm text-center">
                <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Days Present</span>
                <span class="block text-2xl font-extrabold text-emerald-600 mt-2">{{ $presentCount }}</span>
            </div>

            <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl p-4 shadow-sm text-center">
                <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Days Late</span>
                <span class="block text-2xl font-extrabold text-amber-600 mt-2">{{ $lateCount }}</span>
            </div>

            <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl p-4 shadow-sm text-center">
                <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Days Absent</span>
                <span class="block text-2xl font-extrabold text-rose-600 mt-2">{{ $absentCount }}</span>
            </div>

            <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl p-4 shadow-sm text-center col-span-2 lg:col-span-1">
                <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Days Excused</span>
                <span class="block text-2xl font-extrabold text-blue-600 mt-2">{{ $excusedCount }}</span>
            </div>
        </div>

        <!-- Attendance Logs Table -->
        <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl p-6 shadow-sm">
            <h3 class="font-bold text-lg text-slate-800 dark:text-slate-100 font-heading mb-4">Detailed Attendance History</h3>
            
            @if($student->attendance->isEmpty())
                <div class="text-center py-12">
                    <svg class="w-16 h-16 text-slate-300 dark:text-slate-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <h4 class="text-base font-semibold text-slate-705">No attendance logs yet</h4>
                    <p class="text-slate-500 text-sm mt-1">Daily attendance records will be displayed here as they are entered by teachers.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-slate-50/50 dark:bg-slate-850/40 text-slate-450 dark:text-slate-400 uppercase tracking-wider text-[10px] font-bold border-b border-slate-150 dark:border-slate-800">
                                <th class="text-left px-6 py-3">Date</th>
                                <th class="text-left px-6 py-3">Day</th>
                                <th class="text-left px-6 py-3">Status</th>
                                <th class="text-left px-6 py-3">Remarks / Excuse Note</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-850">
                            @foreach($student->attendance->sortByDesc('attendance_date') as $record)
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                                    <td class="px-6 py-4 font-semibold text-slate-850 dark:text-slate-200">
                                        {{ $record->attendance_date->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-4 text-slate-500">
                                        {{ $record->attendance_date->format('l') }}
                                    </td>
                                    <td class="px-6 py-4">
                                        @php
                                            $statusColors = [
                                                'present' => 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-450',
                                                'absent' => 'bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-450',
                                                'late' => 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-450',
                                                'excused' => 'bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-450',
                                            ];
                                            $statusName = strtolower($record->status);
                                            $colorClass = $statusColors[$statusName] ?? 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400';
                                        @endphp
                                        <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-bold {{ $colorClass }}">
                                            {{ ucfirst($record->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-slate-500 italic">
                                        {{ $record->remarks ?: 'No remarks' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-parent-layout>