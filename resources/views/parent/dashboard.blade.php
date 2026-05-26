<x-parent-layout header="Dashboard">
    <div class="space-y-8">
        <!-- Welcome Banner -->
        <div class="relative bg-gradient-to-r from-violet-600 via-indigo-600 to-indigo-700 rounded-3xl p-5 sm:p-6 lg:p-8 text-white overflow-hidden shadow-lg shadow-indigo-100 dark:shadow-none">
            <!-- Translucent decorative glow shapes -->
            <div class="absolute -right-10 -top-10 w-48 h-48 bg-white/10 rounded-full blur-2xl"></div>
            <div class="absolute -right-20 -bottom-20 w-64 h-64 bg-indigo-500/20 rounded-full blur-3xl"></div>

            <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                <!-- Left Content -->
                <div class="space-y-2">
                    <span class="text-indigo-200 text-xs font-bold tracking-wider uppercase">Renaissance Parent Portal</span>
                    <h2 class="text-2xl lg:text-3xl font-black font-heading tracking-tight leading-none">
                        Welcome Back, {{ auth()->user()->name }}! 👋
                    </h2>
                    <p class="text-indigo-100 max-w-xl text-sm leading-relaxed pt-1">
                        Monitor your children's academic performance, track daily attendance, view disciplinary reports, and connect with homeroom teachers from your personal dashboard.
                    </p>
                </div>

                <!-- Right Stats Summary -->
                <div class="flex flex-wrap sm:flex-nowrap gap-3">
                    <!-- Academic Year Card -->
                    <div class="flex items-center gap-3 px-4 py-3 bg-white/10 dark:bg-black/20 backdrop-blur-md rounded-2xl border border-white/10 min-w-[140px]">
                        <div class="p-2 bg-white/10 rounded-xl text-white">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <div>
                            <span class="block text-[9px] font-black text-indigo-200 uppercase tracking-widest leading-none">Academic Year</span>
                            <span class="block text-sm font-extrabold text-white mt-1">{{ \App\Helpers\CachedData::activeAcademicYear()?->name ?? 'N/A' }}</span>
                        </div>
                    </div>

                    <!-- Linked Kids Card -->
                    <div class="flex items-center gap-3 px-4 py-3 bg-white/10 dark:bg-black/20 backdrop-blur-md rounded-2xl border border-white/10 min-w-[140px]">
                        <div class="p-2 bg-white/10 rounded-xl text-white">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <span class="block text-[9px] font-black text-indigo-200 uppercase tracking-widest leading-none">Linked Students</span>
                            <span class="block text-sm font-extrabold text-white mt-1">{{ $children->count() }} Children</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- My Children Grid -->
        <div>
            <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-4 font-heading flex items-center gap-2">
                <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
                My Children
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @if($children->isEmpty())
                    <div class="col-span-full p-12 text-center bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl shadow-sm">
                        <svg class="w-16 h-16 text-slate-200 dark:text-slate-700 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        <h3 class="text-lg font-bold text-slate-800 dark:text-slate-200 font-heading">No linked children found</h3>
                        <p class="text-slate-500 dark:text-slate-400 mt-1 max-w-sm mx-auto text-sm">Please contact the administrator to link your account to your children's profiles.</p>
                    </div>
                @else
                    @foreach($children as $child)
                        <div x-data="{ selectedTermId: '{{ $child->default_term_id }}', alerts: {{ json_encode($child->alerts_by_term) }}, mobileDrawerOpen: false }" class="group relative bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl p-6 shadow-sm hover:shadow-xl sm:hover:-translate-y-1 transition-all duration-355 flex flex-col justify-between overflow-hidden">
                            <!-- Card Header -->
                            <div class="flex items-start justify-between gap-4 mb-6">
                                <div class="flex items-center gap-4">
                                    <div class="relative flex-shrink-0">
                                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br {{ $child->nearly_closed_term_rank && $child->nearly_closed_term_rank <= 10 ? 'from-amber-100 to-yellow-200 dark:from-amber-900/60 dark:to-yellow-900/40 ring-2 ring-amber-400/60 dark:ring-amber-500/40' : 'from-indigo-50 to-indigo-100 dark:from-indigo-950 dark:to-slate-900' }} flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold text-xl shadow-inner relative overflow-hidden border {{ $child->nearly_closed_term_rank && $child->nearly_closed_term_rank <= 10 ? 'border-amber-200 dark:border-amber-700' : 'border-slate-100 dark:border-slate-800' }}">
                                            @if($child->photo)
                                                <img src="/storage/{{ $child->photo }}" alt="{{ $child->full_name }}" class="w-full h-full object-cover rounded-2xl">
                                            @else
                                                {{ substr($child->first_name, 0, 1) }}{{ substr($child->father_name, 0, 1) }}
                                            @endif
                                        </div>
                                    </div>
                                    <div>
                                        <h3 class="font-extrabold text-lg text-slate-800 dark:text-slate-100 font-heading group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors leading-tight">
                                            {{ $child->full_name }}
                                        </h3>
                                        <div class="flex items-center gap-2 mt-1.5">
                                            <span class="px-2.5 py-0.5 bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 rounded-lg text-xs font-black">
                                                @if(str_starts_with(strtolower($child->gradeLevel->name ?? ''), 'grade'))
                                                    {{ $child->gradeLevel->name }}
                                                @else
                                                    Grade {{ $child->gradeLevel->name ?? 'N/A' }}
                                                @endif
                                            </span>
                                            <span class="px-2.5 py-0.5 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 rounded-lg text-xs font-black">
                                                Section {{ $child->section->name ?? 'N/A' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex flex-col items-end gap-1.5">
                                    <span class="text-xs font-bold text-slate-400 dark:text-slate-500 font-mono bg-slate-50 dark:bg-slate-800 px-2 py-0.5 rounded">#{{ $child->student_id }}</span>
                                    @if($child->nearly_closed_term_rank)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-black {{ $child->nearly_closed_term_rank <= 3 ? 'bg-gradient-to-r from-amber-50 to-yellow-50 dark:from-amber-950/50 dark:to-yellow-950/40 text-amber-700 dark:text-amber-300 border border-amber-200/60 dark:border-amber-800/50 shadow-sm' : ($child->nearly_closed_term_rank <= 10 ? 'bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 border border-indigo-100/50 dark:border-indigo-900/30' : 'bg-slate-50 dark:bg-slate-800 text-slate-500 dark:text-slate-400') }}">
                                            @if($child->nearly_closed_term_rank <= 3)
                                                {{ ['🥇', '🥈', '🥉'][$child->nearly_closed_term_rank - 1] }}
                                            @endif
                                            #{{ $child->nearly_closed_term_rank }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Performance Stats -->
                            <div class="grid grid-cols-3 gap-4 py-5 border-y border-slate-100 dark:border-slate-800/80 mb-6 text-center">
                                <div>
                                    <span class="block text-[10px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">
                                        {{ $child->nearly_closed_term_name ? $child->nearly_closed_term_name . ' Avg' : 'Avg Grade' }}
                                    </span>
                                    <span class="block mt-1.5 text-lg font-black text-blue-600 dark:text-blue-400 font-heading">
                                        {{ $child->average_score !== null ? $child->average_score . '%' : 'N/A' }}
                                    </span>
                                </div>
                                <div>
                                    <span class="block text-[10px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">Attendance</span>
                                    <span class="block mt-1.5 text-lg font-black font-heading {{ $child->attendance_rate >= 90 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                        {{ $child->attendance_rate }}%
                                    </span>
                                </div>
                                <div>
                                    <span class="block text-[10px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">Incidents</span>
                                    <span class="block mt-1.5 text-lg font-black font-heading {{ $child->conduct_incidents_count > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-500 dark:text-slate-400' }}">
                                        {{ $child->conduct_incidents_count }}
                                    </span>
                                </div>
                            </div>

                            <!-- Subject Alerts Section -->
                            <div class="flex items-center justify-between mt-2 mb-3">
                                <h4 class="text-[10px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500 flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-slate-450" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                    </svg>
                                    Subject Alerts
                                </h4>
                                <!-- Desktop select (hidden on mobile) -->
                                <select x-model="selectedTermId" class="hidden sm:block pl-3 pr-8 py-1.5 border border-indigo-500/80 dark:border-indigo-500/50 bg-white dark:bg-slate-850 text-slate-850 dark:text-slate-100 rounded-xl font-bold text-xs focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition-all cursor-pointer">
                                    @foreach($quarters as $quarter)
                                        <option value="{{ $quarter->id }}">{{ $quarter->name }}</option>
                                    @endforeach
                                </select>

                                <!-- Mobile Drawer trigger button (hidden on desktop) -->
                                <button type="button" @click="mobileDrawerOpen = true" class="sm:hidden flex items-center justify-between pl-3 pr-2.5 py-1.5 border border-indigo-500/80 dark:border-indigo-500/50 bg-white dark:bg-slate-850 text-slate-855 dark:text-slate-100 rounded-xl font-bold text-xs shadow-sm min-w-[100px] hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                                    <span x-text="alerts[selectedTermId]?.term_name || 'Select Term'" class="truncate pr-1"></span>
                                    <svg class="w-3.5 h-3.5 text-indigo-500 dark:text-indigo-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>
                            </div>

                            <!-- Alert Cards Grid -->
                            <div class="grid grid-cols-1 min-[400px]:grid-cols-2 gap-3 mb-5">
                                <!-- Warning Card (Below 75) -->
                                <div class="p-3 rounded-2xl border transition-all duration-300"
                                     :class="(alerts[selectedTermId]?.warning || []).length > 0 
                                             ? 'bg-amber-50/50 border-amber-100/50 text-amber-800 dark:bg-amber-950/20 dark:border-amber-900/30 dark:text-amber-300' 
                                             : 'bg-slate-50/50 border-slate-100 dark:bg-slate-800/10 dark:border-slate-800 text-slate-400 dark:text-slate-500'">
                                    <div class="flex items-center gap-1.5 mb-2">
                                        <span class="w-1.5 h-1.5 rounded-full" 
                                              :class="(alerts[selectedTermId]?.warning || []).length > 0 ? 'bg-amber-500' : 'bg-slate-300 dark:bg-slate-700'"></span>
                                        <span class="text-[9px] font-black uppercase tracking-wider">Warning (< 75)</span>
                                    </div>
                                    
                                    <!-- Alert Subject List -->
                                    <div class="space-y-1.5 max-h-[80px] overflow-y-auto pr-1">
                                        <template x-if="(alerts[selectedTermId]?.warning || []).length > 0">
                                            <template x-for="alert in (alerts[selectedTermId]?.warning || [])" :key="alert.subject_name">
                                                <div class="flex items-center justify-between text-[11px] font-extrabold leading-tight">
                                                    <span class="truncate" x-text="alert.subject_name"></span>
                                                    <span class="font-mono text-[10px]" x-text="alert.score + '%'"></span>
                                                </div>
                                            </template>
                                        </template>
                                        <template x-if="(alerts[selectedTermId]?.warning || []).length === 0">
                                            <span class="text-[10px] font-bold italic block leading-tight">All subjects ≥ 75%</span>
                                        </template>
                                    </div>
                                </div>

                                <!-- Critical Card (50 & Below) -->
                                <div class="p-3 rounded-2xl border transition-all duration-300"
                                     :class="(alerts[selectedTermId]?.critical || []).length > 0 
                                             ? 'bg-rose-50/50 border-rose-100/50 text-rose-800 dark:bg-rose-950/20 dark:border-rose-900/30 dark:text-rose-350' 
                                             : 'bg-slate-50/50 border-slate-100 dark:bg-slate-800/10 dark:border-slate-800 text-slate-400 dark:text-slate-500'">
                                    <div class="flex items-center gap-1.5 mb-2">
                                        <span class="w-1.5 h-1.5 rounded-full" 
                                              :class="(alerts[selectedTermId]?.critical || []).length > 0 ? 'bg-rose-500 animate-pulse' : 'bg-slate-300 dark:bg-slate-700'"></span>
                                        <span class="text-[9px] font-black uppercase tracking-wider">Critical (≤ 50)</span>
                                    </div>
                                    
                                    <!-- Alert Subject List -->
                                    <div class="space-y-1.5 max-h-[80px] overflow-y-auto pr-1">
                                        <template x-if="(alerts[selectedTermId]?.critical || []).length > 0">
                                            <template x-for="alert in (alerts[selectedTermId]?.critical || [])" :key="alert.subject_name">
                                                <div class="flex items-center justify-between text-[11px] font-extrabold leading-tight">
                                                    <span class="truncate" x-text="alert.subject_name"></span>
                                                    <span class="font-mono text-[10px]" x-text="alert.score + '%'"></span>
                                                </div>
                                            </template>
                                        </template>
                                        <template x-if="(alerts[selectedTermId]?.critical || []).length === 0">
                                            <span class="text-[10px] font-bold italic block leading-tight">No critical alerts</span>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            <!-- Quicklinks Action bar -->
                             <div class="grid grid-cols-2 min-[400px]:grid-cols-4 gap-2">
                                 <!-- Child Portal Dashboard -->
                                 <a href="{{ route('parent.student.dashboard', $child) }}" class="min-w-0 flex flex-col items-center justify-center p-2.5 bg-slate-50 hover:bg-indigo-50 dark:bg-slate-800/40 dark:hover:bg-slate-800 rounded-2xl text-center group/btn transition-all duration-200">
                                     <div class="w-8 h-8 rounded-xl bg-indigo-100 dark:bg-slate-800 text-indigo-600 dark:text-indigo-400 flex items-center justify-center group-hover/btn:bg-indigo-600 group-hover/btn:text-white transition-all shadow-sm">
                                         <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                         </svg>
                                     </div>
                                     <span class="text-[10px] font-black uppercase text-slate-500 dark:text-slate-400 mt-1.5 truncate w-full px-1">Portal</span>
                                 </a>

                                 <!-- Grades -->
                                 <a href="{{ route('parent.student.grades.index', $child) }}" class="min-w-0 flex flex-col items-center justify-center p-2.5 bg-slate-50 hover:bg-emerald-50 dark:bg-slate-800/40 dark:hover:bg-slate-800 rounded-2xl text-center group/btn transition-all duration-200">
                                     <div class="w-8 h-8 rounded-xl bg-emerald-100 dark:bg-slate-800 text-emerald-600 dark:text-emerald-400 flex items-center justify-center group-hover/btn:bg-emerald-600 group-hover/btn:text-white transition-all shadow-sm">
                                         <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                         </svg>
                                     </div>
                                     <span class="text-[10px] font-black uppercase text-slate-500 dark:text-slate-400 mt-1.5 truncate w-full px-1">Grades</span>
                                 </a>

                                 <!-- Attendance -->
                                 <a href="{{ route('parent.student.attendance.index', $child) }}" class="min-w-0 flex flex-col items-center justify-center p-2.5 bg-slate-50 hover:bg-amber-50 dark:bg-slate-800/40 dark:hover:bg-slate-800 rounded-2xl text-center group/btn transition-all duration-200">
                                     <div class="w-8 h-8 rounded-xl bg-amber-100 dark:bg-slate-800 text-amber-600 dark:text-amber-400 flex items-center justify-center group-hover/btn:bg-amber-500 group-hover/btn:text-white transition-all shadow-sm">
                                         <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                         </svg>
                                     </div>
                                     <span class="text-[10px] font-black uppercase text-slate-500 dark:text-slate-400 mt-1.5 truncate w-full px-1">Attendance</span>
                                 </a>

                                 <!-- Details -->
                                 <a href="{{ route('parent.student.info.show', $child) }}" class="min-w-0 flex flex-col items-center justify-center p-2.5 bg-slate-50 hover:bg-rose-50 dark:bg-slate-800/40 dark:hover:bg-slate-800 rounded-2xl text-center group/btn transition-all duration-200">
                                     <div class="w-8 h-8 rounded-xl bg-rose-100 dark:bg-slate-800 text-rose-600 dark:text-rose-450 flex items-center justify-center group-hover/btn:bg-rose-500 group-hover/btn:text-white transition-all shadow-sm">
                                         <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                         </svg>
                                     </div>
                                     <span class="text-[10px] font-black uppercase text-slate-500 dark:text-slate-400 mt-1.5 truncate w-full px-1">Profile</span>
                                 </a>
                             </div>

                            <!-- Bottom border decorative slide-in glow line -->
                            <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-violet-500 to-indigo-600 scale-x-0 group-hover:scale-x-100 transition-transform duration-300 origin-left"></div>

                            <!-- Mobile Drawer Backdrop & Drawer -->
                            <div x-cloak x-show="mobileDrawerOpen" class="fixed inset-0 z-[100] sm:hidden" x-transition>
                                <!-- Backdrop -->
                                <div @click="mobileDrawerOpen = false" class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm transition-opacity"></div>
                                
                                <!-- Bottom Drawer Container -->
                                <div class="fixed inset-x-0 bottom-0 max-h-[85vh] bg-white dark:bg-slate-900 rounded-t-[32px] border-t border-slate-150 dark:border-slate-800 shadow-2xl p-6 overflow-y-auto flex flex-col transition-all transform duration-300"
                                     x-show="mobileDrawerOpen"
                                     x-transition:enter="transition ease-out duration-300 transform"
                                     x-transition:enter-start="translate-y-full"
                                     x-transition:enter-end="translate-y-0"
                                     x-transition:leave="transition ease-in duration-200 transform"
                                     x-transition:leave-start="translate-y-0"
                                     x-transition:leave-end="translate-y-full">
                                    
                                    <!-- Swipe Indicator bar -->
                                    <div class="w-12 h-1.5 bg-slate-200 dark:bg-slate-700 rounded-full mx-auto mb-5 flex-shrink-0"></div>
                                    
                                    <div class="flex items-center justify-between mb-6 flex-shrink-0">
                                        <h3 class="font-extrabold text-lg text-slate-850 dark:text-slate-100 font-heading">Select Academic Period</h3>
                                        <button type="button" @click="mobileDrawerOpen = false" class="p-2 rounded-full bg-slate-50 dark:bg-slate-800 text-slate-400">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    </div>
                                    
                                    <!-- Option List -->
                                    <div class="space-y-2.5 text-left text-slate-900 dark:text-slate-100">
                                        @foreach($quarters as $quarter)
                                            <button type="button" @click="selectedTermId = '{{ $quarter->id }}'; mobileDrawerOpen = false"
                                                    :class="selectedTermId == '{{ $quarter->id }}' ? 'border-indigo-500 bg-indigo-50/50 dark:bg-indigo-950/20 text-indigo-650 dark:text-indigo-400' : 'border-slate-100 dark:border-slate-800 text-slate-750 dark:text-slate-350'"
                                                    class="w-full text-left px-4 py-3.5 border rounded-2xl font-bold text-sm flex items-center justify-between transition-colors">
                                                <span>{{ $quarter->name }}</span>
                                                <span x-show="selectedTermId == '{{ $quarter->id }}'" class="text-indigo-600">✓</span>
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>

        <!-- Recent Notices Section -->
        @if($notices->isNotEmpty())
            <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl p-6 shadow-sm">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-500 font-heading flex items-center gap-2">
                        <svg class="w-4.5 h-4.5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                        Notice Board
                    </h3>
                    <a href="{{ route('parent.notices.index') }}" class="text-xs font-extrabold uppercase tracking-wider text-indigo-600 dark:text-indigo-400 hover:text-indigo-700">View All</a>
                </div>

                <div class="space-y-4">
                    @foreach($notices as $notice)
                        <div class="p-4 bg-slate-50/50 dark:bg-slate-800/40 border border-slate-100/50 dark:border-slate-800 rounded-2xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                            <div class="flex items-start justify-between gap-4">
                                <div class="space-y-1">
                                    <a href="{{ route('parent.notices.show', $notice) }}" class="font-extrabold text-slate-800 dark:text-slate-100 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors leading-snug block text-base font-heading">
                                        {{ $notice->title }}
                                    </a>
                                    <p class="text-slate-500 dark:text-slate-400 text-xs flex items-center gap-1.5 font-medium">
                                        <svg class="w-3.5 h-3.5 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        <span>Published: {{ $notice->publish_date->format('M d, Y') }}</span>
                                        <span>•</span>
                                        <span>Posted by: {{ $notice->postedBy->name ?? 'Administrator' }}</span>
                                    </p>
                                </div>
                                <a href="{{ route('parent.notices.show', $notice) }}" class="p-2 bg-white dark:bg-slate-800 hover:bg-indigo-50 dark:hover:bg-slate-700 rounded-xl text-slate-400 hover:text-indigo-600 border border-slate-100 dark:border-slate-700/50 shadow-sm transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-parent-layout>