<x-student-layout header="Academic Dashboard">

    @php
        $daysOfWeekMap = [
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
            7 => 'Sunday'
        ];
    @endphp

    <div class="space-y-8">
        
        <!-- Welcome Section -->
        <div class="relative bg-gradient-to-r from-indigo-600 to-violet-700 rounded-2xl p-6 lg:p-8 text-white overflow-hidden shadow-xl shadow-indigo-100 dark:shadow-none">
            <div class="absolute -right-10 -top-10 w-40 h-40 bg-white/10 rounded-full blur-xl"></div>
            <div class="absolute -right-20 -bottom-20 w-60 h-60 bg-indigo-500/20 rounded-full blur-2xl"></div>
            
            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="space-y-2">
                    <span class="text-indigo-100 text-xs font-semibold tracking-wider uppercase">Renaissance School Portal</span>
                    <h2 class="text-2xl lg:text-3xl font-bold font-heading">Welcome Back, {{ $student->first_name }}! 👋</h2>
                    <p class="text-indigo-150 max-w-2xl text-sm leading-relaxed">
                        @if($student->currentEnrollment)
                            Enrolled in <span class="font-extrabold text-white">{{ $student->currentEnrollment->section->gradeLevel->name }}</span> - Section <span class="font-extrabold text-white">{{ $student->currentEnrollment->section->name }}</span>. Keep track of your classes, attendance, recent grades, and homework tasks below.
                        @else
                            You are not currently enrolled in any active section. Please contact the registrar's office.
                        @endif
                    </p>
                </div>
                
                @if($student->currentEnrollment)
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                        <!-- Classroom Badge -->
                        <div class="bg-white/10 backdrop-blur-md border border-white/15 rounded-xl px-4 py-2 flex items-center gap-3 shadow-md">
                            <div class="w-8 h-8 rounded-lg bg-indigo-500/20 flex items-center justify-center text-indigo-200">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                            </div>
                            <div>
                                <span class="block text-[8px] font-black text-indigo-250 uppercase tracking-widest leading-none mb-0.5">Classroom</span>
                                <span class="text-xs font-bold text-white uppercase tracking-wider">
                                    Section {{ $student->currentEnrollment->section->name }}
                                </span>
                            </div>
                        </div>

                        <!-- Active Term Badge -->
                        @if($activeTerm)
                            <div class="bg-white/10 backdrop-blur-md border border-white/15 rounded-xl px-4 py-2 flex items-center gap-3 shadow-md">
                                <div class="w-8 h-8 rounded-lg bg-emerald-500/20 flex items-center justify-center text-emerald-250">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <span class="block text-[8px] font-black text-emerald-250 uppercase tracking-widest leading-none mb-0.5">Active Term</span>
                                    <span class="text-xs font-bold text-white uppercase tracking-wider">
                                        {{ $activeTerm->name }}
                                    </span>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <!-- Premium Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            
            <!-- Enrollment Details -->
            <div class="glass-panel border-white bg-white/60 dark:bg-slate-900/60 p-6 rounded-[2rem] shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group flex items-center gap-5">
                <div class="w-14 h-14 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-blue-500/30 group-hover:scale-110 transition-transform flex-shrink-0">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between mb-1">
                        <h3 class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest truncate">Grade & Division</h3>
                        <span class="px-2 py-0.5 bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-950/30 rounded-md text-[8px] font-black uppercase tracking-widest">Enrolled</span>
                    </div>
                    <p class="text-2xl font-black text-slate-800 dark:text-slate-100 truncate">
                        @if($student->currentEnrollment)
                            {{ $student->currentEnrollment->section->gradeLevel->name }}
                        @else
                            Not Assigned
                        @endif
                    </p>
                    <div class="flex items-center justify-between mt-2 pt-1 border-t border-slate-100 dark:border-slate-800 text-[10px]">
                        <span class="font-bold text-slate-400 dark:text-slate-500">ID Number</span>
                        <span class="font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-wider">#{{ $student->student_id }}</span>
                    </div>
                </div>
            </div>

            <!-- Attendance Rate KPI -->
            @php
                $rate = $attendanceStats['rate'];
                $rateColor = match(true) {
                    $rate >= 90 => ['emerald', 'bg-emerald-500', 'text-emerald-600 dark:text-emerald-400', 'bg-emerald-500/10', 'Excellent Presence'],
                    $rate >= 75 => ['amber', 'bg-amber-500', 'text-amber-600 dark:text-amber-400', 'bg-amber-500/10', 'Stable Presence'],
                    default => ['rose', 'bg-rose-500', 'text-rose-600 dark:text-rose-400', 'bg-rose-500/10', 'Critical Attendance']
                };
            @endphp
            <div class="glass-panel border-white bg-white/60 dark:bg-slate-900/60 p-6 rounded-[2rem] shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group flex items-center gap-5">
                <div class="w-14 h-14 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-emerald-500/30 group-hover:scale-110 transition-transform flex-shrink-0">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between mb-1">
                        <h3 class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Attendance</h3>
                        <span class="px-2 py-0.5 {{ $rateColor[3] }} {{ $rateColor[2] }} border border-{{ $rateColor[0] }}-200 dark:border-{{ $rateColor[0] }}-900/30 rounded-md text-[8px] font-black uppercase tracking-widest whitespace-nowrap">
                            {{ $rateColor[4] }}
                        </span>
                    </div>
                    <p class="text-2xl font-black text-slate-800 dark:text-slate-100">
                        {{ $attendanceStats['rate'] }}%
                    </p>
                    <!-- Tiny Micro Progress Bar -->
                    <div class="w-full h-1.5 bg-slate-100 dark:bg-slate-800 rounded-full mt-2 overflow-hidden">
                        <div class="h-full {{ $rateColor[1] }}" style="width: {{ $attendanceStats['rate'] }}%"></div>
                    </div>
                    <div class="flex justify-between mt-2 pt-1 border-t border-slate-100 dark:border-slate-800 text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">
                        <span class="text-emerald-500 font-extrabold">{{ $attendanceStats['present'] }}P</span>
                        <span class="text-rose-500 font-extrabold">{{ $attendanceStats['absent'] }}A</span>
                        <span class="text-amber-500 font-extrabold">{{ $attendanceStats['late'] }}L</span>
                        <span class="text-slate-500 font-extrabold">{{ $attendanceStats['excused'] }}E</span>
                    </div>
                </div>
            </div>

            <!-- Conduct & Disciplinary Status KPI -->
            @php
                $conductColor = $disciplinaryCount === 0 
                    ? ['emerald', 'bg-emerald-500/10', 'text-emerald-600 dark:text-emerald-400', 'Excellent Conduct', 'No active disciplinary files'] 
                    : ['rose', 'bg-rose-500/10', 'text-rose-600 dark:text-rose-400', 'Requires Attention', $disciplinaryCount . ' active case(s)'];
            @endphp
            <div class="glass-panel border-white bg-white/60 dark:bg-slate-900/60 p-6 rounded-[2rem] shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group flex items-center gap-5">
                <div class="w-14 h-14 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-indigo-500/30 group-hover:scale-110 transition-transform flex-shrink-0">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between mb-1">
                        <h3 class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Conduct Status</h3>
                        <span class="px-2 py-0.5 {{ $conductColor[1] }} {{ $conductColor[2] }} border border-{{ $conductColor[0] }}-200 dark:border-{{ $conductColor[0] }}-900/30 rounded-md text-[8px] font-black uppercase tracking-widest whitespace-nowrap">
                            {{ $conductColor[3] }}
                        </span>
                    </div>
                    <p class="text-2xl font-black text-slate-800 dark:text-slate-100 truncate">
                        {{ $conductColor[3] }}
                    </p>
                    <div class="flex items-center justify-between mt-3 pt-1 border-t border-slate-100 dark:border-slate-800 text-[10px] min-w-0">
                        <span class="font-bold text-slate-400 dark:text-slate-500 truncate mr-2">{{ $conductColor[4] }}</span>
                        @if($disciplinaryCount > 0)
                            <span class="w-2 h-2 rounded-full bg-rose-500 animate-ping flex-shrink-0"></span>
                        @endif
                    </div>
                </div>
            </div>
            
        </div>

        <!-- Quick Navigation -->
        <div class="glass-panel border-white bg-white/60 dark:bg-slate-900/60 p-8 rounded-[2.5rem] shadow-sm">
            <h4 class="font-black text-xs text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-6">Quick Navigation</h4>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                
                <a href="{{ route('student.grades.index') }}" class="flex items-center gap-4 p-5 rounded-3xl bg-white dark:bg-slate-950 border border-slate-100 dark:border-slate-800 hover:border-indigo-300 dark:hover:border-indigo-500/50 hover:shadow-xl hover:shadow-indigo-500/5 hover:-translate-y-1 transition duration-300 group/nav">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-600 text-white flex items-center justify-center shadow-lg shadow-indigo-500/30 group-hover/nav:scale-110 transition duration-300 flex-shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <div class="text-left min-w-0">
                        <span class="block text-xs font-black text-slate-800 dark:text-slate-200 uppercase tracking-widest leading-none mb-1">Grades</span>
                        <span class="block text-[10px] text-slate-400 dark:text-slate-500 font-bold uppercase tracking-wider truncate">My Marks</span>
                    </div>
                </a>

                <a href="{{ route('student.activities.index') }}" class="flex items-center gap-4 p-5 rounded-3xl bg-white dark:bg-slate-950 border border-slate-100 dark:border-slate-800 hover:border-emerald-300 dark:hover:border-emerald-500/50 hover:shadow-xl hover:shadow-emerald-500/5 hover:-translate-y-1 transition duration-300 group/nav">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white flex items-center justify-center shadow-lg shadow-emerald-500/30 group-hover/nav:scale-110 transition duration-300 flex-shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                        </svg>
                    </div>
                    <div class="text-left min-w-0">
                        <span class="block text-xs font-black text-slate-800 dark:text-slate-200 uppercase tracking-widest leading-none mb-1">Activities</span>
                        <span class="block text-[10px] text-slate-400 dark:text-slate-500 font-bold uppercase tracking-wider truncate">Tasks & Exams</span>
                    </div>
                </a>

                <a href="{{ route('student.profile') }}" class="flex items-center gap-4 p-5 rounded-3xl bg-white dark:bg-slate-950 border border-slate-100 dark:border-slate-800 hover:border-purple-300 dark:hover:border-purple-500/50 hover:shadow-xl hover:shadow-purple-500/5 hover:-translate-y-1 transition duration-300 group/nav">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-purple-500 to-indigo-600 text-white flex items-center justify-center shadow-lg shadow-purple-500/30 group-hover/nav:scale-110 transition duration-300 flex-shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <div class="text-left min-w-0">
                        <span class="block text-xs font-black text-slate-800 dark:text-slate-200 uppercase tracking-widest leading-none mb-1">Profile</span>
                        <span class="block text-[10px] text-slate-400 dark:text-slate-500 font-bold uppercase tracking-wider truncate">My Details</span>
                    </div>
                </a>

                <div class="flex items-center gap-4 p-5 rounded-3xl bg-slate-50/50 dark:bg-slate-900/30 opacity-50 border border-slate-100 dark:border-slate-800 cursor-not-allowed">
                    <div class="w-12 h-12 rounded-2xl bg-slate-400 dark:bg-slate-800 text-white dark:text-slate-600 flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div class="text-left min-w-0">
                        <span class="block text-xs font-black text-slate-600 dark:text-slate-500 uppercase tracking-widest leading-none mb-1">Schedule</span>
                        <span class="block text-[10px] text-slate-400 dark:text-slate-655 font-bold uppercase tracking-wider truncate">Timetable</span>
                    </div>
                </div>

            </div>
        </div>

        <!-- Announcements / Live Notices Board -->
        @if($activeNotices->count() > 0)
            <div class="glass-panel border-white bg-white/60 dark:bg-slate-900/60 p-8 rounded-[2.5rem] shadow-sm">
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center flex-shrink-0 shadow-sm border border-indigo-100/30 dark:border-indigo-950/20">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-slate-900 dark:text-slate-100 uppercase tracking-tight font-heading">Notice Board</h3>
                        <p class="text-[9px] text-slate-400 dark:text-slate-500 font-bold uppercase tracking-widest mt-0.5">Live school announcements</p>
                    </div>
                </div>
                
                <div class="space-y-4">
                    @foreach($activeNotices as $notice)
                        <div class="p-5 rounded-3xl bg-white dark:bg-slate-950/50 border border-slate-100 dark:border-slate-800/80 hover:border-indigo-200 dark:hover:border-indigo-500/30 hover:bg-white/80 dark:hover:bg-slate-950 transition duration-300 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                            <div class="space-y-1">
                                <h4 class="text-sm font-extrabold text-slate-800 dark:text-slate-200 leading-snug">{{ $notice->title }}</h4>
                                <p class="text-xs text-slate-600 dark:text-slate-400 font-medium leading-relaxed">{{ $notice->content }}</p>
                                 @if($notice->attachment)
                                    <a href="/storage/{{ $notice->attachment }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/40 dark:hover:bg-indigo-900/40 border border-indigo-100 dark:border-indigo-900/30 text-indigo-700 dark:text-indigo-300 text-[10px] font-black uppercase tracking-widest rounded-lg transition-colors mt-2">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        Attachment
                                    </a>
                                @endif
                            </div>
                            <div class="flex-shrink-0 flex items-center gap-2 bg-indigo-50 dark:bg-indigo-950/50 border border-indigo-100 dark:border-indigo-900/30 rounded-xl px-3 py-1.5">
                                <span class="text-[9px] font-black text-indigo-700 dark:text-indigo-400 uppercase tracking-widest">
                                    {{ $notice->publish_date }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Left Side Column: Schedule and Grades (2/3 width) -->
            <div class="lg:col-span-2 space-y-8">
                
                <!-- Schedule / Timetable Section -->
                <div class="glass-panel border-white bg-white/60 dark:bg-slate-900/60 p-8 rounded-[2.5rem] shadow-sm space-y-6">
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center flex-shrink-0 shadow-sm border border-indigo-100/30 dark:border-indigo-950/20">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-black text-slate-900 dark:text-slate-100 uppercase tracking-tight font-heading">Weekly Timetable</h3>
                                <p class="text-[9px] text-slate-400 dark:text-slate-555 font-bold uppercase tracking-widest mt-0.5">Your daily class schedule</p>
                            </div>
                        </div>
                    </div>

                    @if($timetable->count() > 0)
                        <div x-data="{ activeDay: {{ date('N') <= 5 ? date('N') : 1 }} }" class="space-y-4">
                            <!-- Day Selector Buttons -->
                            <div class="flex flex-wrap gap-2">
                                @foreach([1, 2, 3, 4, 5] as $dayIndex)
                                    <button 
                                        @click="activeDay = {{ $dayIndex }}"
                                        :class="activeDay === {{ $dayIndex }} ? 'bg-gradient-to-r from-indigo-500 to-indigo-600 text-white shadow-lg shadow-indigo-500/30' : 'bg-white dark:bg-slate-950 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-900 border border-slate-100 dark:border-slate-800'"
                                        class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition duration-300">
                                        {{ $daysOfWeekMap[$dayIndex] }}
                                    </button>
                                @endforeach
                            </div>

                            <!-- Day Schedule Lists -->
                            @foreach([1, 2, 3, 4, 5] as $dayIndex)
                                <div x-show="activeDay === {{ $dayIndex }}" x-transition class="space-y-3">
                                    @php
                                        $dayEntries = $timetable->get($dayIndex, collect());
                                    @endphp

                                    @forelse($dayEntries as $entry)
                                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center p-4 bg-white dark:bg-slate-950/50 hover:bg-white/80 dark:hover:bg-slate-950 border border-slate-100 dark:border-slate-800/80 hover:border-indigo-200 dark:hover:border-indigo-500/30 rounded-2xl transition duration-300 gap-4">
                                            <div class="flex items-center gap-4">
                                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white flex flex-col items-center justify-center flex-shrink-0 shadow-md shadow-indigo-500/20">
                                                    <span class="text-[8px] font-black uppercase leading-none mb-1">Period</span>
                                                    <span class="text-base font-extrabold leading-none">{{ $entry->classPeriod->name }}</span>
                                                </div>
                                                <div>
                                                    <h4 class="text-sm font-extrabold text-slate-800 dark:text-slate-200 leading-snug">
                                                        {{ $entry->teacherAssignment->subject->name ?? 'N/A' }}
                                                    </h4>
                                                    <p class="text-[10px] text-slate-400 dark:text-slate-500 font-bold uppercase tracking-widest mt-0.5">
                                                        Instructor: <span class="text-slate-600 dark:text-slate-400">{{ $entry->teacherAssignment->teacher->name ?? 'N/A' }}</span>
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-3">
                                                <div class="bg-indigo-50 dark:bg-indigo-950/50 border border-indigo-100 dark:border-indigo-900/30 text-indigo-700 dark:text-indigo-400 px-3 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-widest">
                                                    Room {{ $entry->room_number ?? 'TBD' }}
                                                </div>
                                                <div class="bg-slate-50 dark:bg-slate-900 text-slate-600 dark:text-slate-400 border border-slate-100 dark:border-slate-800 px-3 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-widest">
                                                    {{ $entry->classPeriod->start_time->format('H:i') }} - {{ $entry->classPeriod->end_time->format('H:i') }}
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="text-center py-10 bg-slate-50/30 dark:bg-slate-950/30 rounded-2xl border border-dashed border-slate-200 dark:border-slate-800">
                                            <p class="text-xs font-bold text-slate-400 dark:text-slate-500 italic">No classes scheduled for {{ $daysOfWeekMap[$dayIndex] }}</p>
                                        </div>
                                    @endforelse
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12 bg-slate-50/30 dark:bg-slate-950/30 rounded-2xl border border-dashed border-slate-200 dark:border-slate-800">
                            <p class="text-xs font-bold text-slate-400 dark:text-slate-500 italic">Weekly schedule timetable is not set yet for your section.</p>
                        </div>
                    @endif
                </div>

                <!-- Recent Grades Section -->
                <div class="glass-panel border-white bg-white/60 dark:bg-slate-900/60 p-8 rounded-[2.5rem] shadow-sm space-y-6">
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center flex-shrink-0 shadow-sm border border-indigo-100/30 dark:border-indigo-950/20">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-black text-slate-900 dark:text-slate-100 uppercase tracking-tight font-heading">Recent Grades</h3>
                                <p class="text-[9px] text-slate-400 dark:text-slate-555 font-bold uppercase tracking-widest mt-0.5">Your latest academic marks</p>
                            </div>
                        </div>
                        <a href="{{ route('student.grades.index') }}" class="px-4 py-2 bg-white dark:bg-slate-950 border border-slate-100 dark:border-slate-800 hover:bg-indigo-600 hover:text-white dark:hover:bg-indigo-600 dark:hover:text-white rounded-xl text-[9px] font-black uppercase tracking-[0.2em] transition duration-300 shadow-sm font-heading">
                            Full Gradebook
                        </a>
                    </div>

                    @if($recentGrades->count() > 0)
                        <div class="overflow-x-auto custom-scrollbar">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="border-b border-slate-100 dark:border-slate-800">
                                        <th class="text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest pb-3">Subject</th>
                                        <th class="text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest pb-3">Assessment Type</th>
                                        <th class="text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest pb-3">Term</th>
                                        <th class="text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest pb-3 text-center">Score</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50 dark:divide-slate-800/40">
                                    @foreach($recentGrades as $grade)
                                        @php
                                            $gradePercent = $grade->score;
                                            $scoreColor = match(true) {
                                                $gradePercent >= 75 => 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900/30',
                                                $gradePercent >= 50 => 'bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 border border-amber-100 dark:border-amber-900/30',
                                                default => 'bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 border border-rose-100 dark:border-rose-900/30'
                                            };
                                        @endphp
                                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-950/30 transition-colors">
                                            <td class="py-4 pr-4">
                                                <span class="text-sm font-extrabold text-slate-800 dark:text-slate-200 leading-snug">
                                                    {{ $grade->subject->name ?? 'N/A' }}
                                                </span>
                                            </td>
                                            <td class="py-4 pr-4">
                                                <span class="text-xs text-slate-500 dark:text-slate-400 font-bold uppercase tracking-widest">
                                                    {{ $grade->assessmentTemplate->name ?? 'N/A' }}
                                                </span>
                                            </td>
                                            <td class="py-4 pr-4">
                                                <span class="text-xs text-slate-500 dark:text-slate-400 font-bold uppercase tracking-widest">
                                                    {{ $grade->term->name ?? 'N/A' }}
                                                </span>
                                            </td>
                                            <td class="py-4 text-center">
                                                <span class="px-2.5 py-1 rounded-lg text-xs font-black {{ $scoreColor }}">
                                                    {{ number_format($grade->score, 1) }}%
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-12 bg-slate-50/30 dark:bg-slate-950/30 rounded-2xl border border-dashed border-slate-200 dark:border-slate-800">
                            <p class="text-xs font-bold text-slate-400 dark:text-slate-500 italic">No assessment grades have been recorded yet for this academic year.</p>
                        </div>
                    @endif
                </div>

            </div>

            <!-- Right Side Column: Homework and Notices (1/3 width) -->
            <div class="space-y-8">
                
                <!-- Homework & Activities Board -->
                <div class="glass-panel border-white bg-white/60 dark:bg-slate-900/60 p-8 rounded-[2.5rem] shadow-sm space-y-6">
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center flex-shrink-0 shadow-sm border border-indigo-100/30 dark:border-indigo-950/20">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-black text-slate-900 dark:text-slate-100 uppercase tracking-tight font-heading font-heading">Homework & Tasks</h3>
                                <p class="text-[9px] text-slate-400 dark:text-slate-555 font-bold uppercase tracking-widest mt-0.5">Upcoming academic activities</p>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        @forelse($recentActivities as $activity)
                            @php
                                $typeColor = match($activity->type) {
                                    'exam' => 'bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 border border-rose-100 dark:border-rose-900/30',
                                    'assignment' => 'bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-900/30',
                                    default => 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900/30'
                                };
                            @endphp
                            <div class="p-5 rounded-3xl bg-white dark:bg-slate-950/50 border border-slate-100 dark:border-slate-800/80 hover:border-indigo-200 dark:hover:border-indigo-500/30 hover:bg-white/80 dark:hover:bg-slate-950 transition duration-300 space-y-3">
                                <div class="flex justify-between items-start">
                                    <span class="px-2.5 py-0.5 rounded-md text-[9px] font-black uppercase tracking-widest {{ $typeColor }}">
                                        {{ $activity->type }}
                                    </span>
                                    <span class="text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">
                                        Max: {{ number_format($activity->max_score, 0) }} pts
                                    </span>
                                </div>
                                <div class="space-y-1">
                                    <h4 class="text-sm font-extrabold text-slate-800 dark:text-slate-200 leading-snug">
                                        {{ $activity->title }}
                                    </h4>
                                    <p class="text-[10px] text-slate-400 dark:text-slate-500 font-bold uppercase tracking-wider">
                                        Subject: <span class="text-slate-600 dark:text-slate-400">{{ $activity->teacherAssignment->subject->name ?? 'N/A' }}</span>
                                    </p>
                                </div>
                                <div class="pt-2 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between">
                                    <div class="text-[9px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest">
                                        📅 Due: <span class="text-indigo-600 dark:text-indigo-400 font-black">{{ $activity->due_date->format('M j, H:i') }}</span>
                                    </div>
                                    <a href="{{ route('student.activities.show', $activity) }}" class="text-[9px] font-black uppercase tracking-wider text-indigo-700 dark:text-indigo-400 hover:underline">
                                        Open Task →
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-10 bg-slate-50/30 dark:bg-slate-950/30 rounded-2xl border border-dashed border-slate-200 dark:border-slate-800">
                                <p class="text-xs font-bold text-slate-400 dark:text-slate-555 italic">No upcoming tasks or exams published.</p>
                            </div>
                        @endforelse
                    </div>
            </div>

        </div>

    </div>

</div>
</x-student-layout>
