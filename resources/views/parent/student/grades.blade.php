<x-parent-layout header="{{ $student->full_name }}'s Academic Report">
    <div class="space-y-8" x-data="{ mobileDrawerOpen: false, selectedPeriodTemp: '{{ $selectedPeriod }}' }">
        <!-- Modern Premium Banner -->
        <div class="relative bg-gradient-to-r from-violet-600 via-indigo-600 to-indigo-700 rounded-3xl p-6 lg:p-8 text-white overflow-hidden shadow-lg shadow-indigo-100 dark:shadow-none">
            <!-- Translucent decorative glow shapes -->
            <div class="absolute -right-10 -top-10 w-48 h-48 bg-white/10 rounded-full blur-2xl"></div>
            <div class="absolute -right-20 -bottom-20 w-64 h-64 bg-indigo-500/20 rounded-full blur-3xl"></div>
            
            <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-8">
                <!-- Left: Student Info Summary -->
                <div class="flex items-center gap-5">
                    <div class="relative flex-shrink-0">
                        <div class="w-16 h-16 rounded-2xl bg-white/10 backdrop-blur-md flex items-center justify-center font-bold text-xl border border-white/15 shadow-inner uppercase tracking-wider relative overflow-hidden">
                            @if($student->photo)
                                <img src="/storage/{{ $student->photo }}" alt="{{ $student->full_name }}" class="w-full h-full object-cover rounded-2xl">
                            @else
                                {{ substr($student->first_name, 0, 1) }}{{ substr($student->father_name, 0, 1) }}
                            @endif
                        </div>
                        <span class="absolute -bottom-1 -right-1 flex h-4 w-4">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-4 w-4 bg-emerald-500 border border-slate-900"></span>
                        </span>
                    </div>
                    <div class="space-y-1">
                        <span class="text-indigo-300 text-[10px] font-black tracking-widest uppercase block leading-none">Official Report Card</span>
                        <h2 class="text-2xl font-black font-heading tracking-tight text-white leading-none mt-1">
                            {{ $student->full_name }}
                        </h2>
                        <div class="flex flex-wrap items-center gap-2 mt-2">
                            <span class="px-2.5 py-0.5 bg-white/10 backdrop-blur-md border border-white/10 text-indigo-200 rounded-lg text-[10px] font-extrabold uppercase">
                                @if(str_starts_with(strtolower($student->gradeLevel->name ?? ''), 'grade'))
                                    {{ $student->gradeLevel->name }}
                                @else
                                    Grade {{ $student->gradeLevel->name ?? 'N/A' }}
                                @endif
                            </span>
                            <span class="px-2.5 py-0.5 bg-white/10 backdrop-blur-md border border-white/10 text-indigo-200 rounded-lg text-[10px] font-extrabold uppercase">
                                Section {{ $student->section->name ?? 'N/A' }}
                            </span>
                            <span class="px-2.5 py-0.5 bg-white/10 backdrop-blur-md border border-white/10 text-indigo-200 rounded-lg text-[10px] font-extrabold uppercase">
                                Roll #{{ $student->currentEnrollment->roll_number ?? 'N/A' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Right: High-end Filter Form -->
                <div class="bg-white/5 backdrop-blur-xl border border-white/10 rounded-2xl p-4 w-full max-w-xs lg:w-72 min-w-0 self-start lg:self-auto flex-shrink-0">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[10px] font-black uppercase tracking-wider text-indigo-200">Select Academic Period</span>
                        <span class="text-[9px] font-mono text-indigo-400 bg-indigo-500/10 px-2 py-0.5 rounded">Filter</span>
                    </div>
                    <!-- Desktop View Selector -->
                    <form action="{{ route('parent.student.grades.index', $student) }}" method="GET" class="hidden sm:block w-full">
                        <select name="period" onchange="this.form.submit()" class="w-full px-4 py-2.5 border border-white/10 bg-slate-900/80 text-white rounded-xl font-bold text-xs focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all cursor-pointer shadow-inner">
                            <option value="all" {{ $selectedPeriod == 'all' ? 'selected' : '' }}>All Records</option>
                            <option disabled class="text-slate-500 bg-slate-950">── Quarters ──</option>
                            @foreach($quarters as $quarter)
                                <option value="term_{{ $quarter->id }}" {{ $selectedPeriod == 'term_'.$quarter->id ? 'selected' : '' }}>{{ $quarter->name }}</option>
                            @endforeach
                            <option disabled class="text-slate-500 bg-slate-950">── Semesters ──</option>
                            @foreach($semesters as $semester)
                                <option value="semester_{{ $semester->id }}" {{ $selectedPeriod == 'semester_'.$semester->id ? 'selected' : '' }}>{{ $semester->name }}</option>
                            @endforeach
                            <option disabled class="text-slate-500 bg-slate-950">── Yearly ──</option>
                            <option value="yearly" {{ $selectedPeriod == 'yearly' ? 'selected' : '' }}>Yearly Report</option>
                        </select>
                    </form>
                    <!-- Mobile View Selector Button -->
                    <div class="sm:hidden w-full">
                        <button type="button" @click="mobileDrawerOpen = true" class="w-full flex items-center justify-between px-4 py-2.5 border border-white/10 bg-slate-900/80 text-white rounded-xl font-bold text-xs shadow-inner">
                            <span class="truncate pr-2">
                                {{ $selectedPeriod === 'all' ? 'All Records' : ($selectedPeriod === 'yearly' ? 'Yearly Report' : ($quarters->find((int) str_replace('term_', '', $selectedPeriod))?->name ?? ($semesters->find((int) str_replace('semester_', '', $selectedPeriod))?->name ?? 'Select Period'))) }}
                            </span>
                            <svg class="w-4 h-4 text-indigo-300 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Period Info Title Bar -->
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800/80 pb-4 px-2">
            <div class="flex items-center gap-3">
                <div class="w-2.5 h-6 rounded bg-indigo-600"></div>
                <h2 class="text-lg font-black text-slate-800 dark:text-slate-100 font-heading uppercase tracking-wider">{{ $periodName }} Overview</h2>
            </div>
            <span class="text-xs text-slate-450 dark:text-slate-500 font-bold bg-slate-100 dark:bg-slate-800 px-3 py-1 rounded-xl">
                Active Year: {{ \App\Helpers\CachedData::activeAcademicYear()?->name ?? 'N/A' }}
            </span>
        </div>

        <!-- Gradebook Details -->
        <div class="space-y-8">
            @if(empty($groupedMarks) || count($groupedMarks) === 0)
                <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl p-16 text-center shadow-sm relative overflow-hidden group">
                    <div class="absolute -right-10 -top-10 w-40 h-40 bg-indigo-500/5 rounded-full blur-2xl"></div>
                    <div class="w-16 h-16 rounded-2xl bg-slate-50 dark:bg-slate-850 flex items-center justify-center text-slate-400 dark:text-slate-600 mx-auto mb-5 border border-slate-100 dark:border-slate-800">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-extrabold text-slate-800 dark:text-slate-200 font-heading">No academic records found</h3>
                    <p class="text-slate-500 dark:text-slate-400 mt-2 max-w-sm mx-auto text-sm">Assessment scores and official report card data will appear here once they are published by the academic staff.</p>
                </div>
            @else
                @foreach($groupedMarks as $termName => $marks)
                    <div x-data="{ open: false }" class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl shadow-sm hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 overflow-hidden">
                        <!-- Card Header with Premium Collapsible Toggle -->
                        <button @click="open = !open" class="w-full flex flex-col xl:flex-row xl:items-center xl:justify-between px-6 py-5 bg-slate-50/50 dark:bg-slate-800/40 border-b border-slate-100 dark:border-slate-800 text-left gap-4 select-none">
                            <div class="flex items-center gap-3">
                                <div class="w-2.5 h-2.5 rounded-full bg-indigo-600 animate-pulse"></div>
                                <span class="font-black text-slate-850 dark:text-slate-100 font-heading text-lg tracking-tight">{{ $termName }} Report</span>
                            </div>
                            
                            @php
                                $termRecord = isset($termRecords) ? ($termRecords[$termName] ?? null) : null;
                            @endphp
                            
                            <div class="flex flex-wrap items-center gap-3.5 xl:self-end">
                                <!-- Average Badge -->
                                @if($termRecord && $termRecord->average_score !== null)
                                    <div class="bg-indigo-50 dark:bg-indigo-950/40 text-indigo-650 dark:text-indigo-300 border border-indigo-100/50 dark:border-indigo-900/30 px-3 py-1.5 rounded-xl font-bold flex items-center gap-1.5 shadow-sm text-xs">
                                        <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                        </svg>
                                        <span class="text-[9px] font-black uppercase tracking-wider hidden sm:inline opacity-70">Average</span>
                                        <span>{{ number_format($termRecord->average_score, 1) }}%</span>
                                    </div>
                                @endif

                                <!-- Rank Badge -->
                                @if($termRecord && $termRecord->rank !== null)
                                    <div class="bg-amber-50 dark:bg-amber-950/40 text-amber-650 dark:text-amber-300 border border-amber-100/50 dark:border-amber-900/30 px-3 py-1.5 rounded-xl font-bold flex items-center gap-1.5 shadow-sm text-xs">
                                        <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 013 3h-15a3 3 0 013-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.75M9 15.375V12M12 3v12.375m0-12.375a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z"></path>
                                        </svg>
                                        <span class="text-[9px] font-black uppercase tracking-wider hidden sm:inline opacity-70">Rank</span>
                                        <span>#{{ $termRecord->rank }}<span class="text-[10px] font-bold opacity-60">/{{ $termRecord->rank_out_of }}</span></span>
                                    </div>
                                @endif

                                <!-- Conduct Badge -->
                                @if($termRecord)
                                    <div class="bg-emerald-50 dark:bg-emerald-950/40 text-emerald-650 dark:text-emerald-300 border border-emerald-100/50 dark:border-emerald-900/30 px-3 py-1.5 rounded-xl font-bold flex items-center gap-1.5 shadow-sm text-xs">
                                        <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z"></path>
                                        </svg>
                                        <span class="text-[9px] font-black uppercase tracking-wider hidden sm:inline opacity-70">Conduct</span>
                                        <span>{{ $termRecord->conduct_grade ?? 'A' }}</span>
                                    </div>
                                @endif

                                <!-- Absence Badge -->
                                @if($termRecord)
                                    <div class="bg-rose-50 dark:bg-rose-950/40 text-rose-650 dark:text-rose-300 border border-rose-100/50 dark:border-rose-900/30 px-3 py-1.5 rounded-xl font-bold flex items-center gap-1.5 shadow-sm text-xs">
                                        <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"></path>
                                        </svg>
                                        <span class="text-[9px] font-black uppercase tracking-wider hidden sm:inline opacity-70">Absence</span>
                                        <span>
                                            @if($termRecord->days_absent !== null)
                                                {{ $termRecord->days_absent }} day{{ $termRecord->days_absent != 1 ? 's' : '' }}
                                            @else
                                                —
                                            @endif
                                        </span>
                                    </div>
                                @endif
                                
                                <div class="w-8 h-8 rounded-xl bg-slate-100/80 dark:bg-slate-800/85 flex items-center justify-center text-slate-500 dark:text-slate-400 shadow-sm border border-slate-200/50 dark:border-slate-700/50 hover:bg-indigo-50 dark:hover:bg-slate-700 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all">
                                    <svg class="w-4 h-4 transition-transform duration-300" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </div>
                            </div>
                        </button>
                        
                        @php
                            $uniqueAssessments = $marks->map(fn($m) => $m->assessmentTemplate)
                                                             ->filter()
                                                             ->unique('name')
                                                             ->sortBy('id')
                                                             ->values();
                            $groupedBySubject = $marks->groupBy('subject.name');
                        @endphp
                        
                        <!-- Premium Interactive Table -->
                        <div x-show="open" x-transition:enter="transition ease-out duration-250" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="bg-slate-50/20 dark:bg-slate-900/20 text-slate-400 dark:text-slate-500 uppercase tracking-widest text-[9px] font-black border-b border-slate-100 dark:border-slate-800 select-none">
                                        <th class="text-left px-6 py-4 sticky left-0 bg-slate-50 dark:bg-slate-900 z-10 border-r border-slate-100 dark:border-slate-800 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.1)]">Subject</th>
                                        @foreach($uniqueAssessments as $assessment)
                                            <th class="text-center px-4 py-4 min-w-[110px]">
                                                <span class="block text-slate-700 dark:text-slate-350">{{ $assessment->name }}</span>
                                                <span class="block text-[8px] font-bold text-indigo-450 dark:text-slate-500 mt-0.5">Max {{ number_format($assessment->max_score, 0) }} pts</span>
                                            </th>
                                        @endforeach
                                        <th class="text-center px-6 py-4 min-w-[140px]">Total Score</th>
                                        <th class="text-center px-6 py-4 min-w-[120px]">Academic Standing</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-850">
                                    @foreach($groupedBySubject as $subjectName => $subjectMarks)
                                        @php
                                            $subjectTotal = 0;
                                            $subjectMax = 0;
                                            $hasComponents = $subjectMarks->contains(function($m) {
                                                return $m->assessmentTemplate && $m->assessmentTemplate->name !== 'Term Total';
                                            });
                                        @endphp
                                        <tr class="hover:bg-slate-50/40 dark:hover:bg-slate-800/20 transition-all duration-200">
                                            <!-- Subject Column -->
                                            <td class="px-6 py-4 font-bold text-slate-800 dark:text-slate-200 sticky left-0 bg-white dark:bg-slate-900 z-10 border-r border-slate-100 dark:border-slate-855 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.1)]">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-950/40 text-indigo-650 dark:text-indigo-400 flex items-center justify-center flex-shrink-0 border border-indigo-100/50 dark:border-indigo-900/30">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                                        </svg>
                                                    </div>
                                                    <span class="font-extrabold text-slate-850 dark:text-slate-100 font-heading">{{ $subjectName }}</span>
                                                </div>
                                            </td>

                                            <!-- Dynamic Assessment Columns -->
                                            @foreach($uniqueAssessments as $assessment)
                                                @php
                                                    $isRelevant = $hasComponents 
                                                        ? ($assessment->name !== 'Term Total')
                                                        : ($assessment->name === 'Term Total');
                                                    
                                                    $score = null;
                                                    if ($isRelevant) {
                                                        $mark = $subjectMarks->first(function($m) use ($assessment) {
                                                            return $m->assessmentTemplate && $m->assessmentTemplate->name === $assessment->name;
                                                        });
                                                        $score = $mark ? $mark->score : null;
                                                        if ($score !== null) {
                                                            $subjectTotal += $score;
                                                        }
                                                        $subjectMax += $assessment->max_score;
                                                    }
                                                @endphp
                                                <td class="px-4 py-4 text-center">
                                                    @if($isRelevant)
                                                        @if($score !== null)
                                                            <span class="font-extrabold text-slate-800 dark:text-slate-200 font-mono text-sm">
                                                                {{ number_format($score, 1) }}
                                                            </span>
                                                        @else
                                                            <span class="text-slate-350 dark:text-slate-650 font-bold">-</span>
                                                        @endif
                                                    @else
                                                        <span class="text-slate-200 dark:text-slate-800/40 font-bold opacity-30 select-none">—</span>
                                                    @endif
                                                </td>
                                            @endforeach

                                            @php
                                                // Computed Semester / Yearly rows have no per-component columns, so the
                                                // column loop above leaves the total at 0. Derive it directly from the
                                                // resolved marks (out of 100) so the summary rows show real values.
                                                if ($uniqueAssessments->isEmpty()) {
                                                    $subjectTotal = $subjectMarks->sum('score');
                                                    $subjectMax = 100;
                                                }
                                            @endphp

                                            <!-- Total Score Cell with elegant Gradient Progress Bar -->
                                            @php
                                                $percentage = $subjectMax > 0 ? ($subjectTotal / $subjectMax) * 100 : 0;
                                                $scoreColor = match(true) {
                                                    $percentage >= 75 => 'text-emerald-600 dark:text-emerald-400',
                                                    $percentage >= 50 => 'text-indigo-600 dark:text-indigo-400',
                                                    default => 'text-rose-600 dark:text-rose-455'
                                                };
                                                $barColor = match(true) {
                                                    $percentage >= 75 => 'from-emerald-500 to-teal-400',
                                                    $percentage >= 50 => 'from-indigo-500 to-violet-400',
                                                    default => 'from-rose-500 to-red-400'
                                                };
                                            @endphp
                                            <td class="px-6 py-4 text-center">
                                                <div class="flex flex-col items-center justify-center gap-1.5">
                                                    <div class="flex items-baseline gap-0.5">
                                                        <span class="{{ $scoreColor }} text-sm font-black font-mono">{{ number_format($subjectTotal, 1) }}</span>
                                                        <span class="text-slate-400 dark:text-slate-500 text-[10px] font-bold">/ {{ number_format($subjectMax, 0) }}</span>
                                                    </div>
                                                    <div class="w-24 h-1 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden border border-slate-200/10 dark:border-slate-700/10 shadow-inner">
                                                        <div class="h-full bg-gradient-to-r {{ $barColor }} rounded-full" style="width: {{ min(100, max(0, $percentage)) }}%"></div>
                                                    </div>
                                                </div>
                                            </td>
                                            
                                            <!-- Academic Standing Status -->
                                            <td class="px-6 py-4 text-center">
                                                @php
                                                    $status = match(true) {
                                                        $percentage >= 75 => ['Excellent', 'text-emerald-700 bg-emerald-50 dark:text-emerald-300 dark:bg-emerald-950/60 border border-emerald-100/50 dark:border-emerald-900/30', '<svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"></path></svg>'],
                                                        $percentage >= 50 => ['Passing', 'text-indigo-700 bg-indigo-50 dark:text-indigo-300 dark:bg-indigo-950/60 border border-indigo-100/50 dark:border-indigo-900/30', '<svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"></path></svg>'],
                                                        default => ['Failing', 'text-rose-700 bg-rose-50 dark:text-rose-300 dark:bg-rose-950/60 border border-rose-100/50 dark:border-rose-900/30', '<svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"></path></svg>']
                                                    };
                                                @endphp
                                                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-xl text-[9px] font-black uppercase tracking-wider {{ $status[1] }} select-none">
                                                    {!! $status[2] !!}
                                                    {{ $status[0] }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>

    <!-- Mobile Drawer Backdrop & Drawer -->
    <div x-cloak x-show="mobileDrawerOpen" class="fixed inset-0 z-[100] sm:hidden" x-transition>
        <!-- Backdrop -->
        <div @click="mobileDrawerOpen = false" class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm transition-opacity"></div>
        
        <!-- Bottom Drawer Container -->
        <div class="fixed inset-x-0 bottom-0 max-h-[85vh] bg-white dark:bg-slate-900 rounded-t-[32px] border-t border-slate-150 dark:border-slate-800 shadow-2xl p-6 overflow-y-auto flex flex-col transition-all transform duration-300"
             x-show="mobileDrawerOpen"
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="translate-y-full"
             x-transition:enter-end="translate-y-0"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="translate-y-0"
             x-transition:leave-end="translate-y-full">
            
            <!-- Swipe Indicator bar -->
            <div class="w-12 h-1.5 bg-slate-200 dark:bg-slate-700 rounded-full mx-auto mb-5 flex-shrink-0"></div>
            
            <div class="flex items-center justify-between mb-6 flex-shrink-0">
                <h3 class="font-extrabold text-lg text-slate-850 dark:text-slate-100 font-heading">Select Academic Period</h3>
                <button @click="mobileDrawerOpen = false" class="p-2 rounded-full bg-slate-50 dark:bg-slate-800 text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <!-- Option Lists -->
            <form action="{{ route('parent.student.grades.index', $student) }}" method="GET" class="space-y-4">
                <input type="hidden" name="period" x-model="selectedPeriodTemp">
                
                <div class="space-y-2.5">
                    <!-- All Records Option -->
                    <button type="button" @click="selectedPeriodTemp = 'all'; $nextTick(() => $el.form.submit())"
                            :class="selectedPeriodTemp === 'all' ? 'border-indigo-500 bg-indigo-50/50 dark:bg-indigo-950/20 text-indigo-650 dark:text-indigo-450' : 'border-slate-100 dark:border-slate-800 text-slate-750 dark:text-slate-350'"
                            class="w-full text-left px-4 py-3.5 border rounded-2xl font-bold text-sm flex items-center justify-between transition-colors">
                        <span>All Records</span>
                        <span x-show="selectedPeriodTemp === 'all'" class="text-indigo-600">✓</span>
                    </button>

                    <!-- Quarters header -->
                    <div class="text-[10px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500 pt-2 px-1">Quarters</div>
                    @foreach($quarters as $quarter)
                        <button type="button" @click="selectedPeriodTemp = 'term_{{ $quarter->id }}'; $nextTick(() => $el.form.submit())"
                                :class="selectedPeriodTemp === 'term_{{ $quarter->id }}' ? 'border-indigo-500 bg-indigo-50/50 dark:bg-indigo-950/20 text-indigo-650 dark:text-indigo-455' : 'border-slate-100 dark:border-slate-800 text-slate-750 dark:text-slate-350'"
                                class="w-full text-left px-4 py-3.5 border rounded-2xl font-bold text-sm flex items-center justify-between transition-colors">
                            <span>{{ $quarter->name }}</span>
                            <span x-show="selectedPeriodTemp === 'term_{{ $quarter->id }}'" class="text-indigo-600">✓</span>
                        </button>
                    @endforeach

                    <!-- Semesters header -->
                    <div class="text-[10px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500 pt-2 px-1">Semesters</div>
                    @foreach($semesters as $semester)
                        <button type="button" @click="selectedPeriodTemp = 'semester_{{ $semester->id }}'; $nextTick(() => $el.form.submit())"
                                :class="selectedPeriodTemp === 'semester_{{ $semester->id }}' ? 'border-indigo-500 bg-indigo-50/50 dark:bg-indigo-950/20 text-indigo-650 dark:text-indigo-455' : 'border-slate-100 dark:border-slate-800 text-slate-750 dark:text-slate-350'"
                                class="w-full text-left px-4 py-3.5 border rounded-2xl font-bold text-sm flex items-center justify-between transition-colors">
                            <span>{{ $semester->name }}</span>
                            <span x-show="selectedPeriodTemp === 'semester_{{ $semester->id }}'" class="text-indigo-600">✓</span>
                        </button>
                    @endforeach

                    <!-- Yearly header -->
                    <div class="text-[10px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500 pt-2 px-1">Yearly</div>
                    <button type="button" @click="selectedPeriodTemp = 'yearly'; $nextTick(() => $el.form.submit())"
                            :class="selectedPeriodTemp === 'yearly' ? 'border-indigo-500 bg-indigo-50/50 dark:bg-indigo-950/20 text-indigo-650 dark:text-indigo-455' : 'border-slate-100 dark:border-slate-800 text-slate-750 dark:text-slate-350'"
                            class="w-full text-left px-4 py-3.5 border rounded-2xl font-bold text-sm flex items-center justify-between transition-colors">
                        <span>Yearly Report</span>
                        <span x-show="selectedPeriodTemp === 'yearly'" class="text-indigo-600">✓</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
</x-parent-layout>