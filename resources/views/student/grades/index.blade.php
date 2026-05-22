<x-student-layout header="My Gradebook">

    <div class="space-y-8">
        
        <!-- Grades Header Banner -->
        <div class="relative overflow-hidden rounded-[3rem] bg-slate-900 p-8 md:p-12 shadow-2xl glass-panel border-0">
            <div class="absolute inset-0 bg-gradient-to-br from-indigo-600/30 via-transparent to-purple-500/30 opacity-70"></div>
            <div class="absolute -top-24 -right-24 w-96 h-96 bg-indigo-500/20 rounded-full blur-3xl mix-blend-screen pointer-events-none"></div>
            
            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <span class="px-3 py-1 bg-indigo-500/30 text-indigo-200 border border-indigo-400/30 rounded-xl text-xs font-black uppercase tracking-widest">Academic Report</span>
                    <h1 class="text-3xl md:text-5xl font-black text-white font-heading tracking-tight mt-3 mb-2">My Gradebook</h1>
                    <p class="text-indigo-100/80 text-base font-medium">
                        {{ $student->full_name }} • <span class="text-indigo-300 font-extrabold">{{ $enrollment->section->gradeLevel->name }} - Section {{ $enrollment->section->name }}</span>
                    </p>
                </div>
                
                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                    <!-- Filter Dropdown -->
                    <form action="{{ route('student.grades.index') }}" method="GET" class="flex items-center gap-2">
                        <select name="period" onchange="this.form.submit()" class="rounded-2xl border-white/20 bg-white/10 text-white font-bold text-sm focus:ring-indigo-500 focus:border-indigo-500 shadow-lg backdrop-blur-md transition-all px-4 py-2.5">
                            <option value="all" class="text-slate-900" {{ $selectedPeriod == 'all' ? 'selected' : '' }}>All Records</option>
                            <option disabled class="text-slate-400">── Quarters ──</option>
                            @foreach($quarters as $quarter)
                                <option value="term_{{ $quarter->id }}" class="text-slate-900" {{ $selectedPeriod == 'term_'.$quarter->id ? 'selected' : '' }}>{{ $quarter->name }}</option>
                            @endforeach
                            <option disabled class="text-slate-400">── Semesters ──</option>
                            @foreach($semesters as $semester)
                                <option value="semester_{{ $semester->id }}" class="text-slate-900" {{ $selectedPeriod == 'semester_'.$semester->id ? 'selected' : '' }}>{{ $semester->name }}</option>
                            @endforeach
                            <option disabled class="text-slate-400">── Yearly ──</option>
                            <option value="yearly" class="text-slate-900" {{ $selectedPeriod == 'yearly' ? 'selected' : '' }}>Yearly Report</option>
                        </select>
                    </form>

                    <!-- Academic Year Badge -->
                    <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-2xl p-3 flex items-center gap-3 shadow-lg">
                        <div class="w-10 h-10 rounded-xl bg-indigo-500/30 flex items-center justify-center text-indigo-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <div>
                            <span class="block text-[8px] font-black text-indigo-200 uppercase tracking-widest leading-none mb-1">Academic Year</span>
                            <span class="text-xs font-black text-white uppercase tracking-wider">
                                {{ $enrollment->academicYear->name }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Period Action Row -->
        <div class="mb-6 flex justify-between items-center px-4">
            <h2 class="text-xl font-black text-slate-800 dark:text-slate-100 font-heading uppercase tracking-widest">{{ $periodName }}</h2>
            @if($selectedPeriod !== 'all')
                <a href="{{ route('student.grades.download', ['period' => $selectedPeriod]) }}" class="inline-flex items-center px-5 py-2.5 bg-gradient-to-r from-indigo-500 to-indigo-600 border border-transparent rounded-2xl font-bold text-xs text-white uppercase tracking-widest hover:from-indigo-600 hover:to-indigo-700 active:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition duration-150 shadow-md shadow-indigo-200 dark:shadow-none">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                    </svg> Download PDF
                </a>
            @endif
        </div>

        <!-- Grade Matrices per Period -->
        @forelse($grades as $termName => $termGrades)
            @php
                // Extract unique assessment templates for this specific period
                // Use unique('name') to collapse multiple "Term Total" templates (different IDs per subject) into one column
                $uniqueAssessments = $termGrades->map(fn($m) => $m->assessmentTemplate)
                                                 ->filter()
                                                 ->unique('name')
                                                 ->sortBy('id')
                                                 ->values();
                $groupedBySubject = $termGrades->groupBy('subject.name');
            @endphp

            <div class="glass-panel border-white bg-white/60 dark:bg-slate-900/60 p-8 rounded-[2.5rem] shadow-sm flex flex-col gap-6" x-data="{ expanded: {{ $loop->first ? 'true' : 'false' }} }">
                <div class="flex items-center justify-between cursor-pointer select-none pb-4 transition-all duration-300 group"
                     :class="expanded ? 'border-b border-slate-100 dark:border-slate-800' : ''"
                     @click="expanded = !expanded">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center flex-shrink-0 shadow-sm border border-indigo-100/30 dark:border-indigo-950/20">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-lg font-black text-slate-900 dark:text-slate-100 uppercase tracking-tight font-heading">{{ $termName }}</h3>
                                @if(isset($activeTerm) && $activeTerm && $activeTerm->name === $termName)
                                    <span class="px-2 py-0.5 bg-emerald-500/10 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 dark:border-emerald-500/30 rounded-md text-[8px] font-black uppercase tracking-widest">Active</span>
                                @endif
                            </div>
                            <p class="text-[9px] text-slate-400 dark:text-slate-500 font-bold uppercase tracking-widest mt-0.5">Unified Grade Matrix</p>
                        </div>
                    </div>
                    @php
                        $termRecord = isset($termRecords) ? ($termRecords[$termName] ?? null) : null;
                        $termHasEnded = $termRecord && $termRecord->term && $termRecord->term->end_date && now()->startOfDay()->greaterThanOrEqualTo($termRecord->term->end_date->startOfDay());
                    @endphp
                    <div class="flex items-center gap-3">
                        @if($termHasEnded && $termRecord && $termRecord->average_score !== null)
                            <div class="bg-indigo-50 dark:bg-indigo-950/40 border border-indigo-100 dark:border-indigo-900/30 rounded-2xl px-4 py-2 text-center">
                                <span class="block text-[8px] font-black text-indigo-400 dark:text-indigo-500 uppercase tracking-widest leading-none mb-1">Average</span>
                                <span class="text-base font-black text-indigo-600 dark:text-indigo-300">{{ number_format($termRecord->average_score, 1) }}%</span>
                            </div>
                        @endif
                        @if($termHasEnded && $termRecord && $termRecord->rank !== null)
                            <div class="bg-amber-50 dark:bg-amber-950/40 border border-amber-100 dark:border-amber-900/30 rounded-2xl px-4 py-2 text-center">
                                <span class="block text-[8px] font-black text-amber-400 dark:text-amber-500 uppercase tracking-widest leading-none mb-1">Rank</span>
                                <span class="text-base font-black text-amber-600 dark:text-amber-300">{{ $termRecord->rank }}<span class="text-[10px] font-bold opacity-60">/ {{ $termRecord->rank_out_of }}</span></span>
                            </div>
                        @endif
                        
                        <!-- Collapsible Chevron Icon -->
                        <div class="w-10 h-10 rounded-xl bg-slate-100/50 dark:bg-slate-800/50 flex items-center justify-center text-slate-500 dark:text-slate-400 group-hover:bg-indigo-50 dark:group-hover:bg-indigo-950/50 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-all duration-300 shadow-sm border border-slate-200/50 dark:border-slate-700/50">
                            <svg class="w-5 h-5 transition-transform duration-300" :class="expanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div x-show="expanded" x-collapse x-cloak>
                    <div class="overflow-x-auto custom-scrollbar">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-slate-100 dark:border-slate-800">
                                    <th class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest pb-3">Subject</th>
                                    @foreach($uniqueAssessments as $assessment)
                                        <th class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest pb-3 text-center">
                                            {{ $assessment->name }}
                                            <span class="block text-[8px] font-bold text-slate-450 dark:text-slate-500 mt-0.5">(Max {{ number_format($assessment->max_score, 0) }} pts)</span>
                                        </th>
                                    @endforeach
                                    <th class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest pb-3 text-center">Total</th>
                                    <th class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest pb-3 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50 dark:divide-slate-800/40">
                                @foreach($groupedBySubject as $subjectName => $marks)
                                    @php
                                        $subjectTotal = 0;
                                        $subjectMax = 0;
                                        $hasComponents = $marks->contains(function($m) {
                                            return $m->assessmentTemplate && $m->assessmentTemplate->name !== 'Term Total';
                                        });
                                    @endphp
                                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-950/30 transition-colors">
                                        <td class="py-4 pr-4">
                                            <span class="text-sm font-extrabold text-slate-800 dark:text-slate-200 leading-snug">
                                                {{ $subjectName }}
                                            </span>
                                        </td>
                                        @foreach($uniqueAssessments as $assessment)
                                            @php
                                                $isRelevant = $hasComponents 
                                                    ? ($assessment->name !== 'Term Total')
                                                    : ($assessment->name === 'Term Total');
                                                
                                                $score = null;
                                                if ($isRelevant) {
                                                    // Match by template name (not ID) so "Term Total" marks from different template IDs
                                                    // all resolve to the single collapsed "Term Total" column
                                                    $mark = $marks->first(function($m) use ($assessment) {
                                                        return $m->assessmentTemplate && $m->assessmentTemplate->name === $assessment->name;
                                                    });
                                                    $score = $mark ? $mark->score : null;
                                                    if ($score !== null) {
                                                        $subjectTotal += $score;
                                                    }
                                                    $subjectMax += $assessment->max_score;
                                                }
                                            @endphp
                                            <td class="py-4 text-center">
                                                @if($isRelevant)
                                                    @if($score !== null)
                                                        <span class="text-sm font-extrabold text-slate-800 dark:text-slate-100">
                                                            {{ number_format($score, 2) }}
                                                        </span>
                                                    @else
                                                        <span class="text-sm text-slate-350 dark:text-slate-700 font-bold">-</span>
                                                    @endif
                                                @else
                                                    <span class="text-sm text-slate-300 dark:text-slate-800/50 font-bold opacity-30">—</span>
                                                @endif
                                            </td>
                                        @endforeach
                                        
                                        <!-- Total Score cell -->
                                        @php
                                            $percentage = $subjectMax > 0 ? ($subjectTotal / $subjectMax) * 100 : 0;
                                            $scoreColor = match(true) {
                                                $percentage >= 75 => 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900/30',
                                                $percentage >= 50 => 'bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 border border-amber-100 dark:border-amber-900/30',
                                                default => 'bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 border border-rose-100 dark:border-rose-900/30'
                                            };
                                        @endphp
                                        <td class="py-4 text-center">
                                            <span class="px-2.5 py-1 rounded-lg text-xs font-black {{ $scoreColor }}">
                                                {{ number_format($subjectTotal, 2) }} <span class="text-[10px] font-bold opacity-60">/ {{ number_format($subjectMax, 0) }}</span>
                                            </span>
                                        </td>
                                        
                                        <!-- Status cell -->
                                        <td class="py-4 text-center">
                                            @php
                                                $status = match(true) {
                                                    $percentage >= 75 => ['Excellent', 'text-emerald-750 bg-emerald-50 dark:text-emerald-300 dark:bg-emerald-950/60 border border-emerald-100/50 dark:border-emerald-900/30'],
                                                    $percentage >= 50 => ['Passing', 'text-amber-750 bg-amber-50 dark:text-amber-300 dark:bg-amber-950/60 border border-amber-100/50 dark:border-amber-900/30'],
                                                    default => ['Failing', 'text-rose-750 bg-rose-50 dark:text-rose-300 dark:bg-rose-950/60 border border-rose-100/50 dark:border-rose-900/30']
                                                };
                                            @endphp
                                            <span class="px-2.5 py-1 rounded-lg text-[9px] font-black uppercase tracking-wider {{ $status[1] }}">
                                                {{ $status[0] }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @empty
            <div class="glass-panel border-white bg-white/60 dark:bg-slate-900/60 p-12 text-center text-slate-500 dark:text-slate-400">
                <p class="text-base font-bold">No grade records found for this academic year.</p>
            </div>
        @endforelse

    </div>
</x-student-layout>
