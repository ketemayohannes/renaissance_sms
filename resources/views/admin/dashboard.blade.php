<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <span class="hidden lg:inline text-[17px] font-bold text-slate-800 dark:text-slate-100 whitespace-nowrap">Command Center</span>
            <div class="flex items-center gap-2">
                <!-- Division Selector -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="bg-white border border-slate-200 rounded-lg px-3 py-1.5 flex items-center gap-2.5 hover:border-indigo-500 transition-all shadow-sm group">
                        <div class="flex flex-col items-start">
                            <span class="hidden md:block text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] leading-none mb-1">Division</span>
                            <span class="text-[11px] font-black whitespace-nowrap text-slate-800 uppercase tracking-wider leading-none">{{ $selectedDivision?->name ?? 'All Divisions' }}</span>
                        </div>
                        <svg class="w-4 h-4 text-slate-400 group-hover:text-indigo-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div x-show="open" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                         x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                         @click.away="open = false" 
                         class="absolute right-0 mt-3 w-64 rounded-2xl bg-white shadow-2xl border border-slate-100 py-3 z-[100] ring-1 ring-black/5" 
                         x-cloak>
                        <div class="px-6 py-2 mb-2 border-b border-slate-50">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Select View</span>
                        </div>
                        <a href="{{ route('admin.dashboard') }}" class="flex items-center justify-between px-6 py-3 text-xs font-black uppercase tracking-widest transition-all {{ !$selectedDivision ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-50 hover:text-indigo-600' }}">
                            Global View
                            @if(!$selectedDivision)
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                            @endif
                        </a>
                        @foreach($divisions as $division)
                            <a href="{{ route('admin.dashboard', ['division_id' => $division->id]) }}" class="flex items-center justify-between px-6 py-3 text-xs font-black uppercase tracking-widest transition-all {{ $selectedDivision?->id == $division->id ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-50 hover:text-indigo-600' }}">
                                {{ $division->name }}
                                @if($selectedDivision?->id == $division->id)
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>

                <div class="hidden 2xl:block h-6 w-[1px] bg-slate-200 mx-1"></div>

                <div class="hidden 2xl:flex bg-indigo-50 border border-indigo-100 rounded-lg px-3 py-1.5 items-center gap-2 whitespace-nowrap">
                    <span class="w-2 h-2 rounded-full bg-indigo-500 animate-pulse"></span>
                    <span class="text-[11px] font-black text-indigo-700 uppercase tracking-wider">{{ $academicYear?->name ?? 'N/A' }}</span>
                </div>
            </div>
        </div>
    </x-slot>
    
    <div class="dashboard-compact">
        <!-- Premium KPI Row -->
        @php
            $attendanceStatus = match(true) {
                $stats['today_attendance'] >= 90 => ['Excellent', 'emerald'],
                $stats['today_attendance'] >= 75 => ['Stable', 'amber'],
                default => ['Critical', 'rose']
            };
            $kpis = [
                ['label' => 'Students', 'value' => number_format($stats['total_students']), 'note' => $selectedDivision ? $selectedDivision->name : 'Total Enrolled',
                 'badge' => ['Active', 'bg-emerald-500/10 text-emerald-600'], 'icon' => ['bg-indigo-500/10 group-hover:bg-indigo-500', 'text-indigo-600'],
                 'path' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
                ['label' => 'Faculty', 'value' => $stats['total_staff'], 'note' => $selectedDivision ? $selectedDivision->name : 'Academic & Support',
                 'badge' => ['Active', 'bg-indigo-500/10 text-indigo-600'], 'icon' => ['bg-emerald-500/10 group-hover:bg-emerald-500', 'text-emerald-600'],
                 'path' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'],
                ['label' => 'Presence', 'value' => $stats['today_attendance'].'%', 'note' => 'Daily Rate',
                 'badge' => [$attendanceStatus[0], 'bg-'.$attendanceStatus[1].'-500/10 text-'.$attendanceStatus[1].'-600'], 'icon' => ['bg-rose-500/10 group-hover:bg-rose-500', 'text-rose-600'],
                 'path' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4'],
            ];
        @endphp
        {{-- Badge colours used above, listed in full so Tailwind keeps them: bg-emerald-500/10 text-emerald-600 bg-amber-500/10 text-amber-600 bg-rose-500/10 text-rose-600 --}}
        {{-- Five cards side by side when attendance is pending (it leads the row), otherwise four. --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 dash-grid {{ $sectionsMissingAttendance > 0 ? 'lg:grid-cols-3 xl:grid-cols-5' : 'lg:grid-cols-4' }}">
            @if($sectionsMissingAttendance > 0)
            <!-- Attendance pending (school days only) -->
            <div class="glass-panel dash-card-sm !border-amber-300 dark:!border-amber-500/40 ring-1 ring-amber-200/60 dark:ring-amber-500/20 group hover:-translate-y-0.5 transition-all duration-300 flex flex-col justify-between">
                <div class="flex items-start justify-between gap-2">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-8 h-8 rounded-lg bg-amber-500/10 group-hover:bg-amber-500 flex items-center justify-center shrink-0 transition-all duration-300">
                            <svg class="w-4 h-4 text-amber-600 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <h4 class="dash-label !text-amber-600">Attendance Pending</h4>
                            <p class="dash-kpi-value">{{ $sectionsMissingAttendance }} <span class="text-[11px] font-bold text-slate-500 tracking-normal">section(s)</span></p>
                        </div>
                    </div>
                    <span class="px-1.5 py-0.5 bg-amber-500/10 text-amber-600 rounded text-[9px] font-black uppercase tracking-widest shrink-0">Today</span>
                </div>
                <a href="{{ route('admin.attendance.index') }}" class="mt-1.5 pt-1.5 border-t border-amber-100 dark:border-amber-500/20 flex items-center justify-between gap-1 text-[11px] font-black text-amber-600 hover:text-orange-600 dark:text-amber-400">
                    <span class="truncate">Mark Attendance Now</span>
                    <svg class="w-3.5 h-3.5 shrink-0 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </a>
            </div>
            @endif

            @foreach($kpis as $kpi)
            <div class="glass-panel dash-card-sm group hover:-translate-y-0.5 transition-all duration-300 flex flex-col justify-between">
                <div class="flex items-start justify-between gap-2">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-8 h-8 rounded-lg {{ $kpi['icon'][0] }} flex items-center justify-center shrink-0 transition-all duration-300">
                            <svg class="w-4 h-4 {{ $kpi['icon'][1] }} group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $kpi['path'] }}"></path>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <h4 class="dash-label">{{ $kpi['label'] }}</h4>
                            <p class="dash-kpi-value">{{ $kpi['value'] }}</p>
                        </div>
                    </div>
                    <span class="px-1.5 py-0.5 {{ $kpi['badge'][1] }} rounded text-[9px] font-black uppercase tracking-widest shrink-0">{{ $kpi['badge'][0] }}</span>
                </div>
                <p class="mt-1.5 pt-1.5 border-t border-slate-100 dark:border-slate-800 text-[10px] font-bold text-slate-400 italic truncate">{{ $kpi['note'] }}</p>
            </div>
            @endforeach

            <!-- Quick Access Menu -->
            <div class="glass-panel dash-card-sm bg-slate-900 overflow-hidden relative group">
                <div class="absolute inset-0 bg-gradient-to-br from-indigo-600/20 to-purple-600/20 opacity-0 group-hover:opacity-100 transition-opacity duration-700"></div>
                <h4 class="dash-label !text-indigo-400 mb-1.5 relative z-10">Power Actions</h4>
                <div class="flex flex-wrap gap-1 relative z-10">
                    @can('create students')
                    <a href="{{ route('admin.students.create') }}" class="flex-auto flex items-center justify-between gap-1 px-1.5 py-1.5 rounded-lg bg-white/5 hover:bg-white/10 border border-white/5 transition-all group/item">
                        <span class="text-[11px] font-bold text-white leading-4 tracking-tight whitespace-nowrap">Register Student</span>
                        <svg class="w-2.5 h-2.5 shrink-0 text-indigo-400 group-hover/item:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    </a>
                    @endcan
                    <a href="{{ route('admin.section-grades.index') }}" class="flex-auto flex items-center justify-between gap-1 px-1.5 py-1.5 rounded-lg bg-white/5 hover:bg-white/10 border border-white/5 transition-all group/item">
                        <span class="text-[11px] font-bold text-white leading-4 tracking-tight whitespace-nowrap">Batch Grading</span>
                        <svg class="w-2.5 h-2.5 shrink-0 text-emerald-400 group-hover/item:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    </a>
                    @can('create employees')
                    <a href="{{ route('admin.employees.create') }}" class="flex-auto flex items-center justify-between gap-1 px-1.5 py-1.5 rounded-lg bg-white/5 hover:bg-white/10 border border-white/5 transition-all group/item">
                        <span class="text-[11px] font-bold text-white leading-4 tracking-tight whitespace-nowrap">Onboard Staff</span>
                        <svg class="w-2.5 h-2.5 shrink-0 text-amber-400 group-hover/item:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    </a>
                    @endcan
                </div>
            </div>
        </div>

        <!-- Academic Calendar (school-wide schedule) -->
        <x-academic-calendar />

        <div class="grid grid-cols-1 lg:grid-cols-3 dash-grid">
            <!-- Enrollment Analytics -->
            <div class="glass-panel dash-card lg:col-span-2">
                <div class="dash-card-head">
                    <div>
                        <h3 class="dash-title">Enrollment Matrix</h3>
                        <p class="dash-subtitle">Student Distribution</p>
                    </div>
                </div>
                <div class="h-[150px]">
                    <canvas id="enrollmentChart"></canvas>
                </div>
            </div>

            <!-- Demographic Breakdown -->
            <div class="glass-panel dash-card">
                <div class="dash-card-head">
                    <h3 class="dash-title">Demographics</h3>
                </div>
                <div class="flex items-center gap-4">
                    <div class="h-[140px] w-[140px] shrink-0 relative">
                        <canvas id="genderChart"></canvas>
                    </div>
                    <div class="flex-1 min-w-0 space-y-1.5">
                        <div class="flex items-center justify-between px-2.5 py-1.5 bg-slate-50 dark:bg-slate-800/60 rounded-lg border border-slate-100 dark:border-slate-700">
                            <div class="flex items-center gap-2">
                                <div class="w-2 h-2 rounded-full bg-indigo-500"></div>
                                <span class="dash-label !text-slate-500">Male</span>
                            </div>
                            <span class="text-xs font-black text-slate-900 dark:text-slate-100">{{ $genderBreakdown['M'] ?? 0 }}</span>
                        </div>
                        <div class="flex items-center justify-between px-2.5 py-1.5 bg-slate-50 dark:bg-slate-800/60 rounded-lg border border-slate-100 dark:border-slate-700">
                            <div class="flex items-center gap-2">
                                <div class="w-2 h-2 rounded-full bg-rose-400"></div>
                                <span class="dash-label !text-slate-500">Female</span>
                            </div>
                            <span class="text-xs font-black text-slate-900 dark:text-slate-100">{{ $genderBreakdown['F'] ?? 0 }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Academic Performance Analytics (Full Width) -->
        <div class="grid grid-cols-1 lg:grid-cols-2 dash-grid">
            <div class="glass-panel dash-card min-w-0">
                <div class="dash-card-head">
                    <div>
                        <h3 class="dash-title">Academic Excellence</h3>
                        <p class="dash-subtitle">Subject Average Performance</p>
                    </div>

                    <!-- Selectors -->
                    <form id="academicExcellenceForm" class="flex flex-wrap gap-1.5">
                        @if(request('division_id'))
                            <input type="hidden" name="division_id" value="{{ request('division_id') }}">
                        @endif
                        <select name="grade_level_id" onchange="fetchAcademicExcellenceData()" class="dash-select" aria-label="Grade level">
                            @foreach($gradeLevels as $grade)
                                <option value="{{ $grade->id }}" {{ $selectedGradeLevelId == $grade->id ? 'selected' : '' }}>{{ $grade->name }}</option>
                            @endforeach
                        </select>
                        <select name="term_id" onchange="fetchAcademicExcellenceData()" class="dash-select" aria-label="Term">
                            <option value="yearly" {{ $selectedTermId === 'yearly' ? 'selected' : '' }}>Yearly</option>
                            @foreach($terms as $term)
                                <option value="{{ $term->id }}" {{ $selectedTermId == $term->id ? 'selected' : '' }}>{{ $term->name }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>

                <!-- Chart -->
                <div class="h-[160px] mb-2">
                    <canvas id="gradeAveragesChart"></canvas>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto custom-scrollbar relative" id="academicExcellenceTableContainer">
                    <div id="academicExcellenceLoading" class="absolute inset-0 bg-white/80 backdrop-blur-sm z-20 flex items-center justify-center hidden rounded-xl">
                        <svg class="animate-spin h-6 w-6 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>

                    <table class="w-full text-center">
                        <thead id="academicExcellenceThead">
                            <tr>
                                @foreach($subjectAverages as $avg)
                                <th class="text-[10px] font-black text-slate-400 uppercase tracking-widest pb-1 px-2 whitespace-nowrap">{{ $avg->subject_name }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody id="academicExcellenceTbody">
                            <tr>
                                @forelse($subjectAverages as $avg)
                                <td class="py-1 px-1.5">
                                    <span class="px-2 py-0.5 rounded-md text-[11px] font-black {{ $avg->average >= 75 ? 'bg-emerald-50 text-emerald-600' : ($avg->average >= 50 ? 'bg-amber-50 text-amber-600' : 'bg-rose-50 text-rose-600') }}">
                                        {{ $avg->average }}%
                                    </span>
                                </td>
                                @empty
                                <td class="py-3 text-center text-xs font-bold text-slate-400 italic">No data</td>
                                @endforelse
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Performance Distribution -->
            <div class="glass-panel dash-card min-w-0">
                <div class="dash-card-head">
                    <div>
                        <h3 class="dash-title">Performance Analysis</h3>
                        <p class="dash-subtitle">Score Distribution Per Subject</p>
                    </div>

                    <!-- Selectors -->
                    <div class="flex flex-wrap items-center gap-1.5">
                        <select id="dist_grade_level_id" onchange="onGradeChange()" class="dash-select" aria-label="Grade level">
                            @foreach($gradeLevels as $grade)
                                <option value="{{ $grade->id }}" {{ $selectedGradeLevelId == $grade->id ? 'selected' : '' }}>{{ $grade->name }}</option>
                            @endforeach
                        </select>
                        <select id="dist_subject_id" onchange="fetchPerformanceDistribution()" class="dash-select min-w-[100px]" aria-label="Subject">
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}">
                                    {{ strlen($subject->name) > 12 ? ($subject->code ?: $subject->name) : $subject->name }}
                                </option>
                            @endforeach
                        </select>
                        <select id="dist_term_id" onchange="fetchPerformanceDistribution()" class="dash-select" aria-label="Term">
                            @foreach($terms as $term)
                                <option value="{{ $term->id }}" {{ ($terms->where('is_grading_open', true)->first()?->id ?? $terms->last()?->id) == $term->id ? 'selected' : '' }}>{{ $term->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="h-[160px] relative">
                    <div id="distributionLoading" class="absolute inset-0 bg-white/80 backdrop-blur-sm z-20 flex items-center justify-center hidden rounded-xl">
                        <svg class="animate-spin h-6 w-6 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                    <canvas id="performanceDistributionChart"></canvas>
                </div>

                <div id="distributionSummary" class="mt-2 pt-2 flex justify-around text-center border-t border-slate-100 dark:border-slate-800">
                    <div>
                        <span class="block dash-label">0-49</span>
                        <span id="range_0_49" class="text-base font-black text-rose-600 leading-6">0</span>
                    </div>
                    <div>
                        <span class="block dash-label">50-74</span>
                        <span id="range_50_74" class="text-base font-black text-amber-500 leading-6">0</span>
                    </div>
                    <div>
                        <span class="block dash-label">75-100</span>
                        <span id="range_75_100" class="text-base font-black text-emerald-500 leading-6">0</span>
                    </div>
                </div>
            </div>
        </div>

        @role('Super Admin')
        {{-- items-start: health and the audit feed have different amounts of content, so neither is stretched. --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 dash-grid lg:items-start">
            <!-- System Status -->
            <div class="glass-panel dash-card">
                <div class="dash-card-head">
                    <h3 class="dash-title">System Health</h3>
                </div>
                @php
                    $statusColors = [
                        'Online' => 'bg-emerald-500',
                        'Idle' => 'bg-emerald-500',
                        'Warm' => 'bg-emerald-500',
                        'Offline' => 'bg-rose-500',
                    ];
                @endphp
                <div class="space-y-1.5">
                    @foreach(['database' => 'Database', 'queue' => 'Queue', 'cache' => 'Cache'] as $key => $label)
                    <div class="flex items-center justify-between px-3 py-2 bg-slate-50 dark:bg-slate-800/60 rounded-lg border border-slate-100 dark:border-slate-700">
                        <div class="flex items-center gap-2.5">
                            <div class="w-2 h-2 rounded-full {{ $statusColors[$systemHealth[$key]] ?? 'bg-slate-400' }}"></div>
                            <span class="text-[11px] font-black text-slate-500 uppercase tracking-widest">{{ $label }}</span>
                        </div>
                        <span class="text-xs font-black text-slate-900 dark:text-slate-100">{{ $systemHealth[$key] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Timeline of Activity -->
            <div class="glass-panel dash-card lg:col-span-2 min-w-0">
                <div class="dash-card-head">
                    <div>
                        <h3 class="dash-title">System Pulse</h3>
                        <p class="dash-subtitle">Live Audit Feed</p>
                    </div>
                    <a href="{{ route('admin.audit-logs.index') }}" class="px-3 py-1.5 bg-slate-100 dark:bg-slate-800 hover:bg-indigo-600 hover:text-white rounded-lg text-[10px] font-black uppercase tracking-[0.2em] transition-all">Full Log</a>
                </div>

                <div class="relative max-h-[15rem] overflow-y-auto custom-scrollbar pr-1">
                    <ul class="relative space-y-1 before:absolute before:left-[13px] before:top-2 before:bottom-2 before:w-[2px] before:bg-slate-100 dark:before:bg-slate-800">
                        @forelse($recentActivity as $log)
                            @php
                                $eventConfig = match(true) {
                                    str_contains($log->event, 'created') => ['Created', 'bg-emerald-500', 'text-emerald-500'],
                                    str_contains($log->event, 'updated') => ['Updated', 'bg-indigo-500', 'text-indigo-500'],
                                    str_contains($log->event, 'deleted') => ['Deleted', 'bg-rose-500', 'text-rose-500'],
                                    default => ['Action', 'bg-slate-400', 'text-slate-500']
                                };
                                $modelName = str_replace('_', ' ', class_basename($log->auditable_type));
                            @endphp
                            <li class="relative pl-9 py-1 group">
                                <div class="absolute left-0 top-1/2 -translate-y-1/2 w-7 h-7 rounded-lg bg-white dark:bg-slate-900 border-2 border-slate-100 dark:border-slate-700 flex items-center justify-center transition-all z-10">
                                    <div class="w-1.5 h-1.5 rounded-full {{ $eventConfig[1] }}"></div>
                                </div>
                                <div class="flex items-center justify-between gap-2 min-w-0">
                                    <p class="text-xs font-bold text-slate-900 dark:text-slate-100 truncate min-w-0">
                                        <span class="text-indigo-600 dark:text-indigo-400">{{ $log->user?->name ?? 'System' }}</span>
                                        {{ strtolower($eventConfig[0]) }} a {{ strtolower($modelName) }}
                                        <span class="ml-1 text-[10px] font-bold text-slate-400 uppercase tracking-wider">· {{ $log->created_at->diffForHumans() }}</span>
                                    </p>
                                    <span class="shrink-0 px-2 py-0.5 bg-slate-50 dark:bg-slate-800 text-slate-400 border border-slate-100 dark:border-slate-700 rounded-md text-[10px] font-black uppercase tracking-widest">#{{ $log->auditable_id }}</span>
                                </div>
                            </li>
                        @empty
                            <li class="text-center py-6">
                                <p class="text-xs font-black text-slate-400 uppercase tracking-widest italic">All Quiet on the Front</p>
                            </li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
        @endrole

        <!-- Grade Enrollment List -->
        <div class="glass-panel dash-card">
            <div class="dash-card-head">
                <h3 class="dash-title">Capacity Report</h3>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-2">
                @foreach($studentsByGrade as $grade)
                <div class="group flex items-center justify-between gap-2 px-3 py-2 bg-slate-50 dark:bg-slate-800/60 hover:bg-indigo-600 hover:shadow-lg hover:shadow-indigo-200 rounded-xl transition-all duration-300 border border-slate-100 dark:border-slate-700 hover:border-indigo-600 min-w-0">
                    <span class="text-[11px] font-black text-slate-700 dark:text-slate-200 uppercase tracking-wider group-hover:text-white transition-colors truncate">{{ $grade->grade_name }}</span>
                    <div class="flex items-center gap-2 shrink-0">
                        <span class="text-base font-black text-slate-900 dark:text-slate-100 group-hover:text-white transition-colors">{{ number_format($grade->count) }}</span>
                        <div class="w-1.5 h-6 bg-slate-200 group-hover:bg-white/20 rounded-full overflow-hidden">
                            <div class="bg-indigo-500 w-full h-1/2 rounded-full"></div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"
            integrity="sha384-9nhczxUqK87bcKHh20fSQcTGD4qq5GhayNYSYWqwBkINBhOfQLg/P5HG5lF1urn4"
            crossorigin="anonymous"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Enrollment Bar Chart - Modernized
            const enrollmentCtx = document.getElementById('enrollmentChart');
            if (enrollmentCtx) {
                const gradient = enrollmentCtx.getContext('2d').createLinearGradient(0, 0, 0, 400);
                gradient.addColorStop(0, '#4f46e5');
                gradient.addColorStop(1, '#818cf8');

                new Chart(enrollmentCtx.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: {!! json_encode($studentsByGrade->pluck('grade_name')) !!},
                        datasets: [{
                            label: 'Students',
                            data: {!! json_encode($studentsByGrade->pluck('count')) !!},
                            backgroundColor: gradient,
                            hoverBackgroundColor: '#4338ca',
                            borderRadius: 6,
                            maxBarThickness: 24,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { 
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#0f172a',
                                titleFont: { size: 14, weight: '900', family: 'Inter' },
                                bodyFont: { size: 13, family: 'Inter' },
                                padding: 16,
                                cornerRadius: 16,
                                displayColors: false,
                            }
                        },
                        scales: {
                            y: { 
                                beginAtZero: true, 
                                grid: { color: '#f8fafc', drawBorder: false },
                                ticks: { font: { size: 10, weight: '700' }, color: '#94a3b8' }
                            },
                            x: { 
                                grid: { display: false },
                                ticks: { 
                                    font: { size: 10, weight: '700' }, 
                                    color: '#94a3b8',
                                    maxRotation: 0,
                                    minRotation: 0,
                                    autoSkip: true,
                                    maxTicksLimit: 12
                                }
                            }
                        }
                    }
                });
            }
            
            // Gender Donut Chart - Modernized
            const genderCtx = document.getElementById('genderChart');
            if (genderCtx) {
                new Chart(genderCtx.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: ['Male', 'Female'],
                        datasets: [{
                            data: [{{ $genderBreakdown['M'] ?? 0 }}, {{ $genderBreakdown['F'] ?? 0 }}],
                            backgroundColor: ['#4f46e5', '#fb7185'],
                            borderWidth: 4,
                            borderColor: '#ffffff',
                            hoverOffset: 10,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '70%',
                        plugins: { 
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#0f172a',
                                padding: 16,
                                cornerRadius: 16,
                            }
                        }
                    }
                });
            }
            
            // Grade Averages Chart
            const averagesCtx = document.getElementById('gradeAveragesChart');
            window.academicExcellenceChart = null;
            
            if (averagesCtx) {
                const gradient = averagesCtx.getContext('2d').createLinearGradient(0, 400, 0, 0);
                gradient.addColorStop(0, '#34d399');
                gradient.addColorStop(1, '#10b981');

                const initialData = {!! json_encode($subjectAverages->pluck('average')) !!};
                const bgColors = initialData.map(val => val < 70 ? '#f43f5e' : gradient);
                const hoverColors = initialData.map(val => val < 70 ? '#e11d48' : '#059669');

                window.academicExcellenceChart = new Chart(averagesCtx.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: {!! json_encode($subjectAverages->pluck('subject_name')) !!},
                        datasets: [{
                            label: 'Average %',
                            data: initialData,
                            backgroundColor: bgColors,
                            hoverBackgroundColor: hoverColors,
                            borderRadius: 8,
                            maxBarThickness: 40,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { 
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#0f172a',
                                titleFont: { size: 14, weight: '900', family: 'Inter' },
                                bodyFont: { size: 13, family: 'Inter' },
                                padding: 16,
                                cornerRadius: 16,
                                displayColors: false,
                                callbacks: {
                                    label: function(ctx) { return ctx.parsed.y + '%'; }
                                }
                            }
                        },
                        scales: {
                            y: { 
                                min: 0,
                                max: 100,
                                beginAtZero: true, 
                                grid: { color: '#f8fafc', drawBorder: false },
                                ticks: { 
                                    font: { size: 10, weight: '700' }, 
                                    color: '#94a3b8',
                                    callback: function(v) { return v + '%'; }
                                }
                            },
                            x: { 
                                grid: { display: false },
                                ticks: { 
                                    font: { size: 10, weight: '700' }, 
                                    color: '#94a3b8',
                                    maxRotation: 0,
                                    minRotation: 0
                                }
                            }
                        }
                    }
                });
            }
        });

        // AJAX function for Academic Excellence
        function fetchAcademicExcellenceData() {
            const form = document.getElementById('academicExcellenceForm');
            const url = new URL('{{ route('admin.dashboard') }}', window.location.origin);
            const formData = new FormData(form);
            
            // Append form data to URL
            for (const [key, value] of formData.entries()) {
                url.searchParams.append(key, value);
            }
            url.searchParams.append('fetch_academic_excellence', '1');

            // Show loading
            document.getElementById('academicExcellenceLoading').classList.remove('hidden');

            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                const averages = data.subjectAverages;
                
                // Update Chart
                if (window.academicExcellenceChart) {
                    const newLabels = averages.map(a => a.subject_name);
                    const newData = averages.map(a => a.average);
                    
                    const averagesCtx = document.getElementById('gradeAveragesChart');
                    const gradient = averagesCtx.getContext('2d').createLinearGradient(0, 400, 0, 0);
                    gradient.addColorStop(0, '#34d399');
                    gradient.addColorStop(1, '#10b981');
                    
                    const newBgColors = newData.map(val => val < 70 ? '#f43f5e' : gradient);
                    const newHoverColors = newData.map(val => val < 70 ? '#e11d48' : '#059669');

                    window.academicExcellenceChart.data.labels = newLabels;
                    window.academicExcellenceChart.data.datasets[0].data = newData;
                    window.academicExcellenceChart.data.datasets[0].backgroundColor = newBgColors;
                    window.academicExcellenceChart.data.datasets[0].hoverBackgroundColor = newHoverColors;
                    window.academicExcellenceChart.update();
                }

                // Update Table
                const thead = document.getElementById('academicExcellenceThead');
                const tbody = document.getElementById('academicExcellenceTbody');
                
                let theadHtml = '<tr>';
                let tbodyHtml = '<tr>';
                
                if (averages.length === 0) {
                    theadHtml += '<th class="pb-1 px-2 text-[10px] font-black text-slate-400 uppercase tracking-widest whitespace-nowrap">Subject</th>';
                    tbodyHtml += '<td class="py-3 text-center text-xs font-bold text-slate-400 italic">No data</td>';
                } else {
                    averages.forEach(avg => {
                        theadHtml += `<th class="text-[10px] font-black text-slate-400 uppercase tracking-widest pb-1 px-2 whitespace-nowrap">${avg.subject_name}</th>`;
                        
                        let colorClass = 'bg-rose-50 text-rose-600';
                        if (avg.average >= 75) colorClass = 'bg-emerald-50 text-emerald-600';
                        else if (avg.average >= 50) colorClass = 'bg-amber-50 text-amber-600';
                        
                        tbodyHtml += `
                            <td class="py-1 px-1.5">
                                <span class="px-2 py-0.5 rounded-md text-[11px] font-black ${colorClass}">
                                    ${avg.average}%
                                </span>
                            </td>
                        `;
                    });
                }
                
                theadHtml += '</tr>';
                tbodyHtml += '</tr>';
                
                thead.innerHTML = theadHtml;
                tbody.innerHTML = tbodyHtml;
            })
            .catch(error => {
                console.error('Error fetching academic excellence data:', error);
            })
            .finally(() => {
                document.getElementById('academicExcellenceLoading').classList.add('hidden');
            });
        }

        document.addEventListener('DOMContentLoaded', function() {

            // Performance Distribution Chart Initializer
            const distCanvas = document.getElementById('performanceDistributionChart');
            if (distCanvas) {
                window.performanceDistributionChart = new Chart(distCanvas.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: ['0-49', '50-74', '75-100'],
                        datasets: [{
                            label: 'Students',
                            data: [0, 0, 0],
                            backgroundColor: ['#f43f5e', '#f59e0b', '#10b981'],
                            hoverBackgroundColor: ['#e11d48', '#d97706', '#059669'],
                            borderRadius: 6,
                            maxBarThickness: 40,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { 
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#0f172a',
                                titleFont: { size: 14, weight: '900', family: 'Inter' },
                                bodyFont: { size: 13, family: 'Inter' },
                                padding: 16,
                                cornerRadius: 16,
                                displayColors: false
                            }
                        },
                        scales: {
                            y: { 
                                beginAtZero: true, 
                                grid: { color: '#f8fafc', drawBorder: false },
                                ticks: { font: { size: 10, weight: '700', family: 'Inter' }, color: '#94a3b8' }
                            },
                            x: { 
                                grid: { display: false },
                                ticks: { font: { size: 10, weight: '700', family: 'Inter' }, color: '#94a3b8' }
                            }
                        }
                    }
                });
                
            }

            // Initial fetch safely after chart is ready
            console.log('Dashboard loaded, initializing distribution...');
            setTimeout(() => {
                fetchPerformanceDistribution();
            }, 200);
        });

        // AJAX logic for Performance Distribution
        function fetchPerformanceDistribution() {
            const termEl = document.getElementById('dist_term_id');
            const gradeEl = document.getElementById('dist_grade_level_id');
            const subjectEl = document.getElementById('dist_subject_id');

            if (!termEl || !gradeEl || !subjectEl) {
                console.warn('Distribution elements not found');
                return;
            }

            const termId = termEl.value;
            const gradeLevelId = gradeEl.value;
            const subjectId = subjectEl.value;

            console.log('Fetching distribution for:', { termId, gradeLevelId, subjectId });

            if (!termId || !gradeLevelId || !subjectId) {
                console.warn('Missing distribution parameters');
                return;
            }

            const loading = document.getElementById('distributionLoading');
            if (loading) loading.classList.remove('hidden');

            fetch(`{{ route('admin.dashboard') }}?fetch_distribution=1&term_id=${termId}&grade_level_id=${gradeLevelId}&subject_id=${subjectId}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.json())
            .then(data => {
                console.log('Distribution data received:', data);
                // Update text summary
                const r1 = document.getElementById('range_0_49');
                const r2 = document.getElementById('range_50_74');
                const r3 = document.getElementById('range_75_100');
                if (r1) r1.textContent = data.data[0];
                if (r2) r2.textContent = data.data[1];
                if (r3) r3.textContent = data.data[2];

                // Update Chart
                if (window.performanceDistributionChart) {
                    window.performanceDistributionChart.data.datasets[0].data = data.data;
                    window.performanceDistributionChart.update();
                }
            })
            .catch(err => console.error('Performance Analysis Fetch Error:', err))
            .finally(() => {
                if (loading) loading.classList.add('hidden');
            });
        }
        window.fetchPerformanceDistribution = fetchPerformanceDistribution;

        function onGradeChange() {
            const gradeLevelId = document.getElementById('dist_grade_level_id').value;
            const subjectSelect = document.getElementById('dist_subject_id');
            
            console.log('Grade changed to:', gradeLevelId, 'Fetching subjects...');

            fetch(`{{ route('admin.dashboard') }}?fetch_subjects=1&grade_level_id=${gradeLevelId}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.json())
            .then(subjects => {
                console.log('Subjects received:', subjects);
                subjectSelect.innerHTML = '';
                subjects.forEach(s => {
                    const opt = document.createElement('option');
                    opt.value = s.id;
                    // Use code if name is long (>12 chars), fallback to name
                    opt.textContent = s.name.length > 12 ? (s.code || s.name) : s.name;
                    subjectSelect.appendChild(opt);
                });
                
                // Trigger distribution fetch for the new subjects
                setTimeout(() => fetchPerformanceDistribution(), 100);
            })
            .catch(err => console.error('Subject Fetch Error:', err));
        }
        window.onGradeChange = onGradeChange;
    </script>
    @endpush

    <!-- Performance Footer -->
    <div class="mt-12 text-center pb-8">
        <span class="text-[10px] font-black uppercase tracking-widest text-slate-300">
            Engine Performance: {{ $executionTime ?? 'N/A' }}s | Cached Metrics Ready
        </span>
    </div>
</x-admin-layout>
