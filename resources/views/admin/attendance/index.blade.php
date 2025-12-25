<x-admin-layout>
    <x-slot name="header">Attendance Hub</x-slot>

    <div class="space-y-6">
            <!-- Breadcrumb -->
            <div class="mb-8 pl-1">
                <x-breadcrumb :items="[
                    ['label' => 'Attendance', 'url' => '#']
                ]" />
            </div>

            <!-- Global Status Overview -->
            @php
                $completedCount = $sections->where('is_complete', true)->count();
                $totalSections = $sections->count();
                $pendingCount = $totalSections - $completedCount;
            @endphp
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
                <div class="glass-card p-8 group relative overflow-hidden">
                    <div class="absolute -right-6 -top-6 w-32 h-32 bg-blue-500/5 rounded-full transition-transform group-hover:scale-150 duration-700"></div>
                    <div class="flex items-center justify-between relative z-10">
                        <div>
                            <p class="text-slate-400 text-[10px] uppercase font-black tracking-widest mb-1">Date Context</p>
                            <p class="text-2xl font-black text-slate-800 tracking-tight">{{ \Carbon\Carbon::parse($today)->format('D, M j, Y') }}</p>
                        </div>
                        <div class="vibrant-gradient-blue text-white p-4 rounded-2xl shadow-xl shadow-blue-200/50 transition-transform group-hover:rotate-12">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                    </div>
                </div>

                <div class="glass-card p-8 group relative overflow-hidden border-emerald-100/30">
                    <div class="absolute -right-6 -top-6 w-32 h-32 bg-emerald-500/5 rounded-full transition-transform group-hover:scale-150 duration-700"></div>
                    <div class="flex items-center justify-between relative z-10">
                        <div>
                            <p class="text-slate-400 text-[10px] uppercase font-black tracking-widest mb-1">Rosters Complete</p>
                            <p class="text-5xl font-black text-emerald-600 tracking-tighter">{{ $completedCount }}<span class="text-lg text-slate-300 ml-2 font-bold italic">/ {{ $totalSections }}</span></p>
                        </div>
                        <div class="vibrant-gradient-emerald text-white p-4 rounded-2xl shadow-xl shadow-emerald-200/50 transition-transform group-hover:scale-110">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                    </div>
                </div>

                <div class="glass-card p-8 group relative overflow-hidden {{ $pendingCount > 0 ? 'border-amber-100/30' : '' }}">
                    <div class="absolute -right-6 -top-6 w-32 h-32 {{ $pendingCount > 0 ? 'bg-amber-500/5' : 'bg-slate-500/5' }} rounded-full transition-transform group-hover:scale-150 duration-700"></div>
                    <div class="flex items-center justify-between relative z-10">
                        <div>
                            <p class="text-slate-400 text-[10px] uppercase font-black tracking-widest mb-1">Action Required</p>
                            <p class="text-5xl font-black {{ $pendingCount > 0 ? 'text-amber-500' : 'text-slate-300' }} tracking-tighter">{{ $pendingCount }}<span class="text-lg text-slate-300 ml-2 font-bold italic">Pending</span></p>
                        </div>
                        <div class="{{ $pendingCount > 0 ? 'vibrant-gradient-amber' : 'vibrant-gradient-slate' }} text-white p-4 rounded-2xl shadow-xl {{ $pendingCount > 0 ? 'shadow-amber-200/50 animate-pulse-subtle' : 'opacity-40' }}">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Action: Section Grid -->
            <div class="premium-card p-10 mb-12 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-64 h-64 bg-slate-50 rounded-bl-[10rem] -mr-12 -mt-12 -z-0"></div>
                
                <div class="relative z-10">
                    <div class="flex items-center justify-between mb-10">
                        <div>
                            <h3 class="text-xl font-black text-slate-800 tracking-tight flex items-center">
                                <span class="w-2 h-8 bg-blue-600 rounded-full mr-4 shadow-lg shadow-blue-200"></span>
                                Live Attendance Roster
                            </h3>
                            <p class="text-slate-400 text-sm mt-1 font-medium italic ml-6">Select a section to begin today's tracking</p>
                        </div>
                        @if($pendingCount > 0)
                            <div class="flex items-center gap-2 text-amber-500 bg-amber-50/50 backdrop-blur-sm px-5 py-2 rounded-2xl border border-amber-100 shadow-sm animate-pulse-subtle">
                                <div class="w-2 h-2 bg-amber-500 rounded-full"></div>
                                <span class="text-[11px] font-black uppercase tracking-widest">Incomplete Coverage</span>
                            </div>
                        @endif
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-6">
                        @foreach($sections as $section)
                            @if($section->enrolled_count > 0)
                            <a href="{{ route('admin.attendance.register', ['section_id' => $section->id, 'date' => $today]) }}"
                               class="premium-card p-6 group hover:border-blue-400 hover:-translate-y-1 relative overflow-hidden bg-white/40 shadow-sm">
                                
                                <div class="relative z-10">
                                    <div class="flex items-center justify-between mb-4">
                                        <div class="flex flex-col">
                                            <span class="font-black text-lg text-slate-800 tracking-tighter leading-none group-hover:text-blue-600 transition-colors">
                                                {{ $section->gradeLevel->name }}{{ $section->name }}
                                            </span>
                                            <span class="text-[9px] text-slate-400 font-bold uppercase tracking-widest mt-1">Section Grade</span>
                                        </div>
                                        <div class="w-10 h-10 rounded-2xl flex items-center justify-center transition-all duration-500 {{ $section->is_complete ? 'bg-emerald-50 text-emerald-600 shadow-emerald-100' : 'bg-amber-50 text-amber-600 shadow-amber-100' }} shadow-lg group-hover:scale-110">
                                            @if($section->is_complete)
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                            @else
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="space-y-2 mt-6">
                                        <div class="flex justify-between items-end mb-1">
                                            <span class="text-[10px] text-slate-400 font-black uppercase tracking-tighter">Engagement</span>
                                            <span class="text-xs font-black text-slate-700 tracking-tighter">{{ $section->marked_count }}/{{ $section->enrolled_count }}</span>
                                        </div>
                                        <div class="w-full h-1.5 bg-slate-100/50 rounded-full overflow-hidden">
                                            <div class="h-full {{ $section->is_complete ? 'bg-emerald-500 shadow-[0_0_10px_rgba(16,185,129,0.3)]' : 'bg-amber-500 shadow-[0_0_10px_rgba(245,158,11,0.3)]' }} transition-all duration-1000 group-hover:scale-x-105 origin-left" style="width: {{ ($section->marked_count / $section->enrolled_count) * 100 }}%"></div>
                                        </div>
                                    </div>

                                    @if($section->is_complete && $section->today_stats)
                                        <div class="mt-6 pt-4 border-t border-slate-50/50 grid grid-cols-3 gap-1">
                                            <div class="text-center">
                                                <p class="text-[8px] text-slate-300 font-black uppercase mb-0.5">P</p>
                                                <p class="text-xs font-black text-emerald-600 bg-emerald-50 rounded-lg py-1">{{ $section->today_stats->present ?? 0 }}</p>
                                            </div>
                                            <div class="text-center">
                                                <p class="text-[8px] text-slate-300 font-black uppercase mb-0.5">A</p>
                                                <p class="text-xs font-black text-rose-500 bg-rose-50 rounded-lg py-1">{{ $section->today_stats->absent ?? 0 }}</p>
                                            </div>
                                            <div class="text-center">
                                                <p class="text-[8px] text-slate-300 font-black uppercase mb-0.5">L</p>
                                                <p class="text-xs font-black text-amber-500 bg-amber-50 rounded-lg py-1">{{ $section->today_stats->late ?? 0 }}</p>
                                            </div>
                                        </div>
                                    @else
                                        <div class="mt-6 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-300">
                                            <span class="text-[10px] font-black text-blue-600 bg-blue-50/50 px-4 py-1.5 rounded-full border border-blue-100 uppercase flex items-center gap-2">
                                                Launch Roster
                                                <svg class="w-3 h-3 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            </a>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Management Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
                <!-- Selection Form -->
                <div class="premium-card p-10 group bg-slate-50/30 border-slate-200/50 relative overflow-hidden" x-data="{ 
                    gradeLevels: {{ Js::from($gradeLevels) }},
                    selectedGrade: '',
                    sections: [],
                    updateSections() {
                        const grade = this.gradeLevels.find(g => g.id == this.selectedGrade);
                        this.sections = grade ? (grade.sections || []) : [];
                    }
                }">
                    <div class="flex items-center justify-between mb-8">
                        <h3 class="text-xl font-black text-slate-800 tracking-tight flex items-center">
                            <span class="w-2 h-8 bg-indigo-500 rounded-full mr-4 shadow-lg shadow-indigo-200"></span>
                            Historical Trace
                        </h3>
                    </div>
                    <form action="{{ route('admin.attendance.register') }}" method="GET" class="space-y-6">
                        <div class="grid grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label for="grade_id" class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Grade Level</label>
                                <select id="grade_id" x-model="selectedGrade" @change="updateSections()" class="premium-input block w-full transition-all font-bold text-slate-700" required>
                                    <option value="">Select Category</option>
                                    @foreach($gradeLevels as $gradeLevel)
                                        <option value="{{ $gradeLevel->id }}">{{ $gradeLevel->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label for="section_id" class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Section</label>
                                <select name="section_id" id="section_id" class="premium-input block w-full transition-all font-bold text-slate-700" required>
                                    <option value="">Select Sub-unit</option>
                                    <template x-for="section in sections" :key="section.id">
                                        <option :value="section.id" x-text="section.name"></option>
                                    </template>
                                </select>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label for="date" class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Archive Date</label>
                            <input type="date" name="date" id="date" value="{{ date('Y-m-d') }}" class="premium-input block w-full transition-all font-bold text-slate-700 uppercase" required>
                        </div>
                        <button type="submit" class="vibrant-btn-blue w-full py-5 rounded-[1.5rem] flex items-center justify-center gap-4 text-sm tracking-widest leading-none outline-none">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            LOAD ROSTER
                        </button>
                    </form>
                </div>

                <!-- Report Form -->
                <div class="premium-card p-10 group bg-emerald-50/20 border-emerald-100/30 relative overflow-hidden" x-data="{ 
                    gradeLevels: {{ Js::from($gradeLevels) }},
                    selectedGrade2: '',
                    sections2: [],
                    updateSections2() {
                        const grade = this.gradeLevels.find(g => g.id == this.selectedGrade2);
                        this.sections2 = grade ? (grade.sections || []) : [];
                    }
                }">
                    <div class="flex items-center justify-between mb-8">
                        <h3 class="text-xl font-black text-slate-800 tracking-tight flex items-center">
                            <span class="w-2 h-8 bg-emerald-500 rounded-full mr-4 shadow-lg shadow-emerald-200"></span>
                            Performance Intel
                        </h3>
                    </div>
                    <form action="{{ route('admin.attendance.report') }}" method="GET" class="space-y-6">
                        <div class="grid grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label for="report_grade_id" class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1 text-emerald-600/60">Core Level</label>
                                <select id="report_grade_id" x-model="selectedGrade2" @change="updateSections2()" class="premium-input block w-full border-emerald-100 focus:ring-emerald-500 transition-all font-bold text-slate-700" required>
                                    <option value="">Select Grade</option>
                                    @foreach($gradeLevels as $gradeLevel)
                                        <option value="{{ $gradeLevel->id }}">{{ $gradeLevel->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label for="report_section_id" class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1 text-emerald-600/60">Subset</label>
                                <select name="section_id" id="report_section_id" class="premium-input block w-full border-emerald-100 focus:ring-emerald-500 transition-all font-bold text-slate-700" required>
                                    <option value="">Select Section</option>
                                    <template x-for="section in sections2" :key="section.id">
                                        <option :value="section.id" x-text="section.name"></option>
                                    </template>
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label for="month" class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1 text-emerald-600/60">Target Month</label>
                                <select name="month" id="month" class="premium-input block w-full border-emerald-100 focus:ring-emerald-500 font-bold text-slate-700">
                                    @foreach(range(1, 12) as $m)
                                        <option value="{{ $m }}" {{ date('m') == $m ? 'selected' : '' }}>
                                            {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label for="year" class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1 text-emerald-600/60">Fiscal Year</label>
                                <select name="year" id="year" class="premium-input block w-full border-emerald-100 focus:ring-emerald-500 font-bold text-slate-700">
                                    @foreach(range(date('Y')-1, date('Y')+1) as $y)
                                        <option value="{{ $y }}" {{ date('Y') == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <button type="submit" class="vibrant-btn-emerald w-full py-5 rounded-[1.5rem] flex items-center justify-center gap-4 text-sm tracking-widest leading-none outline-none">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            SYNTHESIZE DATA
                        </button>
                    </form>
                </div>
            </div>
    </div>
</x-admin-layout>
