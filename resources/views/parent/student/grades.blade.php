<x-parent-layout header="{{ $student->full_name }}'s Academic Report">
    <div class="space-y-6">
        <!-- PDF Report Download Card -->
        <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl p-6 shadow-sm">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="space-y-1">
                    <h3 class="font-bold text-lg text-slate-800 dark:text-slate-100 font-heading">Official Report Card</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Select a term to view or download a printable PDF copy of the report card.</p>
                </div>
                <form action="{{ route('parent.student.grades.download', $student) }}" method="GET" target="_blank" class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
                    <select name="term_id" required class="px-4 py-2 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 w-full sm:w-64">
                        @foreach($terms as $t)
                            <option value="{{ $t->id }}">{{ $t->name }}</option>
                        @endforeach
                        <option value="yearly">Yearly Report Card</option>
                    </select>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-semibold text-sm transition-colors flex items-center justify-center gap-2 whitespace-nowrap shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        Download PDF
                    </button>
                </form>
            </div>
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
                            <svg class="w-5 h-5 text-slate-400 transition-transform duration-300" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        
                        <!-- Table Content -->
                        <div x-show="open" x-transition class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="bg-slate-50/30 dark:bg-slate-900/30 text-slate-450 dark:text-slate-400 uppercase tracking-wider text-[10px] font-bold border-b border-slate-150 dark:border-slate-800">
                                        <th class="text-left px-6 py-3">Subject</th>
                                        <th class="text-left px-6 py-3">Assessment Type</th>
                                        <th class="text-right px-6 py-3">Weight</th>
                                        <th class="text-right px-6 py-3">Score</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-850">
                                    @foreach($marks as $mark)
                                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                                            <td class="px-6 py-4 font-semibold text-slate-800 dark:text-slate-200">
                                                {{ $mark->subject->name ?? 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 text-slate-500 dark:text-slate-450">
                                                {{ $mark->assessmentTemplate->name ?? 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 text-right text-slate-500 dark:text-slate-450">
                                                {{ $mark->assessmentTemplate->weight ?? 100 }}%
                                            </td>
                                            <td class="px-6 py-4 text-right font-bold">
                                                @php
                                                    $percentage = ($mark->score / ($mark->assessmentTemplate->max_score ?? 100)) * 100;
                                                    $colorClass = $percentage >= 80 ? 'text-emerald-600' : ($percentage >= 50 ? 'text-indigo-600' : 'text-rose-600');
                                                @endphp
                                                <span class="{{ $colorClass }}">{{ number_format($mark->score, 1) }}</span>
                                                <span class="text-slate-400 text-xs">/ {{ $mark->assessmentTemplate->max_score ?? 100 }}</span>
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