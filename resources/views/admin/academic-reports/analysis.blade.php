<x-admin-layout>
    <x-slot name="header">Performance Intelligence: {{ $section->name }}</x-slot>

    <div class="space-y-8 pb-20">
        <!-- Header & Control Bar -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 no-print">
            <div>
                <x-breadcrumb :items="[
                    ['label' => 'Academic Reports', 'url' => route('admin.academic-reports.index')],
                    ['label' => 'Result Analysis', 'url' => '#']
                ]" />
                <h1 class="text-4xl font-black text-slate-900 tracking-tight mt-2 flex items-center gap-3">
                    <span class="w-1.5 h-8 bg-indigo-600 rounded-full shadow-[0_0_15px_rgba(79,70,229,0.4)]"></span>
                    Section Analysis
                </h1>
                <p class="text-slate-500 font-semibold mt-1 uppercase text-[10px] tracking-[0.3em] italic">Granular performance audit for {{ $section->gradeLevel->name }} — {{ $section->name }}</p>
            </div>
            
            <div class="flex items-center gap-4">
                <form action="{{ route('admin.academic-reports.recalculate') }}" method="POST" class="inline">
                    @csrf
                    <input type="hidden" name="academic_year_id" value="{{ $academicYear->id }}">
                    <input type="hidden" name="term_id" value="{{ $term->id }}">
                    <input type="hidden" name="section_id" value="{{ $section->id }}">
                    <button type="submit" class="px-6 py-4 bg-white/80 backdrop-blur-xl border border-slate-200 text-slate-600 font-black text-[10px] uppercase tracking-widest rounded-2xl hover:bg-indigo-50 hover:text-indigo-600 shadow-xl shadow-slate-200/50 transition-all flex items-center gap-3 active:scale-95 group">
                        <div class="w-8 h-8 rounded-xl bg-indigo-50 group-hover:bg-indigo-100 flex items-center justify-center transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                        </div>
                        Sync Stats
                    </button>
                </form>

                <button onclick="window.print()" class="px-8 py-4 bg-slate-900 text-white font-black text-[10px] uppercase tracking-widest rounded-2xl hover:bg-indigo-600 shadow-xl shadow-slate-200 transition-all flex items-center gap-3 active:scale-95">
                    <div class="w-8 h-8 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    </div>
                    Generate Hardcopy
                </button>
            </div>
        </div>

        <!-- Metric Pulse Matrix -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-indigo-900 rounded-[2.2rem] p-8 text-white shadow-2xl relative overflow-hidden group">
                <div class="absolute -right-10 -top-10 w-32 h-32 bg-white/5 rounded-full blur-2xl group-hover:scale-150 transition-transform duration-700"></div>
                <div class="relative z-10">
                    <span class="block text-[10px] font-black text-indigo-300 uppercase tracking-widest mb-4">Class Average</span>
                    <div class="flex items-baseline gap-2">
                        <span class="text-4xl font-black italic tracking-tighter">{{ number_format($classStats->class_average, 1) }}</span>
                        <span class="text-lg font-black text-indigo-400 opacity-60">%</span>
                    </div>
                    <p class="text-[9px] font-bold text-white/40 uppercase mt-4 tracking-tighter">Institutional benchmark</p>
                </div>
            </div>

            <div class="bg-white/80 backdrop-blur-xl rounded-[2.2rem] p-8 border border-white shadow-xl shadow-slate-200/50 relative overflow-hidden group">
                <span class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Efficiency Rate</span>
                <div class="flex items-baseline gap-2">
                    <span class="text-4xl font-black text-slate-900 italic tracking-tighter">
                        {{ $classStats->total_students > 0 ? number_format(($classStats->total_passed / $classStats->total_students) * 100, 1) : 0 }}%
                    </span>
                    <span class="text-xs font-black text-emerald-500 uppercase">Pass</span>
                </div>
                <p class="text-[9px] font-bold text-slate-400 uppercase mt-4 tracking-tighter">
                    {{ $classStats->total_passed }} of {{ $classStats->total_students }} subjects cleared
                </p>
            </div>

            <div class="bg-white/80 backdrop-blur-xl rounded-[2.2rem] p-8 border border-white shadow-xl shadow-slate-200/50 relative overflow-hidden group">
                <span class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Peak Performance</span>
                <div class="flex items-baseline gap-2">
                    <span class="text-4xl font-black text-slate-900 italic tracking-tighter">{{ number_format($classStats->highest_avg, 1) }}%</span>
                    <span class="text-xs font-black text-amber-500 uppercase">Max</span>
                </div>
                <p class="text-[9px] font-bold text-slate-400 uppercase mt-4 tracking-tighter">Highest section average</p>
            </div>

            <div class="bg-indigo-50/50 backdrop-blur-xl rounded-[2.2rem] p-8 border border-indigo-100 shadow-xl shadow-indigo-200/20 relative overflow-hidden group">
                <span class="block text-[10px] font-black text-indigo-400 uppercase tracking-widest mb-4">Domain Count</span>
                <div class="flex items-baseline gap-2">
                    <span class="text-4xl font-black text-indigo-900 italic tracking-tighter">{{ count($subjects) }}</span>
                    <span class="text-xs font-black text-indigo-400 uppercase italic">Subjects</span>
                </div>
                <p class="text-[10px] font-bold text-indigo-400 uppercase mt-4 tracking-tighter">Active academic domains</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Domain Performance Intelligence -->
            <div class="lg:col-span-2">
                <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-2xl shadow-slate-200/50 overflow-hidden">
                    <div class="p-8 border-b border-slate-50 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-1.5 h-6 bg-slate-900 rounded-full"></div>
                            <h3 class="text-lg font-black text-slate-900 uppercase tracking-tight">Domain Analysis Matrix</h3>
                        </div>
                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] italic">Subject-by-subject efficiency audit</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-separate border-spacing-0">
                            <thead>
                                <tr class="bg-slate-50/50">
                                    <th class="px-8 py-4 border-b border-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Academic Domain</th>
                                    <th class="px-6 py-4 border-b border-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-center">Appeared</th>
                                    <th class="px-6 py-4 border-b border-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-center">Efficiency</th>
                                    <th class="px-6 py-4 border-b border-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-center">Peak</th>
                                    <th class="px-10 py-4 border-b border-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-right">Avg Score</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @foreach($subjects as $subject)
                                    @if(isset($subjectStats[$subject->id]))
                                        @php $stats = $subjectStats[$subject->id]; @endphp
                                        <tr class="group hover:bg-slate-50/50 transition-colors">
                                            <td class="px-8 py-6">
                                                <span class="block text-sm font-black text-slate-900 group-hover:text-indigo-600 transition-colors">{{ $subject->name }}</span>
                                                <span class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mt-0.5">Core Discipline</span>
                                            </td>
                                            <td class="px-6 py-6 text-center">
                                                <span class="text-sm font-bold text-slate-600">{{ $stats->appeared }}</span>
                                            </td>
                                            <td class="px-6 py-6 text-center">
                                                @php
                                                    $efficiencyColor = $stats->pass_rate >= 75 ? 'text-emerald-500 bg-emerald-50' : ($stats->pass_rate >= 50 ? 'text-amber-500 bg-amber-50' : 'text-rose-500 bg-rose-50');
                                                @endphp
                                                <span class="px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest {{ $efficiencyColor }} border border-current border-opacity-10">
                                                    {{ number_format($stats->pass_rate, 1) }}%
                                                </span>
                                            </td>
                                            <td class="px-6 py-6 text-center">
                                                <span class="text-sm font-black text-indigo-600 italic">{{ number_format($stats->highest, 1) }}</span>
                                            </td>
                                            <td class="px-10 py-6 text-right">
                                                <span class="text-lg font-black text-slate-900 italic tracking-tighter">{{ number_format($stats->average, 1) }}</span>
                                                <span class="text-[10px] font-black text-slate-300 italic">%</span>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Elite Performers & Demographics -->
            <div class="space-y-8">
                <!-- Top Vanguard -->
                <div class="bg-indigo-900 rounded-[2.5rem] p-8 text-white shadow-2xl relative overflow-hidden group">
                    <div class="absolute -right-20 -top-20 w-60 h-60 bg-white/5 rounded-full blur-3xl group-hover:scale-125 transition-transform duration-1000"></div>
                    <div class="relative z-10">
                        <div class="flex items-center justify-between mb-8">
                            <div>
                                <h3 class="text-sm font-black uppercase tracking-widest">Elite Vanguard</h3>
                                <p class="text-[9px] font-bold text-indigo-300 uppercase mt-1">Highest section merit</p>
                            </div>
                            <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center">
                                <svg class="w-5 h-5 text-amber-400 animate-bounce" fill="currentColor" viewBox="0 0 20 20"><path d="M5 4a2 2 0 012-2h6a2 2 0 012 2v14l-5-2.5L5 18V4z"></path></svg>
                            </div>
                        </div>

                        <div class="space-y-4">
                            @forelse($topPerformers as $index => $report)
                                <div class="bg-white/10 p-4 rounded-2xl border border-white/5 flex items-center justify-between hover:bg-white/20 transition-all cursor-default">
                                    <div class="flex items-center gap-4">
                                        <div class="w-8 h-8 rounded-lg bg-indigo-500/30 flex items-center justify-center text-[10px] font-black">
                                            0{{ $index + 1 }}
                                        </div>
                                        <div>
                                            <p class="text-xs font-black uppercase tracking-tight">{{ $report['student']->full_name }}</p>
                                            <p class="text-[8px] font-bold text-indigo-400 uppercase tracking-widest mt-0.5">Trace № {{ $report['student']->student_id }}</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-lg font-black italic tracking-tighter leading-none">
                                            {{ number_format($term->type === 'yearly' ? ($report['rows']['avg']['average'] ?? 0) : ($report['average'] ?? 0), 1) }}%
                                        </p>
                                        <p class="text-[8px] font-black text-indigo-400 uppercase mt-0.5">Rank: {{ $term->type === 'yearly' ? ($report['rows']['avg']['rank'] ?? '-') : ($report['rank'] ?? '-') }}</p>
                                    </div>
                                </div>
                            @empty
                                <div class="py-8 text-center text-indigo-400 text-[10px] font-black uppercase tracking-[0.2em]">Archival data unavailable</div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Gender Proficiency Variance -->
                <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-2xl shadow-slate-200/50 p-8">
                    <div class="flex items-center gap-3 mb-8">
                        <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center shadow-inner">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </div>
                        <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest">Demographic Proficiency</h3>
                    </div>

                    @php
                        $genderData = collect($reports)->groupBy(fn($r) => $r['student']->gender)->map(function($group) use ($term) {
                            return $group->avg(fn($r) => $term->type === 'yearly' ? ($r['rows']['avg']['average'] ?? 0) : ($r['average'] ?? 0));
                        });
                    @endphp

                    <div class="space-y-8">
                        @foreach(['male' => ['color' => 'indigo', 'label' => 'Male Cadets'], 'female' => ['color' => 'rose', 'label' => 'Female Cadets']] as $gender => $meta)
                            @if(isset($genderData[$gender]))
                                <div class="space-y-3">
                                    <div class="flex justify-between items-end px-1">
                                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-[0.3em]">{{ $meta['label'] }}</span>
                                        <span class="text-sm font-black text-slate-900 italic tracking-tighter">{{ number_format($genderData[$gender], 1) }}%</span>
                                    </div>
                                    <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                                        <div class="h-full bg-{{ $meta['color'] }}-500 rounded-full transition-all duration-1000 shadow-[0_0_10px_rgba(79,70,229,0.3)]" 
                                             style="width: {{ $genderData[$gender] }}%"></div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>

                    <div class="mt-8 pt-6 border-t border-slate-50">
                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest italic text-center">Cross-gender proficiency variance audit</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Print-only structural rules -->
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; font-family: serif; }
            .pb-20 { padding-bottom: 0 !important; }
            .shadow-2xl, .shadow-xl { box-shadow: none !important; border: 1px solid #eee !important; }
            .rounded-[2.2rem], .rounded-[2.5rem], .rounded-[2rem] { border-radius: 0.5rem !important; }
            .bg-indigo-900 { background-color: #1e1b4b !important; color: white !important; -webkit-print-color-adjust: exact; }
            .bg-white\/80 { background-color: white !important; }
            .text-indigo-900 { color: #1e1b4b !important; }
            .text-indigo-600 { color: #4f46e5 !important; }
            .text-slate-900 { color: black !important; }
            .border-white { border: 1px solid #eee !important; }
            table { font-size: 10pt; }
            .px-8, .px-6 { padding: 8px !important; }
            .py-8, .py-6 { padding: 12px 8px !important; }
            @page { margin: 1cm; size: a4 portrait; }
        }
    </style>
</x-admin-layout>
