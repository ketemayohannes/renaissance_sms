<x-admin-layout>
    <x-slot name="header">Promotion Preview: {{ $section->gradeLevel->name }} - {{ $section->name }}</x-slot>

    <div class="space-y-8">
        <!-- Header & Breadcrumbs -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <x-breadcrumb :items="[
                    ['label' => 'Promotions', 'url' => route('admin.promotions.index')],
                    ['label' => 'Preview', 'url' => '#']
                ]" />
                <h1 class="text-4xl font-black text-slate-900 tracking-tight mt-2">Promotion Preview</h1>
                <p class="text-sm text-slate-500 mt-1 font-medium">Review and finalize the automated promotion recommendations for Section <strong class="text-indigo-600">{{ $section->gradeLevel->name }} - {{ $section->name }}</strong>.</p>
            </div>
            <div>
                <a href="{{ route('admin.promotions.process') }}" class="px-6 py-3 bg-white border border-slate-200 text-slate-600 font-black text-[10px] uppercase tracking-widest rounded-xl hover:bg-slate-50 transition-all shadow-sm flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Back to Selection
                </a>
            </div>
        </div>

        <!-- Promotion Config Banner -->
        <div class="glass-panel p-8">
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6 mb-8 bg-slate-50/50 border border-slate-100 p-6 rounded-3xl">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6 flex-1 w-full">
                    <div>
                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-wider block mb-1">Source Grade</span>
                        <span class="text-base font-bold text-slate-800 block">{{ $section->gradeLevel->name }}</span>
                    </div>
                    <div>
                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-wider block mb-1">Target Grade</span>
                        <span class="text-base font-bold text-slate-800 block">{{ $nextGradeLevel ? trim(preg_replace('/\s*\(.*?\)/', '', $nextGradeLevel->name)) : 'N/A (Graduates)' }}</span>
                    </div>
                    <div>
                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-wider block mb-1">Active Rule</span>
                        <span class="text-base font-bold text-slate-800 block">{{ $promotionRule ? 'Min ' . $promotionRule->min_average . '%' : 'Default (50%)' }}</span>
                    </div>
                    <div>
                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-wider block mb-1">Students Found</span>
                        <span class="text-base font-bold text-slate-800 block">{{ count($previewData) }} students</span>
                    </div>
                </div>
                <a href="{{ route('admin.academic-reports.show') }}?academic_year_id={{$academicYear->id}}&term_id=yearly&section_id={{$section->id}}&report_type=result_analysis" target="_blank" class="px-5 py-3 bg-white border border-slate-200 text-indigo-600 hover:text-indigo-700 font-black text-[10px] uppercase tracking-widest rounded-xl hover:bg-slate-50 transition-all shadow-sm flex items-center gap-2 self-stretch lg:self-auto justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2m0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    Yearly Analysis
                </a>
            </div>

            <form action="{{ route('admin.promotions.execute') }}" method="POST" class="space-y-8">
                @csrf
                <input type="hidden" name="section_id" value="{{ $section->id }}">
                <input type="hidden" name="next_academic_year_id" value="{{ $nextAcademicYear->id }}">

                <div class="overflow-x-auto rounded-2xl border border-slate-100">
                    <table class="premium-table">
                        <thead>
                            <tr>
                                <th class="premium-table-header">Student</th>
                                <th class="premium-table-header text-center">Yearly Average</th>
                                <th class="premium-table-header text-center">Recommendation Status</th>
                                <th class="premium-table-header text-center">Override Decision</th>
                                <th class="premium-table-header">Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($previewData as $data)
                                @php
                                    $rowClass = '';
                                    if ($data['recommended'] === 'retained') {
                                        $rowClass = 'bg-rose-50/10 hover:bg-rose-50/20';
                                    } elseif ($data['recommended'] === 're_exam') {
                                        $rowClass = 'bg-amber-50/10 hover:bg-amber-50/20';
                                    } elseif ($data['recommended'] === 'graduated') {
                                        $rowClass = 'bg-emerald-50/10 hover:bg-emerald-50/20';
                                    } else {
                                        $rowClass = 'hover:bg-slate-50/50';
                                    }
                                @endphp
                                <tr class="premium-table-row transition-colors {{ $rowClass }}">
                                    <td class="premium-table-cell">
                                        <div class="font-bold text-slate-800">{{ $data['student']->full_name }}</div>
                                        @if($data['failedSubjects'] > 0)
                                            <span class="inline-flex items-center gap-1.5 text-[9px] font-black text-rose-500 uppercase tracking-wide mt-1">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                                {{ $data['failedSubjects'] }} {{ Str::plural('Fail', $data['failedSubjects']) }}
                                                ({{ $data['failedMajorCount'] }} Major, {{ $data['failedNonMajorCount'] }} Non-major)
                                                - {{ implode(', ', $data['failedSubjectNames']) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="premium-table-cell text-center font-black text-base {{ $data['passesAverage'] ? 'text-emerald-600' : 'text-rose-600' }}">
                                        {{ \App\Helpers\NumberFormatter::format($data['average']) }}%
                                    </td>
                                    <td class="premium-table-cell text-center">
                                        @if($data['recommended'] === 'promoted')
                                            <span class="badge badge-success uppercase tracking-wider text-[9px] font-black px-3 py-1.5">Promoted</span>
                                        @elseif($data['recommended'] === 'graduated')
                                            <span class="badge badge-success uppercase tracking-wider text-[9px] font-black px-3 py-1.5 bg-emerald-100 text-emerald-800">Graduate</span>
                                        @elseif($data['recommended'] === 're_exam')
                                            <span class="badge badge-warning uppercase tracking-wider text-[9px] font-black px-3 py-1.5">Re-exam</span>
                                        @else
                                            <span class="badge badge-danger uppercase tracking-wider text-[9px] font-black px-3 py-1.5">Retained</span>
                                        @endif
                                    </td>
                                    <td class="premium-table-cell text-center">
                                        <select name="decisions[{{ $data['student']->id }}]" class="premium-select py-1.5 text-xs">
                                            <option value="promoted" {{ $data['recommended'] === 'promoted' ? 'selected' : '' }}>Promote</option>
                                            <option value="retained" {{ $data['recommended'] === 'retained' ? 'selected' : '' }}>Retain</option>
                                            <option value="re_exam" {{ $data['recommended'] === 're_exam' ? 'selected' : '' }}>Re-exam</option>
                                            <option value="graduated" {{ $data['recommended'] === 'graduated' ? 'selected' : '' }}>Graduate</option>
                                            <option value="conditionally_promoted" {{ $data['recommended'] === 'conditionally_promoted' ? 'selected' : '' }}>Conditional</option>
                                        </select>
                                    </td>
                                    <td class="premium-table-cell">
                                        <input type="text" name="remarks[{{ $data['student']->id }}]" placeholder="Optional remarks" class="premium-input py-1.5 text-xs w-full">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="flex justify-end items-center gap-4 mt-8">
                    <a href="{{ route('admin.promotions.process') }}" class="px-6 py-3 bg-slate-100 text-slate-600 font-black text-[10px] uppercase tracking-widest rounded-xl hover:bg-slate-200 transition-all">
                        Cancel
                    </a>
                    <button type="submit" class="vibrant-btn-emerald">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Confirm & Execute Promotions
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
