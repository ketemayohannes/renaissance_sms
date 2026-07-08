<x-parent-layout header="{{ $student->full_name }}'s Attendance Report">
    <div class="space-y-6 max-w-6xl mx-auto" x-data="{ quarterDrawerOpen: false, monthDrawerOpen: false, selectedQuarterTemp: '{{ $selectedQuarter }}', selectedMonthTemp: '{{ $selectedMonth }}' }">
        <x-breadcrumb :items="[
            ['label' => $student->full_name, 'url' => route('parent.student.dashboard', $student)],
            ['label' => 'Attendance', 'url' => '#']
        ]" />

        <!-- Premium Banner -->
        <div class="relative bg-gradient-to-r from-violet-600 via-indigo-600 to-indigo-700 rounded-3xl p-5 sm:p-6 lg:p-8 text-white overflow-hidden shadow-lg shadow-indigo-100 dark:shadow-none">
            <div class="absolute -right-10 -top-10 w-32 sm:w-40 h-32 sm:h-40 bg-white/10 rounded-full blur-xl"></div>
            <div class="absolute -right-20 -bottom-20 w-48 sm:w-60 h-48 sm:h-60 bg-indigo-500/20 rounded-full blur-2xl"></div>
            <div class="relative z-10">
                <h2 class="text-lg sm:text-2xl font-bold font-heading tracking-tight">Attendance Overview</h2>
                <p class="text-indigo-100 text-xs sm:text-sm mt-1">Daily presence tracking and attendance statistics</p>
            </div>
        </div>

        <!-- Elegant Premium Filter Bar -->
        <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800/80 rounded-2xl p-4 sm:p-5 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-2 h-5 rounded bg-indigo-600"></div>
                <h3 class="font-bold text-sm sm:text-base text-slate-850 dark:text-slate-100 font-heading uppercase tracking-wider">Filter Attendance</h3>
            </div>
            
            <form action="{{ route('parent.student.attendance.index', $student) }}" method="GET" class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
                <!-- Desktop Selects -->
                <div class="hidden sm:flex items-center gap-3">
                    <div class="flex flex-col">
                        <span class="text-[9px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1">Quarter</span>
                        <select name="quarter" onchange="document.getElementById('desktop-month-select').value = 'all'; this.form.submit()" class="pl-3 pr-8 py-2 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-850 text-slate-700 dark:text-slate-250 rounded-xl font-bold text-xs focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all cursor-pointer shadow-sm">
                            <option value="all" {{ $selectedQuarter == 'all' ? 'selected' : '' }}>All Quarters</option>
                            @foreach($quarters as $quarter)
                                <option value="{{ $quarter->id }}" {{ $selectedQuarter == $quarter->id ? 'selected' : '' }}>{{ $quarter->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex flex-col">
                        <span class="text-[9px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1">Month</span>
                        <select name="month" id="desktop-month-select" onchange="this.form.submit()" class="pl-3 pr-8 py-2 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-850 text-slate-700 dark:text-slate-250 rounded-xl font-bold text-xs focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all cursor-pointer shadow-sm">
                            <option value="all" {{ $selectedMonth == 'all' ? 'selected' : '' }}>All Months</option>
                            @foreach($availableMonths as $m)
                                <option value="{{ $m }}" {{ $selectedMonth == $m ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::createFromFormat('Y-m', $m)->format('F Y') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Mobile Trigger Buttons -->
                <div class="flex sm:hidden items-center gap-2.5 w-full">
                    <!-- Quarter Selector Button -->
                    <button type="button" @click="quarterDrawerOpen = true" class="flex-1 flex items-center justify-between px-3 py-2.5 border border-slate-150 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-855 text-slate-700 dark:text-slate-250 rounded-xl font-bold text-xs shadow-sm">
                        <span class="truncate pr-1">
                            @if($selectedQuarter === 'all')
                                All Quarters
                            @else
                                {{ $quarters->find((int) $selectedQuarter)?->name ?? 'All Quarters' }}
                            @endif
                        </span>
                        <svg class="w-3.5 h-3.5 text-indigo-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>

                    <!-- Month Selector Button -->
                    <button type="button" @click="monthDrawerOpen = true" class="flex-1 flex items-center justify-between px-3 py-2.5 border border-slate-150 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-855 text-slate-700 dark:text-slate-250 rounded-xl font-bold text-xs shadow-sm">
                        <span class="truncate pr-1">
                            @if($selectedMonth === 'all')
                                All Months
                            @else
                                {{ \Carbon\Carbon::createFromFormat('Y-m', $selectedMonth)->format('F Y') }}
                            @endif
                        </span>
                        <svg class="w-3.5 h-3.5 text-indigo-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                </div>
            </form>
        </div>

        <!-- Attendance Stats Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4">
            <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl p-4 shadow-sm text-center">
                <span class="block text-[10px] sm:text-xs font-bold text-slate-400 uppercase tracking-wider">Attendance Rate</span>
                <span class="block text-xl sm:text-2xl font-extrabold text-indigo-600 mt-1.5 sm:mt-2">
                    {{ $attendanceCount > 0 ? round((($presentCount + $lateCount) / $attendanceCount) * 100, 1) : 100 }}%
                </span>
            </div>
            
            <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl p-4 shadow-sm text-center">
                <span class="block text-[10px] sm:text-xs font-bold text-slate-400 uppercase tracking-wider">Days Present</span>
                <span class="block text-xl sm:text-2xl font-extrabold text-emerald-600 mt-1.5 sm:mt-2">{{ $presentCount }}</span>
            </div>

            <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl p-4 shadow-sm text-center">
                <span class="block text-[10px] sm:text-xs font-bold text-slate-400 uppercase tracking-wider">Days Late</span>
                <span class="block text-xl sm:text-2xl font-extrabold text-amber-600 mt-1.5 sm:mt-2">{{ $lateCount }}</span>
            </div>

            <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl p-4 shadow-sm text-center">
                <span class="block text-[10px] sm:text-xs font-bold text-slate-400 uppercase tracking-wider">Days Absent</span>
                <span class="block text-xl sm:text-2xl font-extrabold text-rose-600 mt-1.5 sm:mt-2">{{ $absentCount }}</span>
            </div>

            <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl p-4 shadow-sm text-center col-span-2 sm:col-span-1">
                <span class="block text-[10px] sm:text-xs font-bold text-slate-400 uppercase tracking-wider">Days Excused</span>
                <span class="block text-xl sm:text-2xl font-extrabold text-blue-600 mt-1.5 sm:mt-2">{{ $excusedCount }}</span>
            </div>
        </div>

        <!-- Attendance Logs -->
        <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl p-4 sm:p-6 shadow-sm">
            <h3 class="font-bold text-base sm:text-lg text-slate-800 dark:text-slate-100 font-heading mb-4">Detailed Attendance History</h3>
            
            @if($student->attendance->isEmpty())
                <div class="text-center py-12">
                    <svg class="w-12 h-12 sm:w-16 sm:h-16 text-slate-300 dark:text-slate-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <h4 class="text-sm sm:text-base font-bold text-slate-700 dark:text-slate-300">No attendance logs yet</h4>
                    <p class="text-slate-500 text-xs sm:text-sm mt-1">Daily attendance records will be displayed here as they are entered by teachers.</p>
                </div>
            @elseif($filteredAttendance->isEmpty())
                <div class="text-center py-12">
                    <svg class="w-12 h-12 sm:w-16 sm:h-16 text-slate-300 dark:text-slate-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <h4 class="text-sm sm:text-base font-bold text-slate-700 dark:text-slate-300">No matching attendance logs</h4>
                    <p class="text-slate-500 text-xs sm:text-sm mt-1">No records match the selected quarter and month filters. Try adjusting your filters.</p>
                </div>
            @else
                {{-- Desktop Table --}}
                <div class="hidden sm:block overflow-x-auto">
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
                            @foreach($filteredAttendance->sortByDesc('attendance_date') as $record)
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

                {{-- Mobile Card Layout --}}
                <div class="sm:hidden space-y-2.5">
                    @foreach($filteredAttendance->sortByDesc('attendance_date') as $record)
                        @php
                            $statusColors = [
                                'present' => 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-450 border-emerald-100 dark:border-emerald-900/40',
                                'absent' => 'bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-450 border-rose-100 dark:border-rose-900/40',
                                'late' => 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-450 border-amber-100 dark:border-amber-900/40',
                                'excused' => 'bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-450 border-blue-100 dark:border-blue-900/40',
                            ];
                            $statusName = strtolower($record->status);
                            $cardColor = $statusColors[$statusName] ?? 'bg-slate-50 dark:bg-slate-850/40 text-slate-600 dark:text-slate-400 border-slate-100 dark:border-slate-800';
                            $cardParts = explode(' ', $cardColor);
                            $borderColor = end($cardParts);
                        @endphp
                        <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-850/30 border border-slate-100 dark:border-slate-800 flex items-center gap-3">
                            {{-- Status Indicator Dot --}}
                            <div class="flex-shrink-0">
                                @php
                                    $dotColors = [
                                        'present' => 'bg-emerald-500',
                                        'absent' => 'bg-rose-500',
                                        'late' => 'bg-amber-500',
                                        'excused' => 'bg-blue-500',
                                    ];
                                    $dotColor = $dotColors[$statusName] ?? 'bg-slate-400';
                                @endphp
                                <div class="w-2.5 h-2.5 rounded-full {{ $dotColor }}"></div>
                            </div>

                            {{-- Date & Status --}}
                            <div class="flex-grow min-w-0">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="font-bold text-sm text-slate-800 dark:text-slate-200 truncate">{{ $record->attendance_date->format('M d, Y') }}</span>
                                    @php
                                        $pillColors = [
                                            'present' => 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400',
                                            'absent' => 'bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-400',
                                            'late' => 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400',
                                            'excused' => 'bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-400',
                                        ];
                                        $pillColor = $pillColors[$statusName] ?? 'bg-slate-100 text-slate-600';
                                    @endphp
                                    <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold {{ $pillColor }} flex-shrink-0">
                                        {{ ucfirst($record->status) }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <span class="text-[11px] text-slate-400">{{ $record->attendance_date->format('l') }}</span>
                                    @if($record->remarks)
                                        <span class="text-slate-300 dark:text-slate-600">•</span>
                                        <span class="text-[11px] text-slate-500 italic truncate">{{ $record->remarks }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Quarter Mobile Drawer Backdrop & Drawer -->
        <div x-cloak x-show="quarterDrawerOpen" class="fixed inset-0 z-[100] sm:hidden" x-transition>
            <!-- Backdrop -->
            <div @click="quarterDrawerOpen = false" class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm transition-opacity"></div>
            
            <!-- Bottom Drawer Container -->
            <div class="fixed inset-x-0 bottom-0 max-h-[85vh] bg-white dark:bg-slate-900 rounded-t-[32px] border-t border-slate-150 dark:border-slate-800 shadow-2xl p-6 overflow-y-auto flex flex-col transition-all transform duration-300"
                 x-show="quarterDrawerOpen"
                 x-transition:enter="transition ease-out duration-300 transform"
                 x-transition:enter-start="translate-y-full"
                 x-transition:enter-end="translate-y-0"
                 x-transition:leave="transition ease-in duration-200 transform"
                 x-transition:leave-start="translate-y-0"
                 x-transition:leave-end="translate-y-full">
                
                <!-- Swipe Indicator bar -->
                <div class="w-12 h-1.5 bg-slate-200 dark:bg-slate-700 rounded-full mx-auto mb-5 flex-shrink-0"></div>
                
                <div class="flex items-center justify-between mb-6 flex-shrink-0">
                    <h3 class="font-extrabold text-lg text-slate-850 dark:text-slate-100 font-heading">Select Academic Quarter</h3>
                    <button @click="quarterDrawerOpen = false" class="p-2 rounded-full bg-slate-50 dark:bg-slate-800 text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <!-- Option Lists -->
                <form action="{{ route('parent.student.attendance.index', $student) }}" method="GET" class="space-y-4">
                    <input type="hidden" name="quarter" x-model="selectedQuarterTemp">
                    <input type="hidden" name="month" value="all">
                    
                    <div class="space-y-2.5">
                        <!-- All Quarters Option -->
                        <button type="button" @click="selectedQuarterTemp = 'all'; $nextTick(() => $el.form.submit())"
                                :class="selectedQuarterTemp === 'all' ? 'border-indigo-500 bg-indigo-50/50 dark:bg-indigo-950/20 text-indigo-650 dark:text-indigo-455' : 'border-slate-100 dark:border-slate-800 text-slate-750 dark:text-slate-350'"
                                class="w-full text-left px-4 py-3.5 border rounded-2xl font-bold text-sm flex items-center justify-between transition-colors">
                            <span>All Quarters</span>
                            <span x-show="selectedQuarterTemp === 'all'" class="text-indigo-600">✓</span>
                        </button>

                        @foreach($quarters as $quarter)
                            <button type="button" @click="selectedQuarterTemp = '{{ $quarter->id }}'; $nextTick(() => $el.form.submit())"
                                    :class="selectedQuarterTemp === '{{ $quarter->id }}' ? 'border-indigo-500 bg-indigo-50/50 dark:bg-indigo-950/20 text-indigo-650 dark:text-indigo-455' : 'border-slate-100 dark:border-slate-800 text-slate-750 dark:text-slate-350'"
                                    class="w-full text-left px-4 py-3.5 border rounded-2xl font-bold text-sm flex items-center justify-between transition-colors">
                                <span>{{ $quarter->name }}</span>
                                <span x-show="selectedQuarterTemp === '{{ $quarter->id }}'" class="text-indigo-600">✓</span>
                            </button>
                        @endforeach
                    </div>
                </form>
            </div>
        </div>

        <!-- Month Mobile Drawer Backdrop & Drawer -->
        <div x-cloak x-show="monthDrawerOpen" class="fixed inset-0 z-[100] sm:hidden" x-transition>
            <!-- Backdrop -->
            <div @click="monthDrawerOpen = false" class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm transition-opacity"></div>
            
            <!-- Bottom Drawer Container -->
            <div class="fixed inset-x-0 bottom-0 max-h-[85vh] bg-white dark:bg-slate-900 rounded-t-[32px] border-t border-slate-150 dark:border-slate-800 shadow-2xl p-6 overflow-y-auto flex flex-col transition-all transform duration-300"
                 x-show="monthDrawerOpen"
                 x-transition:enter="transition ease-out duration-300 transform"
                 x-transition:enter-start="translate-y-full"
                 x-transition:enter-end="translate-y-0"
                 x-transition:leave="transition ease-in duration-200 transform"
                 x-transition:leave-start="translate-y-0"
                 x-transition:leave-end="translate-y-full">
                
                <!-- Swipe Indicator bar -->
                <div class="w-12 h-1.5 bg-slate-200 dark:bg-slate-700 rounded-full mx-auto mb-5 flex-shrink-0"></div>
                
                <div class="flex items-center justify-between mb-6 flex-shrink-0">
                    <h3 class="font-extrabold text-lg text-slate-850 dark:text-slate-100 font-heading">Select Attendance Month</h3>
                    <button @click="monthDrawerOpen = false" class="p-2 rounded-full bg-slate-50 dark:bg-slate-800 text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <!-- Option Lists -->
                <form action="{{ route('parent.student.attendance.index', $student) }}" method="GET" class="space-y-4">
                    <input type="hidden" name="quarter" value="{{ $selectedQuarter }}">
                    <input type="hidden" name="month" x-model="selectedMonthTemp">
                    
                    <div class="space-y-2.5">
                        <!-- All Months Option -->
                        <button type="button" @click="selectedMonthTemp = 'all'; $nextTick(() => $el.form.submit())"
                                :class="selectedMonthTemp === 'all' ? 'border-indigo-500 bg-indigo-50/50 dark:bg-indigo-950/20 text-indigo-650 dark:text-indigo-455' : 'border-slate-100 dark:border-slate-800 text-slate-750 dark:text-slate-350'"
                                class="w-full text-left px-4 py-3.5 border rounded-2xl font-bold text-sm flex items-center justify-between transition-colors">
                            <span>All Months</span>
                            <span x-show="selectedMonthTemp === 'all'" class="text-indigo-600">✓</span>
                        </button>

                        @foreach($availableMonths as $m)
                            <button type="button" @click="selectedMonthTemp = '{{ $m }}'; $nextTick(() => $el.form.submit())"
                                    :class="selectedMonthTemp === '{{ $m }}' ? 'border-indigo-500 bg-indigo-50/50 dark:bg-indigo-950/20 text-indigo-650 dark:text-indigo-455' : 'border-slate-100 dark:border-slate-800 text-slate-750 dark:text-slate-350'"
                                    class="w-full text-left px-4 py-3.5 border rounded-2xl font-bold text-sm flex items-center justify-between transition-colors">
                                <span>{{ \Carbon\Carbon::createFromFormat('Y-m', $m)->format('F Y') }}</span>
                                <span x-show="selectedMonthTemp === '{{ $m }}'" class="text-indigo-600">✓</span>
                            </button>
                        @endforeach
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-parent-layout>