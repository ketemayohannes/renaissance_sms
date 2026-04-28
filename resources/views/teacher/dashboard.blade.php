<x-teacher-layout>
    <div class="space-y-8">
        <!-- Welcome Section -->
        <div class="relative overflow-hidden rounded-[3rem] bg-slate-900 p-10 md:p-14 shadow-2xl glass-panel border-0">
            <div class="absolute inset-0 bg-gradient-to-br from-indigo-600/30 via-transparent to-emerald-500/30 opacity-70"></div>
            <!-- Decorative blur -->
            <div class="absolute -top-24 -right-24 w-96 h-96 bg-indigo-500/20 rounded-full blur-3xl mix-blend-screen pointer-events-none"></div>

            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <h1 class="text-3xl md:text-5xl font-black text-white font-heading tracking-tight mb-2">Welcome back, {{ Auth::user()->name }}!</h1>
                    <p class="text-indigo-100/80 text-lg font-medium">Ready to inspire? Here's your overview for today.</p>
                </div>
                <div class="flex gap-4">
                    <a href="{{ route('teacher.schedule.index') }}" class="px-6 py-3 bg-white/10 hover:bg-white/20 border border-white/20 text-white rounded-2xl font-bold backdrop-blur-md transition-all shadow-lg hover:shadow-xl hover:-translate-y-0.5 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        Schedule
                    </a>
                </div>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- My Classes -->
            <a href="{{ route('teacher.classes.index') }}" class="glass-panel border-white bg-white/60 p-6 rounded-[2rem] shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all group flex items-center gap-5">
                <div class="w-14 h-14 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-blue-500/30 group-hover:scale-110 transition-transform">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">My Classes</h3>
                    <p class="text-3xl font-black text-slate-800">{{ $metrics['classes_count'] ?? 0 }}</p>
                </div>
            </a>

            @if($metrics['has_homeroom'])
            <!-- Homeroom Section -->
            <a href="{{ route('teacher.homeroom.index') }}" class="glass-panel border-indigo-100 bg-indigo-50/50 p-6 rounded-[2rem] shadow-sm hover:shadow-xl hover:shadow-indigo-500/10 hover:-translate-y-1 transition-all group flex items-center gap-5">
                <div class="w-14 h-14 bg-gradient-to-br from-indigo-400 to-purple-600 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-indigo-500/30 group-hover:scale-110 transition-transform">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-[10px] font-black text-indigo-500 uppercase tracking-widest mb-1">{{ $metrics['homeroom_grade'] }} - Section {{ $metrics['homeroom_section'] }}</h3>
                    <p class="text-2xl font-black text-slate-800">{{ $metrics['homeroom_student_count'] }} <span class="text-xs font-bold text-slate-500 uppercase tracking-widest ml-1">Students</span></p>
                </div>
            </a>
            @else
            <!-- Placeholder if no homeroom -->
            <div class="glass-panel border-slate-100 bg-slate-50/50 p-6 rounded-[2rem] shadow-sm flex items-center gap-5 opacity-60 grayscale border-dashed border-2">
                <div class="w-14 h-14 bg-slate-200 rounded-2xl flex items-center justify-center text-slate-400">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
                <div>
                    <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Homeroom</h3>
                    <p class="text-sm font-bold text-slate-500">Not Assigned</p>
                </div>
            </div>
            @endif

            @if($metrics['is_dept_head'])
            <!-- Department Oversight -->
            <a href="{{ route('teacher.department.index') }}" class="glass-panel border-emerald-100 bg-emerald-50/50 p-6 rounded-[2rem] shadow-sm hover:shadow-xl hover:shadow-emerald-500/10 hover:-translate-y-1 transition-all group flex items-center gap-5">
                <div class="w-14 h-14 bg-gradient-to-br from-emerald-400 to-teal-500 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-emerald-500/30 group-hover:scale-110 transition-transform">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-[10px] font-black text-emerald-500 uppercase tracking-widest mb-1">Dept Head</h3>
                    <p class="text-sm font-black text-slate-800 leading-tight uppercase truncate max-w-[120px]">{{ implode(', ', $metrics['headed_departments']) }}</p>
                </div>
            </a>
            @else
            <!-- Today's Attendance overview placeholder -->
            <div class="glass-panel border-white bg-white/60 p-6 rounded-[2rem] shadow-sm flex items-center gap-5 group">
                <div class="w-14 h-14 bg-gradient-to-br from-rose-400 to-orange-500 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-rose-500/30 group-hover:scale-110 transition-transform">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Assignments</h3>
                    <p class="text-2xl font-black text-slate-800">{{ $metrics['pending_assignments_count'] }} <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Pending</span></p>
                </div>
            </div>
            @endif

            <!-- Another Metric -->
            <div class="glass-panel border-white bg-white/60 p-6 rounded-[2rem] shadow-sm flex items-center gap-5">
                <div class="w-14 h-14 bg-gradient-to-br from-amber-400 to-orange-500 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-amber-500/30">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Free Periods</h3>
                    <p class="text-2xl font-black text-slate-800">{{ $metrics['free_periods_count'] }} <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Today</span></p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Today's Schedule (Placeholder until timetable engine is built) -->
            <div class="lg:col-span-2 glass-panel border-white bg-white/60 rounded-[2.5rem] shadow-sm p-8">
                <div class="flex items-center justify-between mb-8">
                    <h2 class="text-xl font-black text-slate-900 font-heading uppercase tracking-widest">Today's Schedule</h2>
                    <span class="px-4 py-2 bg-indigo-50 text-indigo-600 text-[10px] font-black uppercase tracking-widest rounded-xl">{{ $metrics['today_name'] }}</span>
                </div>
                
                <div class="space-y-6 relative before:absolute before:inset-y-0 before:left-5 before:w-1 before:bg-slate-100 before:rounded-full">
                    @forelse($metrics['today_schedule'] as $item)
                        @php
                            $startTime = \Carbon\Carbon::parse($item->classPeriod->start_time);
                            $endTime = \Carbon\Carbon::parse($item->classPeriod->end_time);
                            $isNow = now()->between($startTime, $endTime);
                        @endphp
                        <div class="relative pl-12 group">
                            <div @class([
                                'absolute left-3.5 top-3 w-4 h-4 rounded-full border-4 border-white shadow-sm ring-2 transition-transform group-hover:scale-125',
                                'ring-indigo-500 bg-indigo-500' => $isNow,
                                'ring-slate-200 bg-slate-200' => !$isNow
                            ])></div>
                            <div @class([
                                'p-5 rounded-[1.5rem] border transition-all flex justify-between items-center',
                                'bg-white border-slate-100 shadow-sm hover:shadow-md hover:border-indigo-100' => $isNow,
                                'bg-white/50 border-slate-100 shadow-sm hover:bg-white hover:shadow-md opacity-80 hover:opacity-100' => !$isNow
                            ])>
                                <div>
                                    <div class="flex items-center gap-3 mb-2">
                                        <span @class([
                                            'text-[10px] font-black px-3 py-1 rounded-lg uppercase tracking-widest',
                                            'text-indigo-600 bg-indigo-50' => $isNow,
                                            'text-slate-500 bg-slate-100' => !$isNow
                                        ])>
                                            {{ $startTime->format('h:i A') }} - {{ $endTime->format('h:i A') }}
                                        </span>
                                        @if($isNow)
                                            <span class="h-1.5 w-1.5 rounded-full bg-rose-500 animate-pulse"></span>
                                            <span class="text-[10px] font-black text-rose-500 uppercase tracking-widest">Now Happening</span>
                                        @endif
                                    </div>
                                    <h3 @class([
                                        'font-black text-lg',
                                        'text-slate-800' => $isNow,
                                        'text-slate-700' => !$isNow
                                    ])>{{ $item->teacherAssignment->subject->name }}</h3>
                                    <div class="flex items-center gap-2 mt-1">
                                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                        <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                                            {{ $item->section->gradeLevel->name }} • Section {{ $item->section->name }}
                                        </p>
                                    </div>
                                </div>
                                <a href="{{ route('teacher.classes.show', $item->teacherAssignment->id) }}" class="w-12 h-12 bg-slate-50 hover:bg-indigo-50 text-slate-400 hover:text-indigo-600 rounded-2xl flex items-center justify-center transition-all">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="py-12 flex flex-col items-center justify-center text-center opacity-60">
                            <div class="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center mb-4">
                                <svg class="w-10 h-10 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <p class="text-slate-500 font-bold uppercase tracking-widest text-sm">No classes scheduled for today</p>
                            <p class="text-xs text-slate-400 mt-1">Enjoy your free time or prepare for upcoming lessons.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="glass-panel border-white bg-white/60 rounded-[2.5rem] shadow-sm p-8 flex flex-col">
                <h2 class="text-xl font-black text-slate-900 mb-8 font-heading uppercase tracking-widest">Quick Actions</h2>
                <div class="flex-1 space-y-4">
                    @if($metrics['has_homeroom'])
                    <a href="{{ route('teacher.homeroom.attendance') }}" class="block p-5 bg-white border border-slate-100 rounded-[1.5rem] hover:border-indigo-200 hover:shadow-lg hover:shadow-indigo-500/10 transition-all group relative overflow-hidden">
                        <div class="absolute inset-0 bg-gradient-to-r from-indigo-50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        <div class="relative z-10 flex items-center gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition-all shadow-sm">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                                </svg>
                            </div>
                            <div>
                                <span class="block font-black text-slate-800 uppercase tracking-widest text-xs mb-1">Take Attendance</span>
                                <span class="block text-[10px] font-bold text-slate-400">{{ $metrics['homeroom_grade'] }} - Section {{ $metrics['homeroom_section'] }}</span>
                            </div>
                        </div>
                    <a href="{{ route('teacher.homeroom.behavior') }}" class="block p-5 bg-white border border-slate-100 rounded-[1.5rem] hover:border-amber-200 hover:shadow-lg hover:shadow-amber-500/10 transition-all group relative overflow-hidden">
                        <div class="absolute inset-0 bg-gradient-to-r from-amber-50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        <div class="relative z-10 flex items-center gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-amber-50 border border-amber-100 flex items-center justify-center text-amber-600 group-hover:bg-amber-600 group-hover:text-white transition-all shadow-sm">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                </svg>
                            </div>
                            <div>
                                <span class="block font-black text-slate-800 uppercase tracking-widest text-xs mb-1">Manage Behavior</span>
                                <span class="block text-[10px] font-bold text-slate-400">Conduct & Total Absences</span>
                            </div>
                        </div>
                    </a>
                    @endif
                    
                    <a href="#" class="block p-5 bg-white border border-slate-100 rounded-[1.5rem] hover:border-emerald-200 hover:shadow-lg hover:shadow-emerald-500/10 transition-all group relative overflow-hidden">
                        <div class="absolute inset-0 bg-gradient-to-r from-emerald-50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        <div class="relative z-10 flex items-center gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white transition-all shadow-sm">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </div>
                            <div>
                                <span class="block font-black text-slate-800 uppercase tracking-widest text-xs mb-1">Enter Grades</span>
                                <span class="block text-[10px] font-bold text-slate-400">Update student marks</span>
                            </div>
                        </div>
                    </a>

                    <a href="{{ route('teacher.classes.index') }}" class="block p-5 bg-white border border-slate-100 rounded-[1.5rem] hover:border-blue-200 hover:shadow-lg hover:shadow-blue-500/10 transition-all group relative overflow-hidden">
                        <div class="absolute inset-0 bg-gradient-to-r from-blue-50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        <div class="relative z-10 flex items-center gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-all shadow-sm">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <span class="block font-black text-slate-800 uppercase tracking-widest text-xs mb-1">My Students</span>
                                <span class="block text-[10px] font-bold text-slate-400">View rosters & history</span>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-teacher-layout>
