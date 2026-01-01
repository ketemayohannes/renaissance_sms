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
                    Print Matrix
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
            <form action="{{ route('admin.section-grades.store-report-card-entry', $section) }}" method="POST">
                @csrf
                <input type="hidden" name="academic_year_id" value="{{ $academicYear->id }}">
                <input type="hidden" name="term_id" value="{{ $term->id }}">

                <div class="overflow-x-auto">
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
                                            <input type="text" name="records[{{ $student->id }}][conduct]" 
                                                   value="{{ $record->conduct_grade ?? '' }}" 
                                                   class="w-full bg-white/50 border-slate-100 rounded-xl py-3 px-4 text-center text-sm font-black focus:ring-indigo-600 focus:border-indigo-600 shadow-inner group-hover/input:border-indigo-200 transition-all uppercase" 
                                                   placeholder="A+">
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

                <!-- Command Bar -->
                <div class="px-10 py-10 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center text-emerald-500 shadow-xl shadow-slate-200 border border-slate-100">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Active Protection</p>
                            <p class="text-xs font-bold text-slate-600 italic">Analytical data is secured within the temporal vault.</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                         <a href="{{ route('admin.section-grades.index') }}" class="py-4 px-10 text-[10px] font-black text-slate-400 uppercase tracking-widest hover:text-rose-600 transition-colors">Discard Draft</a>
                         <button type="submit" class="py-4 px-16 bg-slate-900 text-white font-black text-[10px] uppercase tracking-[0.2em] rounded-2xl hover:bg-indigo-600 shadow-2xl shadow-indigo-200 transition-all active:scale-95 flex items-center gap-3 group">
                            Commit Behaviors
                            <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                         </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
