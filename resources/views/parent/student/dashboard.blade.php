<x-parent-layout header="{{ $student->full_name }}'s Dashboard">
    <div class="space-y-8">
        <x-breadcrumb :items="[
            ['label' => $student->full_name, 'url' => '#']
        ]" />

        <!-- Welcoming Portal Banner -->
        <div class="relative bg-gradient-to-r from-violet-600 via-indigo-600 to-indigo-700 rounded-3xl p-6 lg:p-8 text-white overflow-hidden shadow-lg shadow-indigo-100 dark:shadow-none">
            <!-- Translucent decorative glow shapes -->
            <div class="absolute -right-10 -top-10 w-48 h-48 bg-white/10 rounded-full blur-2xl"></div>
            <div class="absolute -left-10 -bottom-10 w-48 h-48 bg-indigo-500/20 rounded-full blur-2xl"></div>

            <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                <!-- Left Details -->
                <div class="space-y-3">
                    <span class="text-indigo-200 text-xs font-bold tracking-wider uppercase">Renaissance School Portal</span>
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-xl bg-white/15 backdrop-blur-md flex items-center justify-center font-bold text-lg border border-white/10 shadow-inner uppercase tracking-wider relative">
                            @if($student->photo)
                                <img src="/storage/{{ $student->photo }}" alt="{{ $student->full_name }}" class="w-full h-full object-cover rounded-xl">
                            @else
                                {{ substr($student->first_name, 0, 1) }}{{ substr($student->father_name, 0, 1) }}
                            @endif
                        </div>
                        <div>
                            <h2 class="text-2xl lg:text-3xl font-black font-heading tracking-tight leading-none">
                                Welcome to {{ $student->first_name }}'s Portal! 👋
                            </h2>
                            <p class="text-xs text-indigo-200 mt-1 font-mono uppercase tracking-wide">Admission No: {{ $student->admission_number ?? 'N/A' }}</p>
                        </div>
                    </div>
                    <p class="text-indigo-100 max-w-xl text-sm leading-relaxed pt-1">
                        Enrolled in Grade <span class="font-bold text-white">{{ $student->gradeLevel->name ?? 'N/A' }}</span> - Section <span class="font-bold text-white">{{ $student->section->name ?? 'N/A' }}</span>. Keep track of classes, attendance, recent grades, and conduct records below.
                    </p>
                </div>

                <!-- Right Cards -->
                <div class="grid grid-cols-1 min-[380px]:grid-cols-2 lg:flex lg:flex-nowrap gap-3 w-full lg:w-auto mt-4 lg:mt-0">
                    <!-- Classroom Card -->
                    <div class="flex items-center gap-3 px-4 py-3 bg-white/10 dark:bg-black/20 backdrop-blur-md rounded-2xl border border-white/10 min-w-[140px] flex-1 sm:flex-initial">
                        <div class="p-2 bg-white/10 rounded-xl text-white">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                        </div>
                        <div>
                            <span class="block text-[10px] font-bold text-indigo-200 uppercase tracking-widest leading-none">Classroom</span>
                            <span class="block text-sm font-extrabold text-white mt-1">Section {{ $student->section->name ?? 'N/A' }}</span>
                        </div>
                    </div>

                    <!-- Roll Number Card -->
                    <div class="flex items-center gap-3 px-4 py-3 bg-white/10 dark:bg-black/20 backdrop-blur-md rounded-2xl border border-white/10 min-w-[140px] flex-1 sm:flex-initial">
                        <div class="p-2 bg-white/10 rounded-xl text-white">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <div>
                            <span class="block text-[10px] font-bold text-indigo-200 uppercase tracking-widest leading-none">Roll Number</span>
                            <span class="block text-sm font-extrabold text-white mt-1">#{{ $student->currentEnrollment->roll_number ?? 'N/A' }}</span>
                        </div>
                    </div>

                    @if($nearlyClosedTermRank)
                        <!-- Rank Card -->
                        <div class="flex items-center gap-3 px-4 py-3 {{ $nearlyClosedTermRank <= 10 ? 'bg-gradient-to-br from-amber-500/20 to-yellow-500/10 border-amber-400/40 ring-1 ring-amber-400/30' : 'bg-white/10 dark:bg-black/20' }} backdrop-blur-md rounded-2xl border border-white/10 min-w-[140px] flex-1 sm:flex-initial">
                            <div class="p-2 {{ $nearlyClosedTermRank <= 10 ? 'bg-amber-500/20 text-yellow-300' : 'bg-white/10 text-white' }} rounded-xl">
                                @if($nearlyClosedTermRank <= 3)
                                    <span class="text-base leading-none">{{ ['🥇', '🥈', '🥉'][$nearlyClosedTermRank - 1] }}</span>
                                @else
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                                    </svg>
                                @endif
                            </div>
                            <div>
                                <span class="block text-[10px] font-bold {{ $nearlyClosedTermRank <= 10 ? 'text-amber-200' : 'text-indigo-200' }} uppercase tracking-widest leading-none">
                                    {{ $nearlyClosedTermName ? $nearlyClosedTermName : 'Term' }} Rank
                                </span>
                                <span class="block text-sm font-extrabold text-white mt-1">#{{ $nearlyClosedTermRank }}</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Metric Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Academic/Grade Level Card -->
            <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl p-6 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-5 transition-all duration-300 hover:shadow-md relative overflow-hidden group">
                <div class="flex items-center gap-4">
                    <div class="p-4 bg-blue-600 text-white rounded-2xl shadow-lg shadow-blue-500/30 flex-shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479L12 14zm0 0L5.84 10.578a12.083 12.083 0 00-.665 6.479L12 14zM12 14v7"></path>
                        </svg>
                    </div>
                    <div>
                        <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Grade & Division</span>
                        <h3 class="text-2xl font-black text-slate-800 dark:text-slate-100 mt-1 font-heading leading-none">
                            @if(str_starts_with(strtolower($student->gradeLevel->name ?? ''), 'grade'))
                                {{ $student->gradeLevel->name }}
                            @else
                                Grade {{ $student->gradeLevel->name ?? 'N/A' }}
                            @endif
                        </h3>
                    </div>
                </div>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 self-start">
                    Enrolled
                </span>
                <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-blue-500 to-indigo-500 scale-x-0 group-hover:scale-x-100 transition-transform duration-300 origin-left"></div>
            </div>

            <!-- Attendance Rate Card -->
            <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl p-6 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-5 transition-all duration-300 hover:shadow-md relative overflow-hidden group">
                <div class="flex items-center gap-4">
                    <div class="p-4 bg-emerald-600 text-white rounded-2xl shadow-lg shadow-emerald-500/30 flex-shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div>
                        <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Attendance</span>
                        <h3 class="text-2xl font-black text-slate-800 dark:text-slate-100 mt-1 font-heading leading-none">
                            {{ $attendanceRate }}%
                        </h3>
                    </div>
                </div>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider self-start {{ $attendanceRate >= 90 ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400' : 'bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400' }}">
                    {{ $attendanceRate >= 90 ? 'Excellent Attendance' : 'Critical Attendance' }}
                </span>
                <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-500 to-teal-500 scale-x-0 group-hover:scale-x-100 transition-transform duration-300 origin-left"></div>
            </div>

            <!-- Conduct Status Card -->
            <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl p-6 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-5 transition-all duration-300 hover:shadow-md relative overflow-hidden group">
                <div class="flex items-center gap-4">
                    <div class="p-4 bg-purple-600 text-white rounded-2xl shadow-lg shadow-purple-500/30 flex-shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>
                    <div>
                        <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Conduct Status</span>
                        <h3 class="text-2xl font-black text-slate-800 dark:text-slate-100 mt-1 font-heading leading-none">
                            {{ $student->disciplinaryRecords->count() === 0 ? 'Excellent Conduct' : 'Behavior Incident' }}
                        </h3>
                    </div>
                </div>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider self-start {{ $student->disciplinaryRecords->count() === 0 ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400' : 'bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-450' }}">
                    {{ $student->disciplinaryRecords->count() === 0 ? 'Excellent Conduct' : 'Warning' }}
                </span>
                <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-purple-500 to-indigo-500 scale-x-0 group-hover:scale-x-100 transition-transform duration-300 origin-left"></div>
            </div>
        </div>

        <!-- Quick Navigation -->
        <div>
            <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-4 font-heading">
                Quick Navigation
            </h3>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Grades Quick Link -->
                <a href="{{ route('parent.student.grades.index', $student) }}" class="p-4 bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl flex items-center gap-3.5 transition-all duration-300 hover:-translate-y-0.5 hover:shadow-md group">
                    <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center shadow-md shadow-blue-500/20 transition-transform duration-300 group-hover:scale-105 flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <span class="block text-xs font-black uppercase tracking-wider text-slate-800 dark:text-slate-200 truncate">Grades</span>
                        <span class="block text-[10px] uppercase font-bold text-slate-400 dark:text-slate-500 truncate leading-none mt-0.5">My Marks</span>
                    </div>
                </a>

                <!-- Attendance Quick Link -->
                <a href="{{ route('parent.student.attendance.index', $student) }}" class="p-4 bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl flex items-center gap-3.5 transition-all duration-300 hover:-translate-y-0.5 hover:shadow-md group">
                    <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center shadow-md shadow-emerald-500/20 transition-transform duration-300 group-hover:scale-105 flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <span class="block text-xs font-black uppercase tracking-wider text-slate-800 dark:text-slate-200 truncate">Attendance</span>
                        <span class="block text-[10px] uppercase font-bold text-slate-400 dark:text-slate-500 truncate leading-none mt-0.5">Calendar Logs</span>
                    </div>
                </a>

                <!-- Conduct Quick Link -->
                <a href="{{ route('parent.student.conduct.index', $student) }}" class="p-4 bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl flex items-center gap-3.5 transition-all duration-300 hover:-translate-y-0.5 hover:shadow-md group">
                    <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center shadow-md shadow-indigo-500/20 transition-transform duration-300 group-hover:scale-105 flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <span class="block text-xs font-black uppercase tracking-wider text-slate-800 dark:text-slate-200 truncate">Conduct</span>
                        <span class="block text-[10px] uppercase font-bold text-slate-400 dark:text-slate-500 truncate leading-none mt-0.5">Behavior File</span>
                    </div>
                </a>

                <!-- Profile/Details Quick Link -->
                <a href="{{ route('parent.student.info.show', $student) }}" class="p-4 bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl flex items-center gap-3.5 transition-all duration-300 hover:-translate-y-0.5 hover:shadow-md group">
                    <div class="w-10 h-10 rounded-xl bg-slate-600 text-white flex items-center justify-center shadow-md shadow-slate-500/20 transition-transform duration-300 group-hover:scale-105 flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <span class="block text-xs font-black uppercase tracking-wider text-slate-800 dark:text-slate-200 truncate">Profile</span>
                        <span class="block text-[10px] uppercase font-bold text-slate-400 dark:text-slate-500 truncate leading-none mt-0.5">Student Info</span>
                    </div>
                </a>
            </div>
        </div>

        <!-- Recent Logs Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Recent Marks -->
            <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl p-6 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <div class="space-y-0.5">
                        <h3 class="font-bold text-slate-800 dark:text-slate-100 font-heading">Recent Assessment Scores</h3>
                        <p class="text-xs text-slate-400">Latest mark updates and academic feedback</p>
                    </div>
                    <a href="{{ route('parent.student.grades.index', $student) }}" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:text-indigo-700">View All</a>
                </div>
                <div class="space-y-3">
                    @if($recentMarks->isEmpty())
                        <p class="text-sm text-slate-500 dark:text-slate-400 py-4 text-center">No grades entered yet.</p>
                    @else
                        @foreach($recentMarks as $mark)
                            <div class="flex items-center justify-between p-3.5 bg-slate-50/50 dark:bg-slate-800/40 border border-slate-100/50 dark:border-slate-800 rounded-xl">
                                <div class="min-w-0">
                                    <span class="block text-sm font-bold text-slate-800 dark:text-slate-200 truncate">{{ $mark->subject->name ?? 'N/A' }}</span>
                                    <span class="block text-[10px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500 mt-0.5">{{ $mark->assessmentTemplate->name ?? 'N/A' }} ({{ $mark->term->name ?? '' }})</span>
                                </div>
                                <div class="text-right">
                                    <span class="text-sm font-bold text-indigo-600 dark:text-indigo-400">{{ number_format($mark->score, 1) }}</span>
                                    <span class="text-xs text-slate-400">/ {{ $mark->assessmentTemplate->max_score ?? 100 }}</span>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>

            <!-- Recent Attendance -->
            <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl p-6 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <div class="space-y-0.5">
                        <h3 class="font-bold text-slate-800 dark:text-slate-100 font-heading">Attendance Logs (Latest)</h3>
                        <p class="text-xs text-slate-400">Recent check-in reports and active presence logs</p>
                    </div>
                    <a href="{{ route('parent.student.attendance.index', $student) }}" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:text-indigo-700">View Calendar</a>
                </div>
                <div class="space-y-3">
                    @if($recentAttendance->isEmpty())
                        <p class="text-sm text-slate-500 dark:text-slate-400 py-4 text-center">No attendance logs available.</p>
                    @else
                        @foreach($recentAttendance as $record)
                            <div class="flex items-center justify-between p-3.5 bg-slate-50/50 dark:bg-slate-800/40 border border-slate-100/50 dark:border-slate-800 rounded-xl">
                                <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">{{ $record->attendance_date->format('M d, Y') }}</span>
                                @php
                                    $statusColors = [
                                        'present' => 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400',
                                        'absent' => 'bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-400',
                                        'late' => 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400',
                                        'excused' => 'bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-400',
                                    ];
                                    $statusName = strtolower($record->status);
                                    $colorClass = $statusColors[$statusName] ?? 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400';
                                  @endphp
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ $colorClass }}">
                                    {{ ucfirst($record->status) }}
                                </span>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>

            <!-- Recent Conduct -->
            <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl p-6 shadow-sm col-span-full">
                <div class="flex items-center justify-between mb-4">
                    <div class="space-y-0.5">
                        <h3 class="font-bold text-slate-800 dark:text-slate-100 font-heading">Disciplinary & Conduct Reports</h3>
                        <p class="text-xs text-slate-400">Behavioral remarks, incident details, and warning updates</p>
                    </div>
                    <a href="{{ route('parent.student.conduct.index', $student) }}" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:text-indigo-700">View All Logs</a>
                </div>
                <div class="space-y-3">
                    @if($recentConduct->isEmpty())
                        <p class="text-sm text-slate-500 dark:text-slate-400 py-4 text-center">No conduct reports filed.</p>
                    @else
                        @foreach($recentConduct as $record)
                            <div class="p-4 bg-slate-50/50 dark:bg-slate-800/40 border border-slate-100/50 dark:border-slate-800 rounded-xl flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-bold font-mono text-slate-450">{{ $record->incident_date->format('M d, Y') }}</span>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider
                                            {{ match($record->tier) {
                                                'minor'    => 'bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400',
                                                'moderate' => 'bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400',
                                                'critical' => 'bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400',
                                                default    => 'bg-slate-100 text-slate-500'
                                            } }}">
                                            {{ ucfirst($record->tier) }}
                                        </span>
                                    </div>
                                    <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">{{ $record->infraction_name }}</p>
                                    <p class="text-xs text-slate-550 dark:text-slate-400">{{ $record->description }}</p>
                                </div>
                                <div class="text-left sm:text-right">
                                    <span class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest leading-none mb-1">Action Taken</span>
                                    <span class="text-sm font-extrabold text-slate-850 dark:text-slate-200">{{ $record->action_taken ?? 'Under Review' }}</span>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-parent-layout>