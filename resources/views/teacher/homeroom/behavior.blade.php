<x-teacher-layout>
    <div class="space-y-8 pb-32">
        <!-- Modern Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center gap-6">
                <a href="{{ route('teacher.homeroom.index') }}" class="w-14 h-14 bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl flex items-center justify-center text-slate-400 hover:text-indigo-600 hover:bg-slate-50 dark:hover:bg-slate-800 transition-all shadow-xl active:scale-90">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 19l-7-7 7-7"></path></svg>
                </a>
                <div>
                    <div class="flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">
                        <span>Homeroom</span>
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        <span>{{ $section->name }}</span>
                    </div>
                    <h1 class="text-4xl font-black text-slate-900 dark:text-white tracking-tight flex items-center gap-3">
                        <span class="w-1.5 h-8 bg-indigo-600 rounded-full shadow-[0_0_15px_rgba(79,70,229,0.4)]"></span>
                        Behavior & Protocol
                    </h1>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <form action="{{ route('teacher.homeroom.behavior') }}" method="GET" class="flex items-center gap-3 bg-white dark:bg-slate-900 p-2 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm">
                    <select name="term_id" onchange="this.form.submit()" class="bg-transparent border-0 text-sm font-black text-slate-700 dark:text-slate-200 focus:ring-0 cursor-pointer">
                        @foreach($terms as $t)
                            <option value="{{ $t->id }}" {{ $term->id == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                        @endforeach
                    </select>
                </form>
            </div>
        </div>

        <!-- Success/Error Alerts -->
        @if (session('success'))
            <div class="bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-100 dark:border-emerald-500/20 text-emerald-800 dark:text-emerald-400 px-6 py-4 rounded-[1.5rem] flex items-center gap-4 animate-in fade-in slide-in-from-top-4 duration-300">
                <div class="w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-500/20 flex items-center justify-center text-emerald-600 dark:text-emerald-400 shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <span class="font-bold text-sm">{{ session('success') }}</span>
            </div>
        @endif

        @if (!$term->is_grading_open)
            <div class="bg-amber-50 dark:bg-amber-500/10 border border-amber-100 dark:border-amber-500/20 text-amber-800 dark:text-amber-400 px-6 py-4 rounded-[1.5rem] flex items-center gap-4 animate-in fade-in slide-in-from-top-4 duration-300">
                <div class="w-8 h-8 rounded-full bg-amber-100 dark:bg-amber-500/20 flex items-center justify-center text-amber-600 dark:text-amber-400 shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-11V7a4 4 0 00-8 0v4h8z"></path></svg>
                </div>
                <span class="font-bold text-sm">Grading Period Closed: You cannot modify records for this quarter.</span>
            </div>
        @endif

        @if (session('error'))
            <div class="bg-rose-50 dark:bg-rose-500/10 border border-rose-100 dark:border-rose-500/20 text-rose-800 dark:text-rose-400 px-6 py-4 rounded-[1.5rem] flex items-center gap-4 animate-in fade-in slide-in-from-top-4 duration-300">
                <div class="w-8 h-8 rounded-full bg-rose-100 dark:bg-rose-500/20 flex items-center justify-center text-rose-600 dark:text-rose-400 shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </div>
                <span class="font-bold text-sm">{{ session('error') }}</span>
            </div>
        @endif

        <!-- Context Info -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl border border-white dark:border-slate-800 rounded-[2.5rem] p-8 shadow-xl shadow-slate-200/50 dark:shadow-black/20 flex items-center gap-6 group">
                <div class="w-16 h-16 rounded-2xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shadow-lg">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
                <div>
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block">Class Section</span>
                    <span class="text-xl font-black text-slate-900 dark:text-white leading-none">{{ $section->gradeLevel->name }} - Section {{ $section->name }}</span>
                    <p class="text-[9px] font-bold text-slate-400 mt-1 uppercase tracking-tighter italic">{{ $students->count() }} Registered Students</p>
                </div>
            </div>

            <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl border border-white dark:border-slate-800 rounded-[2.5rem] p-8 shadow-xl shadow-slate-200/50 dark:shadow-black/20 flex items-center gap-6 group">
                <div class="w-16 h-16 rounded-2xl bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shadow-lg">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block">Reporting Period</span>
                    <span class="text-xl font-black text-indigo-600 dark:text-indigo-400 leading-none">{{ $term->name }}</span>
                    <p class="text-[9px] font-bold text-slate-400 mt-1 uppercase tracking-tighter italic">{{ $academicYear->name }} Academic Year</p>
                </div>
            </div>
        </div>

        <!-- Data Matrix -->
        <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl border border-white dark:border-slate-800 rounded-[3rem] shadow-2xl overflow-hidden group">
            <form action="{{ route('teacher.homeroom.behavior.store') }}" method="POST" id="behaviorForm">
                @csrf
                <input type="hidden" name="academic_year_id" value="{{ $academicYear->id }}">
                <input type="hidden" name="term_id" value="{{ $term->id }}">

                <div class="overflow-x-auto pb-24">
                    <table class="w-full text-left border-separate border-spacing-0">
                        <thead>
                            <tr class="bg-slate-50/50 dark:bg-slate-950/50">
                                <th class="px-6 py-8 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center border-b border-slate-100 dark:border-slate-800">No</th>
                                <th class="px-8 py-8 text-[10px] font-black text-slate-900 dark:text-slate-100 uppercase tracking-widest border-b border-slate-100 dark:border-slate-800">Student Identity</th>
                                <th class="px-4 py-8 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center border-b border-slate-100 dark:border-slate-800 w-32">Conduct Tier</th>
                                <th class="px-4 py-8 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center border-b border-slate-100 dark:border-slate-800 w-32">Total Absence</th>
                                <th class="px-8 py-8 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 dark:border-slate-800">Homeroom Teacher Comment</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50 dark:divide-slate-800">
                            @foreach($students as $index => $student)
                                @php
                                    $record = $records[$student->id] ?? null;
                                @endphp
                                <tr class="hover:bg-indigo-50/30 dark:hover:bg-indigo-500/5 transition-all group/row">
                                    <td class="px-6 py-6 text-[10px] font-black text-slate-300 dark:text-slate-600 text-center">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</td>
                                    <td class="px-8 py-6">
                                        <div class="flex flex-col">
                                            <span class="text-sm font-black text-slate-800 dark:text-slate-200 tracking-tight transition-colors group-hover/row:text-indigo-600">{{ $student->full_name }}</span>
                                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">ID: {{ $student->student_id }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-6">
                                        <div class="relative group/input">
                                            <select name="records[{{ $student->id }}][conduct]" 
                                                    {{ !$term->is_grading_open ? 'disabled' : '' }}
                                                    class="w-full bg-white/50 dark:bg-slate-950/50 border-slate-100 dark:border-slate-800 rounded-xl py-3 px-4 text-center text-sm font-black text-slate-800 dark:text-slate-200 focus:ring-indigo-600 focus:border-indigo-600 shadow-inner group-hover/input:border-indigo-200 transition-all appearance-none cursor-pointer">
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
                                                   {{ !$term->is_grading_open ? 'disabled' : '' }}
                                                   value="{{ $record->days_absent ?? 0 }}" 
                                                   class="w-full bg-white/50 dark:bg-slate-950/50 border-slate-100 dark:border-slate-800 rounded-xl py-3 px-4 text-center text-sm font-black text-slate-800 dark:text-slate-200 focus:ring-indigo-600 focus:border-indigo-600 shadow-inner group-hover/input:border-indigo-200 transition-all"
                                                   placeholder="0">
                                        </div>
                                    </td>
                                    <td class="px-8 py-6">
                                        <div class="relative group/input">
                                            <input type="text" name="records[{{ $student->id }}][comment]" 
                                                   {{ !$term->is_grading_open ? 'disabled' : '' }}
                                                   value="{{ $record->homeroom_teacher_comment ?? '' }}" 
                                                   class="w-full bg-white/50 dark:bg-slate-950/50 border-slate-100 dark:border-slate-800 rounded-xl py-3 px-6 text-sm font-bold text-slate-800 dark:text-slate-200 focus:ring-indigo-600 focus:border-indigo-600 shadow-inner group-hover/input:border-indigo-200 transition-all italic placeholder:text-slate-300 dark:placeholder:text-slate-700"
                                                   placeholder="Begin evaluative entry...">
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </form>
        </div>
        
        <!-- Floating Bottom Command Bar -->
        <div class="fixed bottom-6 left-1/2 -translate-x-1/2 z-40 flex items-center gap-1 p-1.5 bg-white/90 dark:bg-slate-900/90 backdrop-blur-xl border border-slate-200 dark:border-slate-800 shadow-2xl shadow-slate-200/50 dark:shadow-black/50 rounded-2xl animate-in slide-in-from-bottom-12 duration-500">
            <div class="flex items-center gap-3 pl-4 pr-2 border-r border-slate-100 dark:border-slate-800">
                <div class="flex flex-col">
                    <span class="text-[10px] uppercase tracking-wider font-bold text-slate-400 leading-none">Status</span>
                    <span class="text-sm font-bold text-slate-700 dark:text-slate-200 leading-tight">Ready</span>
                </div>
            </div>

            <a href="{{ route('teacher.homeroom.index') }}" class="px-5 py-2.5 text-slate-500 font-bold text-xs hover:text-slate-900 dark:hover:text-white transition-colors uppercase tracking-wider">
                Discard
            </a>
            
            @if($term->is_grading_open)
            <button type="submit" form="behaviorForm" class="px-6 py-2.5 bg-slate-900 dark:bg-indigo-600 text-white font-bold text-xs uppercase tracking-widest rounded-xl hover:bg-indigo-600 dark:hover:bg-indigo-500 transition-all shadow-lg shadow-indigo-100 dark:shadow-indigo-500/20 flex items-center gap-2 group">
                Save Behaviors
                <svg class="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
            </button>
            @else
            <div class="px-6 py-2.5 bg-slate-100 dark:bg-slate-800 text-slate-400 font-bold text-xs uppercase tracking-widest rounded-xl flex items-center gap-2 cursor-not-allowed">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-11V7a4 4 0 00-8 0v4h8z"></path></svg>
                Entry Locked
            </div>
            @endif
        </div>
    </div>
</x-teacher-layout>
