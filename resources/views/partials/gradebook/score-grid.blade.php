{{--
    Score-entry grid + floating save bar, shared by the admin and teacher gradebook entry
    screens. Must sit inside each screen's own <form id="gradeForm">.

    Expects: $students, $gradeComponents, $existingMarks, $termTotalTemplate, $term,
    $canEditGradebook, $totalStudents, $gradedStudents, plus:
      $discardUrl       where "Discard" goes
      $lockedTextClass  text colour of read-only (locked) marks
--}}
<!-- Grade Entry Table -->
<div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-2xl shadow-slate-200/50 overflow-hidden relative">
    <div class="overflow-x-auto custom-scrollbar">
        <table class="w-full text-left border-separate border-spacing-0">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200">
                    <th class="px-2 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider text-center w-12 pb-2">No</th>
                    <th class="px-3 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider sticky left-0 z-30 bg-slate-50 shadow-[4px_0_12px_-4px_rgba(0,0,0,0.05)] pb-2 min-w-[250px]">Student Full Name</th>
                    <th class="px-2 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider text-center w-16">Gender</th>
                    
                    @foreach($gradeComponents as $component)
                        <th class="px-4 py-3 border-b border-slate-200 min-w-[8rem] text-center group cursor-default relative hover:bg-slate-100 transition-colors">
                            <div class="flex flex-col items-center justify-center w-full h-full mx-auto px-1 py-1">
                                <span class="text-[10px] font-bold text-slate-600 uppercase tracking-wider whitespace-normal leading-3">{{ $component->name }}</span>
                                <span class="text-[9px] font-semibold text-indigo-500 tracking-tighter mt-1">Max: {{ $component->max_score }}</span>
                            </div>
                        </th>
                    @endforeach

                    @php
                        $totalCaCapacity = $gradeComponents->filter(fn($c) => $c->assessmentType?->code !== 'FINAL')->sum('max_score');
                    @endphp
                    <th class="px-4 py-3 border-b border-slate-200 text-center font-bold text-indigo-600 text-[11px] bg-indigo-50/30 uppercase tracking-wider min-w-[6rem]">
                        CA ({{ (int)$totalCaCapacity }}%)
                    </th>
                    <th class="px-4 py-3 border-b border-slate-200 text-center font-bold text-slate-700 text-[11px] uppercase tracking-wider min-w-[6rem]">
                        Total
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
                @forelse($students as $index => $student)
                    <tr class="group hover:bg-slate-50 transition-colors student-row" data-student-id="{{ $student->id }}">
                        <td class="px-2 py-2 text-xs font-medium text-slate-500 text-center">{{ $index + 1 }}</td>
                        <td class="px-3 py-2 whitespace-nowrap sticky left-0 bg-white z-20 group-hover:bg-slate-50 transition-colors shadow-[4px_0_12px_-4px_rgba(0,0,0,0.05)]">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-600 font-bold text-[10px] ring-1 ring-white shadow-sm flex-shrink-0">
                                    {{ substr($student->first_name ?? $student->full_name, 0, 1) }}{{ substr($student->last_name ?? '', 0, 1) }}
                                </div>
                                <div class="min-w-0">
                                    <div class="font-semibold text-slate-900 text-xs" title="{{ $student->full_name }}">{{ $student->full_name }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-1 py-2 text-xs font-medium text-slate-500 text-center uppercase">
                            <span class="inline-flex items-center justify-center w-5 h-5 rounded-md {{ ($student->gender ?? 'M') === 'F' ? 'bg-pink-50 text-pink-600' : 'bg-blue-50 text-blue-600' }} font-bold text-[10px]">
                                {{ substr($student->gender ?? 'M', 0, 1) }}
                            </span>
                        </td>
                        
                        @php
                            $totalScore = 0;
                            $caScore = 0;
                        @endphp
                        @foreach($gradeComponents as $component)
                            @php
                                $studentMarks = $existingMarks->get($student->id);
                                $mark = $studentMarks ? $studentMarks->first(fn($m) => (int)$m->assessment_template_id === (int)$component->id) : null;
                                $score = $mark ? $mark->score : 0;
                                $val = $mark ? $mark->score : '';
                                $isFinal = ($component->assessmentType?->code === 'FINAL');
                                
                                $totalScore += $score;
                                if (!$isFinal) {
                                    $caScore += $score;
                                }
                            @endphp
                            <td class="p-0.5 text-center relative group/cell min-w-[3rem]">
                                <input type="text" inputmode="decimal"
                                       name="marks[{{ $student->id }}][{{ $component->id }}][score]" 
                                       value="{{ $val }}" 
                                       max="{{ $component->max_score }}" 
                                       class="w-full text-center text-xs font-medium border-slate-200 rounded focus:ring-1 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all hover:bg-slate-50 disabled:bg-slate-50 {{ $lockedTextClass }} mark-input py-1 px-0"
                                       data-is-final="{{ $component->assessmentType?->code === 'FINAL' ? '1' : '0' }}"
                                       oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1'); calculateRow(this.closest('tr'));"
                                       @if(!$canEditGradebook) disabled @endif>
                            </td>
                        @endforeach

                        <td class="px-1 py-2 text-center bg-indigo-50/20">
                            <span class="inline-block px-1.5 py-0.5 rounded bg-indigo-100 text-indigo-700 text-xs font-bold student-ca min-w-[2.5rem]">{{ $caScore }}</span>
                        </td>

                        <td class="px-1 py-2 text-center">
                            @php
                                $displayTotal = $totalScore;
                                $termTotalMark = null;
                                if (isset($termTotalTemplate) && $termTotalTemplate && $studentMarks) {
                                    $termTotalMark = $studentMarks->first(fn($m) => (int)$m->assessment_template_id === (int)$termTotalTemplate->id);
                                }
                                
                                if ($displayTotal == 0 && $termTotalMark) {
                                    $displayTotal = $termTotalMark->score;
                                }
                            @endphp
                            <span class="inline-block px-1.5 py-0.5 rounded bg-slate-100 text-slate-700 text-xs font-bold student-total min-w-[2.5rem]" data-master-total="{{ $termTotalMark ? $termTotalMark->score : 0 }}">{{ number_format($displayTotal, 2) }}</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $gradeComponents->count() + 5 }}" class="px-12 py-20 text-center">
                            <div class="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center text-slate-300 mx-auto mb-4">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354l1.1 3.392a1 1 0 00.95.69h3.462a1 1 0 01.59 1.807l-2.8 2.034a1 1 0 00-.36 1.118l1.07 3.292a1 1 0 01-1.537 1.117l-2.8-2.034a1 1 0 00-1.176 0l-2.8 2.034a1 1 0 01-1.537-1.117l1.07-3.292a1 1 0 00-.36-1.118l-2.8-2.034a1 1 0 01.59-1.807h3.462a1 1 0 00.95-.69L12 4.354z"></path></svg>
                            </div>
                            <h3 class="text-sm font-bold text-slate-900">No students found</h3>
                            <p class="text-slate-500 text-xs mt-1">Please verify the section enrollment.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Floating Bottom Command Bar -->
