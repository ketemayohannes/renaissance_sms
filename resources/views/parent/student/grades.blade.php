<x-parent-layout header="{{ $student->full_name }}'s Academic Report">
    <div class="space-y-6">
        <!-- Academic Report Filter Card -->
        <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl p-6 shadow-sm">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="space-y-1">
                    <h3 class="font-bold text-lg text-slate-800 dark:text-slate-100 font-heading">Official Report Card</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Select a term to view the student's grades and assessment breakdown.</p>
                </div>
                <form action="{{ route('parent.student.grades', $student) }}" method="GET" class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
                    <select name="period" onchange="this.form.submit()" class="px-4 py-2.5 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-750 dark:text-slate-200 rounded-xl font-bold text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 w-full sm:w-64 transition-all shadow-sm">
                        <option value="all" {{ $selectedPeriod == 'all' ? 'selected' : '' }}>All Records</option>
                        <option disabled class="text-slate-400">── Quarters ──</option>
                        @foreach($quarters as $quarter)
                            <option value="term_{{ $quarter->id }}" {{ $selectedPeriod == 'term_'.$quarter->id ? 'selected' : '' }}>{{ $quarter->name }}</option>
                        @endforeach
                        <option disabled class="text-slate-400">── Semesters ──</option>
                        @foreach($semesters as $semester)
                            <option value="semester_{{ $semester->id }}" {{ $selectedPeriod == 'semester_'.$semester->id ? 'selected' : '' }}>{{ $semester->name }}</option>
                        @endforeach
                        <option disabled class="text-slate-400">── Yearly ──</option>
                        <option value="yearly" {{ $selectedPeriod == 'yearly' ? 'selected' : '' }}>Yearly Report</option>
                    </select>
                </form>
            </div>
        </div>

        <!-- Period Action Row -->
        <div class="flex justify-between items-center px-2">
            <h2 class="text-lg font-bold text-slate-800 dark:text-slate-100 font-heading uppercase tracking-wide">{{ $periodName }}</h2>
            @if($selectedPeriod !== 'all')
                <a href="{{ route('parent.student.grades.download', [$student, 'period' => $selectedPeriod]) }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-semibold text-xs transition-colors flex items-center justify-center gap-2 shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                    </svg>
                    Download PDF
                </a>
            @endif
        </div>

        <!-- Gradebook Details -->
        <div class="space-y-6">
            @if(empty($groupedMarks) || count($groupedMarks) === 0)
                <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl p-12 text-center shadow-sm">
                    <svg class="w-16 h-16 text-slate-300 dark:text-slate-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h3 class="text-lg font-semibold text-slate-800 dark:text-slate-200">No grades available yet</h3>
                    <p class="text-slate-500 dark:text-slate-400 mt-1">Assessment scores will appear here once they are published by the teachers.</p>
                </div>
            @else
                @foreach($groupedMarks as $termName => $marks)
                    <div x-data="{ open: true }" class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
                        <!-- Card Header toggles collapse -->
                        <button @click="open = !open" class="w-full flex items-center justify-between px-6 py-4 bg-slate-50/50 dark:bg-slate-800/40 border-b border-slate-100 dark:border-slate-800 text-left">
                            <span class="font-bold text-slate-800 dark:text-slate-100 font-heading">{{ $termName }} Scores</span>
                            
                            @php
                                $termRecord = isset($termRecords) ? ($termRecords[$termName] ?? null) : null;
                            @endphp
                            
                            <div class="flex items-center gap-3">
                                @if($termRecord && $termRecord->average_score !== null)
                                    <div class="bg-indigo-50 dark:bg-indigo-950/40 border border-indigo-100/50 dark:border-indigo-900/30 rounded-xl px-3 py-1.5 text-center flex items-center gap-1.5">
                                        <span class="text-[9px] font-black text-indigo-400 dark:text-indigo-500 uppercase tracking-wider hidden sm:inline">Average:</span>
                                        <span class="text-xs font-black text-indigo-600 dark:text-indigo-300">{{ number_format($termRecord->average_score, 1) }}%</span>
                                    </div>
                                @endif
                                @if($termRecord && $termRecord->rank !== null)
                                    <div class="bg-amber-50 dark:bg-amber-950/40 border border-amber-100/50 dark:border-amber-900/30 rounded-xl px-3 py-1.5 text-center flex items-center gap-1.5">
                                        <span class="text-[9px] font-black text-amber-400 dark:text-amber-500 uppercase tracking-wider hidden sm:inline">Rank:</span>
                                        <span class="text-xs font-black text-amber-600 dark:text-amber-300">{{ $termRecord->rank }}<span class="text-[10px] font-bold opacity-60">/{{ $termRecord->rank_out_of }}</span></span>
                                    </div>
                                @endif
                                
                                <div class="w-8 h-8 rounded-lg bg-slate-100/80 dark:bg-slate-800/80 flex items-center justify-center text-slate-500 dark:text-slate-400 shadow-sm border border-slate-200/50 dark:border-slate-700/50">
                                    <svg class="w-4 h-4 transition-transform duration-300" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
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
                        
                        <!-- Table Content -->
                        <div x-show="open" x-transition class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="bg-slate-50/30 dark:bg-slate-900/30 text-slate-450 dark:text-slate-400 uppercase tracking-wider text-[10px] font-bold border-b border-slate-150 dark:border-slate-800">
                                        <th class="text-left px-6 py-3">Subject</th>
                                        @foreach($uniqueAssessments as $assessment)
                                            <th class="text-center px-4 py-3">
                                                {{ $assessment->name }}
                                                <span class="block text-[8px] font-bold text-slate-400 dark:text-slate-500 mt-0.5">(Max {{ number_format($assessment->max_score, 0) }} pts)</span>
                                            </th>
                                        @endforeach
                                        <th class="text-center px-4 py-3">Total</th>
                                        <th class="text-center px-4 py-3">Status</th>
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
                                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                                            <td class="px-6 py-4 font-semibold text-slate-800 dark:text-slate-200">
                                                {{ $subjectName }}
                                            </td>
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
                                                            <span class="font-bold text-slate-800 dark:text-slate-200">
                                                                {{ number_format($score, 1) }}
                                                            </span>
                                                        @else
                                                            <span class="text-slate-400 dark:text-slate-600">-</span>
                                                        @endif
                                                    @else
                                                        <span class="text-slate-300 dark:text-slate-800/50 font-bold opacity-30">—</span>
                                                    @endif
                                                </td>
                                            @endforeach
                                            
                                            <!-- Total Score cell -->
                                            @php
                                                $percentage = $subjectMax > 0 ? ($subjectTotal / $subjectMax) * 100 : 0;
                                                $scoreColor = match(true) {
                                                    $percentage >= 75 => 'text-emerald-600',
                                                    $percentage >= 50 => 'text-indigo-600',
                                                    default => 'text-rose-600'
                                                };
                                            @endphp
                                            <td class="px-4 py-4 text-center font-bold">
                                                <span class="{{ $scoreColor }}">{{ number_format($subjectTotal, 1) }}</span>
                                                <span class="text-slate-400 text-xs">/ {{ number_format($subjectMax, 0) }}</span>
                                            </td>
                                            
                                            <!-- Status cell -->
                                            <td class="px-4 py-4 text-center">
                                                @php
                                                    $status = match(true) {
                                                        $percentage >= 75 => ['Excellent', 'text-emerald-700 bg-emerald-50 dark:text-emerald-300 dark:bg-emerald-950/60 border border-emerald-100/50 dark:border-emerald-900/30'],
                                                        $percentage >= 50 => ['Passing', 'text-indigo-700 bg-indigo-50 dark:text-indigo-300 dark:bg-indigo-950/60 border border-indigo-100/50 dark:border-indigo-900/30'],
                                                        default => ['Failing', 'text-rose-700 bg-rose-50 dark:text-rose-300 dark:bg-rose-950/60 border border-rose-100/50 dark:border-rose-900/30']
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
                @endforeach
            @endif
        </div>
    </div>
</x-parent-layout>