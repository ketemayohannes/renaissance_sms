<x-admin-layout>
    <div class="space-y-8 pb-32">
        <!-- Modern Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center gap-6">
                <a href="{{ route('admin.section-grades.index') }}" class="w-14 h-14 bg-white border border-slate-100 rounded-2xl flex items-center justify-center text-slate-400 hover:text-indigo-600 hover:bg-slate-50 transition-all shadow-xl active:scale-90">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 19l-7-7 7-7"></path></svg>
                </a>
                <div>
                    <x-breadcrumb :items="[
                        ['label' => 'Academic Ops', 'url' => route('admin.section-grades.index')],
                        ['label' => $section->name, 'url' => '#'],
                        ['label' => 'Report Analytics', 'url' => '#']
                    ]" />
                    <h1 class="text-4xl font-black text-slate-900 tracking-tight mt-2 flex items-center gap-3">
                        <span class="w-1.5 h-8 bg-indigo-600 rounded-full shadow-[0_0_15px_rgba(79,70,229,0.4)]"></span>
                        Behavior & Protocol
                    </h1>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('admin.section-grades.bulk-print-report-cards', $section->id) }}?academic_year_id={{$academicYear->id}}&term_id={{$term->id}}" 
                   target="_blank" 
                   class="px-6 py-4 bg-slate-900 text-white font-black text-[10px] uppercase tracking-widest rounded-2xl hover:bg-slate-800 transition-all shadow-xl shadow-slate-200 flex items-center gap-3">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    Print Report
                </a>
                <a href="{{ route('admin.section-grades.bulk-export-report-cards', $section->id) }}?academic_year_id={{$academicYear->id}}&term_id={{$term_id ?? $term->id}}" 
                   class="px-6 py-4 bg-indigo-600 text-white font-black text-[10px] uppercase tracking-widest rounded-2xl hover:bg-indigo-700 transition-all shadow-xl shadow-indigo-100 flex items-center gap-3">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    Batch Synthesis ⚡
                </a>
            </div>
        </div>

        <!-- Success/Error Alerts -->
        @if (session('success'))
            <div class="bg-emerald-50 border border-emerald-100 text-emerald-800 px-6 py-4 rounded-[1.5rem] flex items-center gap-4 animate-in fade-in slide-in-from-top-4 duration-300">
                <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600 shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <span class="font-bold text-sm">{{ session('success') }}</span>
            </div>
        @endif

        <!-- Low Performance Alert -->
        @php
            $lowPerformers = $students->filter(function($student) use ($records) {
                $record = $records[$student->id] ?? null;
                return $record && $record->average_score !== null && $record->average_score < 75;
            });
        @endphp

        @if($lowPerformers->isNotEmpty())
            <div class="bg-rose-50 border border-rose-100 rounded-[2.5rem] p-8 shadow-xl shadow-rose-100/50 animate-in fade-in slide-in-from-top-4 duration-500">
                <div class="flex items-center gap-6 mb-6">
                    <div class="w-14 h-14 rounded-2xl bg-rose-500 text-white flex items-center justify-center shadow-lg shadow-rose-200">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                    <div class="flex-1">
                        <h2 class="text-xl font-black text-rose-900 tracking-tight">Academic Alert: Low Performance Threshold</h2>
                        <p class="text-sm font-bold text-rose-600/80">The following students have a total average below 75% for this {{ $term->type }}.</p>
                    </div>
                    <a href="{{ route('admin.report-cards.export-low-performance', ['academic_year_id' => $academicYear->id, 'term_id' => $term->id, 'section_id' => $section->id]) }}" 
                       class="px-6 py-3 bg-rose-600 text-white text-xs font-black uppercase tracking-widest rounded-2xl hover:bg-rose-700 transition-all shadow-lg shadow-rose-200 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        Export List
                    </a>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    @foreach($lowPerformers as $student)
                        <div class="bg-white/60 backdrop-blur-md border border-rose-100 rounded-2xl p-4 flex items-center justify-between group hover:bg-white transition-all">
                            <div class="flex flex-col">
                                <span class="text-xs font-black text-slate-800 tracking-tight group-hover:text-rose-600 transition-colors">{{ $student->full_name }}</span>
                                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">ID: {{ $student->student_id }}</span>
                            </div>
                            <div class="flex flex-col items-end">
                                <span class="text-sm font-black text-rose-600">{{ number_format($records[$student->id]->average_score, 1) }}%</span>
                                <span class="text-[9px] font-bold text-rose-300 uppercase tracking-tighter">Average</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Context Matrix -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="bg-white/80 backdrop-blur-xl border border-white rounded-[2.5rem] p-8 shadow-xl shadow-slate-200/50 flex items-center gap-6 group hover:-translate-y-1 transition-all">
                <div class="w-16 h-16 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center shadow-lg group-hover:rotate-6 transition-all">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </div>
                <div>
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block">Academic Cycle</span>
                    <span class="text-xl font-black text-slate-900 leading-none">{{ $academicYear->name }}</span>
                    <p class="text-[9px] font-bold text-slate-400 mt-1 uppercase tracking-tighter italic">Currently Active Period</p>
                </div>
            </div>

            <div class="bg-white/80 backdrop-blur-xl border border-white rounded-[2.5rem] p-8 shadow-xl shadow-slate-200/50 flex items-center gap-6 group hover:-translate-y-1 transition-all">
                <div class="w-16 h-16 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center shadow-lg group-hover:rotate-6 transition-all">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block">Term Interval</span>
                    <span class="text-xl font-black text-indigo-600 leading-none">{{ $term->name }}</span>
                    <p class="text-[9px] font-bold text-slate-400 mt-1 uppercase tracking-tighter italic">Reporting Checkpoint</p>
                </div>
            </div>

            <div class="bg-white/80 backdrop-blur-xl border border-white rounded-[2.5rem] p-8 shadow-xl shadow-slate-200/50 flex items-center gap-6 group hover:-translate-y-1 transition-all">
                <div class="w-16 h-16 rounded-2xl bg-slate-900 text-white flex items-center justify-center shadow-lg group-hover:rotate-6 transition-all">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
                <div>
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block">Entity Registry</span>
                    <span class="text-xl font-black text-slate-900 leading-none">{{ $section->gradeLevel->name }}{{ $section->name }}</span>
                    <p class="text-[9px] font-bold text-slate-400 mt-1 uppercase tracking-tighter italic">{{ $students->count() }} Managed Students</p>
                </div>
            </div>
        </div>

        <!-- Data Matrix -->
        <div class="bg-white/80 backdrop-blur-xl border border-white rounded-[3rem] shadow-2xl overflow-hidden group">
            <form action="{{ route('admin.section-grades.store-report-card-entry', $section) }}" method="POST" id="reportCardForm">
                @csrf
                <input type="hidden" name="academic_year_id" value="{{ $academicYear->id }}">
                <input type="hidden" name="term_id" value="{{ $term->id }}">

                <div class="overflow-x-auto pb-24">
                    <table class="w-full text-left border-separate border-spacing-0">
                        <thead>
                            <tr class="bg-slate-50/50">
                                <th class="px-6 py-8 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center border-b border-slate-100">No</th>
                                <th class="px-8 py-8 text-[10px] font-black text-slate-900 uppercase tracking-widest border-b border-slate-100">Student Identity</th>
                                <th class="px-4 py-8 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center border-b border-slate-100 w-32">Conduct Tier</th>
                                <th class="px-4 py-8 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center border-b border-slate-100 w-32">Temporal Void (Absence)</th>
                                <th class="px-8 py-8 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Evaluative Discourse (Comment)</th>
                                <th class="px-6 py-8 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center border-b border-slate-100">Node Link</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach($students as $index => $student)
                                @php
                                    $record = $records[$student->id] ?? null;
                                @endphp
                                <tr class="hover:bg-indigo-50/30 transition-all group/row">
                                    <td class="px-6 py-6 text-[10px] font-black text-slate-300 text-center">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</td>
                                    <td class="px-8 py-6">
                                        <div class="flex flex-col">
                                            <span class="text-sm font-black text-slate-800 tracking-tight transition-colors group-hover/row:text-indigo-600">{{ $student->full_name }}</span>
                                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">ID: {{ $student->student_id }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-6">
                                        <div class="relative group/input">
                                            <select name="records[{{ $student->id }}][conduct]" 
                                                    class="w-full bg-white/50 border-slate-100 rounded-xl py-3 px-4 text-center text-sm font-black focus:ring-indigo-600 focus:border-indigo-600 shadow-inner group-hover/input:border-indigo-200 transition-all appearance-none cursor-pointer">
                                                <option value="">-</option>
                                                <option value="A" {{ ($record->conduct_grade ?? '') == 'A' ? 'selected' : '' }}>A</option>
                                                <option value="B" {{ ($record->conduct_grade ?? '') == 'B' ? 'selected' : '' }}>B</option>
                                                <option value="C" {{ ($record->conduct_grade ?? '') == 'C' ? 'selected' : '' }}>C</option>
                                            </select>
                                        </div>
                                    </td>
                                    <td class="px-4 py-6">
                                        <div class="relative group/input">
                                            <input type="number" name="records[{{ $student->id }}][absent]" 
                                                   value="{{ $record->days_absent ?? 0 }}" 
                                                   class="w-full bg-white/50 border-slate-100 rounded-xl py-3 px-4 text-center text-sm font-black focus:ring-indigo-600 focus:border-indigo-600 shadow-inner group-hover/input:border-indigo-200 transition-all"
                                                   placeholder="0">
                                        </div>
                                    </td>
                                    <td class="px-8 py-6">
                                        <div class="relative group/input">
                                            <input type="text" name="records[{{ $student->id }}][comment]" 
                                                   value="{{ $record->homeroom_teacher_comment ?? '' }}" 
                                                   class="w-full bg-white/50 border-slate-100 rounded-xl py-3 px-6 text-sm font-bold focus:ring-indigo-600 focus:border-indigo-600 shadow-inner group-hover/input:border-indigo-200 transition-all italic text-slate-600 placeholder:text-slate-300"
                                                   placeholder="Begin evalutative entry...">
                                        </div>
                                    </td>
                                    <td class="px-6 py-6 text-center">
                                        <a href="{{ route('admin.report-cards.pdf', ['student' => $student->id, 'term_id' => $term->id]) }}" 
                                           target="_blank" 
                                           class="w-10 h-10 rounded-xl bg-slate-50 flex items-center justify-center text-slate-400 hover:bg-rose-50 hover:text-rose-600 transition-all shadow-sm border border-slate-100 group/link mx-auto">
                                            <svg class="w-5 h-5 transition-transform group-hover/link:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            </form>

        </div>
        
        <!-- Floating Bottom Command Bar -->
        <div class="fixed bottom-6 left-1/2 -translate-x-1/2 z-40 flex items-center gap-1 p-1.5 bg-white/90 backdrop-blur-xl border border-slate-200 shadow-2xl shadow-slate-200/50 rounded-2xl animate-in slide-in-from-bottom-12 duration-500">
            <div class="flex items-center gap-3 pl-4 pr-2 border-r border-slate-100">
                <div class="flex flex-col">
                    <span class="text-[10px] uppercase tracking-wider font-bold text-slate-400 leading-none">Status</span>
                    <span class="text-sm font-bold text-slate-700 leading-tight">Ready</span>
                </div>
            </div>

            <a href="{{ route('admin.section-grades.index') }}" class="px-5 py-2.5 text-slate-500 font-bold text-xs hover:text-slate-900 transition-colors uppercase tracking-wider">
                Discard
            </a>
            
            <button type="submit" form="reportCardForm" class="px-6 py-2.5 bg-slate-900 text-white font-bold text-xs uppercase tracking-widest rounded-xl hover:bg-indigo-600 transition-all shadow-lg shadow-indigo-100 flex items-center gap-2 group">
                Save Behaviors
                <svg class="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
            </button>
        </div>
    </div>
</x-admin-layout>