<div class="fixed bottom-6 left-1/2 -translate-x-1/2 z-40 flex items-center gap-1 p-1.5 bg-white/90 backdrop-blur-xl border border-slate-200 shadow-2xl shadow-slate-200/50 rounded-2xl animate-in slide-in-from-bottom-12 duration-500">
    <div class="flex items-center gap-3 pl-4 pr-2 border-r border-slate-100">
        <div class="flex flex-col">
            <span class="text-[10px] uppercase tracking-wider font-bold text-slate-400 leading-none" x-text="hasUnsavedChanges ? 'Unsaved' : 'Synced'">Status</span>
            <span class="text-sm font-bold text-slate-700 leading-tight" id="ungradedCount">{{ $totalStudents - $gradedStudents }} Ungraded</span>
        </div>
    </div>

    <a href="{{ $discardUrl }}" class="px-5 py-2.5 text-slate-500 font-bold text-xs hover:text-slate-900 transition-colors uppercase tracking-wider">
        Discard
    </a>
    
    @if($canEditGradebook)
        <button type="submit" class="px-6 py-2.5 bg-indigo-600 text-white font-bold text-xs uppercase tracking-widest rounded-xl hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-100 flex items-center gap-2 group">
            Save Changes
            <svg class="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
        </button>
    @endif
</div>
