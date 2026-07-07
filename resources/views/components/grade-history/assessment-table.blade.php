{{--
    Shared "Assessment" table for one quarter: subjects × individual assessment components
    (Quiz, Mid Exam, Final Exam, etc.) — the raw entries a teacher recorded. For a subject
    that only has a Term Total (Master Sheet entry, no components), falls back to showing
    that single value instead of an empty row.

    Only meaningful for quarters — semesters and the yearly roll-up are computed
    subject totals with no component breakdown, so they belong on the Report tab only.

    Props:
      $marks — Collection<StudentMark> for a single quarter (already resolved
               components-first by StudentAcademicHistoryService).
--}}
@php
    $uniqueAssessments = $marks->map(fn($m) => $m->assessmentTemplate)
        ->filter()
        ->unique('name')
        ->sortBy('id')
        ->values();
    $groupedBySubject = $marks->groupBy('subject.name');
@endphp
<div class="overflow-x-auto no-scrollbar pb-4">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-slate-50/20 dark:bg-slate-900/20 text-slate-400 dark:text-slate-500 uppercase tracking-widest text-[9px] font-black border-b border-slate-100 dark:border-slate-800 select-none">
                <th class="text-left px-6 py-4 sticky left-0 bg-slate-50 dark:bg-slate-900 z-10 border-r border-slate-100 dark:border-slate-800 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.1)]">Subject</th>
                @foreach($uniqueAssessments as $assessment)
                    <th class="text-center px-4 py-4 min-w-[110px]">
                        <span class="block text-slate-700 dark:text-slate-300">{{ $assessment->name }}</span>
                        <span class="block text-[8px] font-bold text-slate-400 dark:text-slate-500 mt-0.5">Max {{ number_format($assessment->max_score, 0) }} pts</span>
                    </th>
                @endforeach
                <th class="text-center px-6 py-4 min-w-[140px]">Total Score</th>
                <th class="text-center px-6 py-4 min-w-[120px]">Academic Standing</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
            @foreach($groupedBySubject as $subjectName => $subjectMarks)
                @php
                    $subjectTotal = 0;
                    $subjectMax = 0;
                    $hasComponents = $subjectMarks->contains(function($m) {
                        return $m->assessmentTemplate && $m->assessmentTemplate->name !== 'Term Total';
                    });
                @endphp
                <tr class="hover:bg-slate-50/40 dark:hover:bg-slate-800/20 transition-all duration-200">
                    <td class="px-6 py-4 font-bold text-slate-800 dark:text-slate-200 sticky left-0 bg-white dark:bg-slate-900 z-10 border-r border-slate-100 dark:border-slate-800 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.1)]">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center flex-shrink-0 border border-indigo-100/50 dark:border-indigo-900/30">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                </svg>
                            </div>
                            <span class="font-extrabold">{{ $subjectName }}</span>
                        </div>
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
                                    <span class="font-extrabold text-slate-800 dark:text-slate-200 font-mono text-sm">
                                        {{ \App\Helpers\NumberFormatter::format($score) }}
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
                        $percentage = $subjectMax > 0 ? ($subjectTotal / $subjectMax) * 100 : 0;
                        $scoreColor = match(true) {
                            $percentage >= 75 => 'text-emerald-600 dark:text-emerald-400',
                            $percentage >= 50 => 'text-indigo-600 dark:text-indigo-400',
                            default => 'text-rose-600 dark:text-rose-400',
                        };
                        $barColor = match(true) {
                            $percentage >= 75 => 'from-emerald-500 to-teal-400',
                            $percentage >= 50 => 'from-indigo-500 to-violet-400',
                            default => 'from-rose-500 to-red-400',
                        };
                    @endphp
                    <td class="px-6 py-4 text-center">
                        <div class="flex flex-col items-center justify-center gap-1.5">
                            <div class="flex items-baseline gap-0.5">
                                <span class="{{ $scoreColor }} text-sm font-black font-mono">{{ \App\Helpers\NumberFormatter::format($subjectTotal) }}</span>
                                <span class="text-slate-400 dark:text-slate-500 text-[10px] font-bold">/ {{ number_format($subjectMax, 0) }}</span>
                            </div>
                            <div class="w-24 h-1 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden border border-slate-200/10 dark:border-slate-700/10 shadow-inner">
                                <div class="h-full bg-gradient-to-r {{ $barColor }} rounded-full" style="width: {{ min(100, max(0, $percentage)) }}%"></div>
                            </div>
                        </div>
                    </td>

                    <td class="px-6 py-4 text-center">
                        @php
                            $status = match(true) {
                                $percentage >= 75 => ['Excellent', 'text-emerald-700 bg-emerald-50 dark:text-emerald-300 dark:bg-emerald-950/60 border border-emerald-100/50 dark:border-emerald-900/30'],
                                $percentage >= 50 => ['Passing', 'text-indigo-700 bg-indigo-50 dark:text-indigo-300 dark:bg-indigo-950/60 border border-indigo-100/50 dark:border-indigo-900/30'],
                                default => ['Failing', 'text-rose-700 bg-rose-50 dark:text-rose-300 dark:bg-rose-950/60 border border-rose-100/50 dark:border-rose-900/30'],
                            };
                        @endphp
                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-xl text-[9px] font-black uppercase tracking-wider {{ $status[1] }} select-none">
                            {{ $status[0] }}
                        </span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
