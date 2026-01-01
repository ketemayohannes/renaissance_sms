<x-admin-layout>
    <x-slot name="header">Attendance Hub</x-slot>

    <div class="space-y-12 pb-24">
        <!-- Header & Navigation -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <x-breadcrumb :items="[
                    ['label' => 'Academic Operations', 'url' => '#'],
                    ['label' => 'Attendance Hub', 'url' => '#']
                ]" />
                <h1 class="text-4xl font-black text-slate-900 tracking-tight mt-2 flex items-center gap-3">
                    <span class="w-1.5 h-8 bg-indigo-600 rounded-full"></span>
                    Attendance Command Center
                </h1>
                <p class="text-slate-500 font-semibold mt-1 uppercase text-[10px] tracking-[0.2em] italic">Daily Roster Management & Surveillance Details</p>
            </div>
            
            <div class="flex items-center gap-4">
                <div class="bg-white/60 backdrop-blur-md border border-white px-6 py-3 rounded-2xl shadow-sm">
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest mr-2">Today's Date</span>
                    <span class="text-sm font-black text-slate-700">{{ \Carbon\Carbon::parse($today)->format('D, M j, Y') }}</span>
                </div>
            </div>
        </div>

        <!-- Global Status Overview -->
        @php
            $completedCount = $sections->where('is_complete', true)->count();
            $totalSections = $sections->count();
            $pendingCount = $totalSections - $completedCount;
            $progressPercentage = $totalSections > 0 ? ($completedCount / $totalSections) * 100 : 0;
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Progress Card -->
            <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-xl shadow-slate-200/50 p-8 relative overflow-hidden group hover:-translate-y-1 transition-all duration-500">
                <div class="absolute top-0 right-0 w-40 h-40 bg-emerald-500/10 rounded-full -mr-16 -mt-16 blur-3xl group-hover:bg-emerald-500/20 transition-all duration-500"></div>
                
                <div class="relative z-10">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-500">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-slate-800 tracking-tight">Roster Completion</h3>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Daily Progress</p>
                        </div>
                    </div>
                    
                    <div class="flex items-baseline gap-2 mb-6">
                        <span class="text-5xl font-black text-slate-800 tracking-tighter">{{ $completedCount }}</span>
                        <span class="text-xl font-bold text-slate-400">/ {{ $totalSections }}</span>
                    </div>
                    
                    <div class="w-full bg-slate-100/80 rounded-full h-3 overflow-hidden shadow-inner">
                        <div class="bg-emerald-500 h-full rounded-full transition-all duration-1000 ease-out shadow-[0_0_10px_rgba(16,185,129,0.5)]" style="width: {{ $progressPercentage }}%"></div>
                    </div>
                </div>
            </div>
            
            <!-- Pending Card -->
            <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-xl shadow-slate-200/50 p-8 relative overflow-hidden group hover:-translate-y-1 transition-all duration-500">
                <div class="absolute top-0 right-0 w-40 h-40 bg-amber-500/10 rounded-full -mr-16 -mt-16 blur-3xl group-hover:bg-amber-500/20 transition-all duration-500"></div>
                
                <div class="relative z-10">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-600 flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-500">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-slate-800 tracking-tight">Pending Action</h3>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Required Updates</p>
                        </div>
                    </div>
                    
                    <div class="flex items-baseline gap-2 mb-6">
                        <span class="text-5xl font-black {{ $pendingCount > 0 ? 'text-amber-500' : 'text-slate-300' }} tracking-tighter">{{ $pendingCount }}</span>
                        <span class="text-sm font-bold text-slate-400 uppercase tracking-widest">Sections Pending</span>
                    </div>
                    
                    @if($pendingCount > 0)
                    <div class="inline-flex items-center gap-2 px-4 py-2 bg-amber-50 rounded-xl border border-amber-100">
                        <div class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></div>
                        <span class="text-[10px] font-black text-amber-700 uppercase tracking-wider">Awaiting Input</span>
                    </div>
                    @else
                    <div class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-50 rounded-xl border border-emerald-100">
                        <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                        <span class="text-[10px] font-black text-emerald-700 uppercase tracking-wider">All Clear</span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Active Session Card -->
            <div class="bg-indigo-600 rounded-[2.5rem] border border-indigo-500 shadow-xl shadow-indigo-500/30 p-8 relative overflow-hidden group text-white hover:-translate-y-1 transition-all duration-500">
                <div class="absolute inset-0 bg-gradient-to-br from-indigo-500 to-indigo-700"></div>
                <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-white/10 rounded-full blur-3xl group-hover:bg-white/20 transition-all duration-700"></div>
                
                <div class="relative z-10 h-full flex flex-col justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-white/10 flex items-center justify-center backdrop-blur-sm border border-white/10 group-hover:rotate-12 transition-transform duration-500">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-black tracking-tight">Active Session</h3>
                            <p class="text-[10px] font-bold text-indigo-200 uppercase tracking-widest">{{ date('l') }}</p>
                        </div>
                    </div>
                    
                    <div class="mt-8">
                        <p class="text-[10px] font-bold text-indigo-200 uppercase tracking-widest mb-2">System Monitor</p>
                        <div class="flex items-center gap-3 bg-white/10 backdrop-blur-md rounded-xl p-3 border border-white/10">
                            <div class="w-3 h-3 bg-emerald-400 rounded-full shadow-[0_0_10px_rgba(52,211,153,0.8)] animate-pulse"></div>
                            <span class="text-sm font-black tracking-tight">Live Tracking Active</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section Grid -->
        <div class="space-y-8">
            <div class="flex items-center justify-between px-2">
                <h2 class="text-2xl font-black text-slate-800 tracking-tight flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-white text-indigo-600 flex items-center justify-center shadow-lg shadow-slate-200/50">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    </div>
                    Live Section Feed
                </h2>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5 gap-6">
                @foreach($sections as $section)
                    @if($section->enrolled_count > 0)
                    <a href="{{ route('admin.attendance.register', ['section_id' => $section->id, 'date' => $today]) }}"
                       class="group relative bg-white/80 backdrop-blur-xl rounded-[2rem] border border-white shadow-lg shadow-slate-200/50 p-6 overflow-hidden hover:-translate-y-1 hover:shadow-xl transition-all duration-300">
                        
                        <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br {{ $section->is_complete ? 'from-emerald-500/10 to-teal-500/10' : 'from-amber-500/10 to-orange-500/10' }} rounded-full -mr-16 -mt-16 blur-2xl group-hover:scale-150 transition-transform duration-700"></div>

                        <div class="relative z-10">
                            <div class="flex items-center justify-between mb-6">
                                <div class="w-10 h-10 rounded-2xl flex items-center justify-center transition-all duration-500 {{ $section->is_complete ? 'bg-emerald-50 text-emerald-600 shadow-emerald-100' : 'bg-amber-50 text-amber-600 shadow-amber-100' }} shadow-lg group-hover:scale-110">
                                    @if($section->is_complete)
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    @else
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    @endif
                                </div>
                                @if(!$section->is_complete)
                                <div class="w-2 h-2 bg-amber-500 rounded-full animate-pulse shadow-[0_0_8px_rgba(245,158,11,0.6)]"></div>
                                @endif
                            </div>

                            <div class="mb-6">
                                <h3 class="text-lg font-black text-slate-800 tracking-tight group-hover:text-indigo-600 transition-colors">
                                    {{ $section->gradeLevel->name }}{{ $section->name }}
                                </h3>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Attendance Tracking</p>
                            </div>

                            <div class="space-y-3">
                                <div class="flex justify-between items-end">
                                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Coverage</span>
                                    <span class="text-xs font-black text-slate-700">{{ $section->marked_count }}/{{ $section->enrolled_count }}</span>
                                </div>
                                <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                    <div class="h-full {{ $section->is_complete ? 'bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.4)]' : 'bg-amber-500 shadow-[0_0_8px_rgba(245,158,11,0.4)]' }} transition-all duration-1000 group-hover:scale-x-105 origin-left" style="width: {{ ($section->marked_count / $section->enrolled_count) * 100 }}%"></div>
                                </div>
                            </div>

                            @if($section->is_complete && $section->today_stats)
                                <div class="mt-6 pt-4 border-t border-slate-100 grid grid-cols-3 gap-2">
                                    <div class="text-center bg-emerald-50/50 rounded-lg py-1.5">
                                        <p class="text-[8px] text-emerald-600/60 font-black uppercase mb-0.5">P</p>
                                        <p class="text-xs font-black text-emerald-600">{{ $section->today_stats->present ?? 0 }}</p>
                                    </div>
                                    <div class="text-center bg-rose-50/50 rounded-lg py-1.5">
                                        <p class="text-[8px] text-rose-600/60 font-black uppercase mb-0.5">A</p>
                                        <p class="text-xs font-black text-rose-600">{{ $section->today_stats->absent ?? 0 }}</p>
                                    </div>
                                    <div class="text-center bg-amber-50/50 rounded-lg py-1.5">
                                        <p class="text-[8px] text-amber-600/60 font-black uppercase mb-0.5">L</p>
                                        <p class="text-xs font-black text-amber-600">{{ $section->today_stats->late ?? 0 }}</p>
                                    </div>
                                </div>
                            @elseif(!$section->is_complete)
                                <div class="mt-6 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-300 transform translate-y-2 group-hover:translate-y-0">
                                    <span class="text-[10px] font-black text-white bg-indigo-600 px-4 py-2 rounded-xl shadow-lg shadow-indigo-200 uppercase flex items-center gap-2">
                                        Launch Roster
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                    </span>
                                </div>
                            @endif
                        </div>
                    </a>
                    @endif
                @endforeach
            </div>
        </div>
        
        <!-- Management Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Historical Trace -->
            <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-xl shadow-slate-200/50 p-10 relative overflow-hidden group hover:shadow-2xl transition-all duration-500" x-data="{ 
                gradeLevels: {{ Js::from($gradeLevels) }},
                selectedGrade: '',
                sections: [],
                updateSections() {
                    const grade = this.gradeLevels.find(g => g.id == this.selectedGrade);
                    this.sections = grade ? (grade.sections || []) : [];
                }
            }">
                <div class="absolute top-0 right-0 w-64 h-64 bg-indigo-50/50 rounded-full -mr-20 -mt-20 blur-3xl group-hover:bg-indigo-50/80 transition-all duration-500"></div>

                <div class="relative z-10">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center shadow-inner">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-black text-slate-800 tracking-tight">Historical Trace</h3>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Archive & Modification</p>
                        </div>
                    </div>

                    <form action="{{ route('admin.attendance.register') }}" method="GET" class="space-y-6">
                        <div class="grid grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label for="grade_id" class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Grade Level</label>
                                <select id="grade_id" x-model="selectedGrade" @change="updateSections()" class="premium-input block w-full outline-none focus:ring-2 focus:ring-indigo-500/20 transition-all font-bold text-slate-700" required>
                                    <option value="">Select Grade</option>
                                    @foreach($gradeLevels as $gradeLevel)
                                        <option value="{{ $gradeLevel->id }}">{{ $gradeLevel->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label for="section_id" class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Section</label>
                                <select name="section_id" id="section_id" class="premium-input block w-full outline-none focus:ring-2 focus:ring-indigo-500/20 transition-all font-bold text-slate-700" required>
                                    <option value="">Select Section</option>
                                    <template x-for="section in sections" :key="section.id">
                                        <option :value="section.id" x-text="section.name"></option>
                                    </template>
                                </select>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label for="date" class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Archive Date</label>
                            <input type="date" name="date" id="date" value="{{ date('Y-m-d') }}" class="premium-input block w-full outline-none focus:ring-2 focus:ring-indigo-500/20 transition-all font-bold text-slate-700 uppercase" required>
                        </div>
                        
                        <button type="submit" class="w-full py-4 bg-indigo-600 hover:bg-indigo-700 text-white font-black text-xs uppercase tracking-[0.2em] rounded-xl shadow-lg shadow-indigo-500/30 transition-all hover:-translate-y-0.5 active:translate-y-0 flex items-center justify-center gap-3">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            Access Archives
                        </button>
                    </form>
                </div>
            </div>

            <!-- Performance Intel -->
            <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-xl shadow-slate-200/50 p-10 relative overflow-hidden group hover:shadow-2xl transition-all duration-500" x-data="{ 
                gradeLevels: {{ Js::from($gradeLevels) }},
                selectedGrade2: '',
                sections2: [],
                updateSections2() {
                    const grade = this.gradeLevels.find(g => g.id == this.selectedGrade2);
                    this.sections2 = grade ? (grade.sections || []) : [];
                }
            }">
                <div class="absolute top-0 right-0 w-64 h-64 bg-emerald-50/50 rounded-full -mr-20 -mt-20 blur-3xl group-hover:bg-emerald-50/80 transition-all duration-500"></div>

                <div class="relative z-10">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center shadow-inner">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-black text-slate-800 tracking-tight">Performance Intel</h3>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Analytics & Reporting</p>
                        </div>
                    </div>

                    <form action="{{ route('admin.attendance.report') }}" method="GET" class="space-y-6">
                        <div class="grid grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label for="report_grade_id" class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Grade Level</label>
                                <select id="report_grade_id" x-model="selectedGrade2" @change="updateSections2()" class="premium-input block w-full outline-none focus:ring-2 focus:ring-emerald-500/20 transition-all font-bold text-slate-700" required>
                                    <option value="">Select Grade</option>
                                    @foreach($gradeLevels as $gradeLevel)
                                        <option value="{{ $gradeLevel->id }}">{{ $gradeLevel->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label for="report_section_id" class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Section</label>
                                <select name="section_id" id="report_section_id" class="premium-input block w-full outline-none focus:ring-2 focus:ring-emerald-500/20 transition-all font-bold text-slate-700" required>
                                    <option value="">Select Section</option>
                                    <template x-for="section in sections2" :key="section.id">
                                        <option :value="section.id" x-text="section.name"></option>
                                    </template>
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label for="month" class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Target Month</label>
                                <select name="month" id="month" class="premium-input block w-full outline-none focus:ring-2 focus:ring-emerald-500/20 transition-all font-bold text-slate-700">
                                    @foreach(range(1, 12) as $m)
                                        <option value="{{ $m }}" {{ date('m') == $m ? 'selected' : '' }}>
                                            {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label for="year" class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Fiscal Year</label>
                                <select name="year" id="year" class="premium-input block w-full outline-none focus:ring-2 focus:ring-emerald-500/20 transition-all font-bold text-slate-700">
                                    @foreach(range(date('Y')-1, date('Y')+1) as $y)
                                        <option value="{{ $y }}" {{ date('Y') == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                        <button type="submit" class="w-full py-4 bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs uppercase tracking-[0.2em] rounded-xl shadow-lg shadow-emerald-500/30 transition-all hover:-translate-y-0.5 active:translate-y-0 flex items-center justify-center gap-3">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            Synthesize Report
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
