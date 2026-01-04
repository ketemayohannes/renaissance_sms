<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <span class="text-xl font-bold text-slate-800">Command Center</span>
            <div class="flex items-center gap-3">
                <div class="bg-indigo-50 border border-indigo-100 rounded-xl px-4 py-2 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-indigo-500 animate-pulse"></span>
                    <span class="text-xs font-black text-indigo-700 uppercase tracking-wider">{{ $academicYear?->name ?? 'N/A' }}</span>
                </div>
            </div>
        </div>
    </x-slot>
    
    <div class="space-y-8">
        <!-- Floating Alert Banner -->
        @if($sectionsMissingAttendance > 0)
        <div class="glass-panel p-6 border-l-8 border-amber-500 flex flex-col md:flex-row items-center justify-between gap-6 hover:shadow-2xl transition-all">
            <div class="flex items-center gap-6">
                <div class="w-16 h-16 rounded-2xl bg-amber-500/10 flex items-center justify-center flex-shrink-0 animate-bounce">
                    <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-xl font-black text-slate-900 uppercase tracking-tight">Attendance Pending</h3>
                    <p class="text-slate-500 font-medium">{{ $sectionsMissingAttendance }} section(s) require attention today.</p>
                </div>
            </div>
            <a href="{{ route('admin.attendance.index') }}" class="vibrant-btn-amber">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                Mark Attendance Now
            </a>
        </div>
        @endif
        
        <!-- Premium KPI Row -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Total Students -->
            <div class="glass-panel p-8 group hover:-translate-y-2 transition-all duration-500">
                <div class="flex items-center justify-between mb-6">
                    <div class="w-14 h-14 rounded-[1.5rem] bg-indigo-500/10 flex items-center justify-center group-hover:bg-indigo-500 group-hover:rotate-12 transition-all duration-500">
                        <svg class="w-7 h-7 text-indigo-600 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                    <span class="px-3 py-1 bg-emerald-500/10 text-emerald-600 rounded-lg text-[10px] font-black uppercase tracking-widest">+{{ number_format($stats['total_students']) }}</span>
                </div>
                <h4 class="text-sm font-black text-slate-400 uppercase tracking-widest mb-1">Total Enrollment</h4>
                <p class="text-4xl font-black text-slate-900 tracking-tight">{{ number_format($stats['total_students']) }}</p>
                <div class="mt-4 pt-4 border-t border-slate-50 flex items-center gap-2">
                    <span class="text-xs font-bold text-slate-400 italic">Global Student Body</span>
                </div>
            </div>
            
            <!-- Total Staff -->
            <div class="glass-panel p-8 group hover:-translate-y-2 transition-all duration-500">
                <div class="flex items-center justify-between mb-6">
                    <div class="w-14 h-14 rounded-[1.5rem] bg-emerald-500/10 flex items-center justify-center group-hover:bg-emerald-500 group-hover:-rotate-12 transition-all duration-500">
                        <svg class="w-7 h-7 text-emerald-600 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <span class="px-3 py-1 bg-indigo-500/10 text-indigo-600 rounded-lg text-[10px] font-black uppercase tracking-widest">Active</span>
                </div>
                <h4 class="text-sm font-black text-slate-400 uppercase tracking-widest mb-1">Total Faculty</h4>
                <p class="text-4xl font-black text-slate-900 tracking-tight">{{ $stats['total_staff'] }}</p>
                <div class="mt-4 pt-4 border-t border-slate-50">
                    <span class="text-xs font-bold text-slate-400 italic">Academic & Support</span>
                </div>
            </div>
            
            <!-- Attendance Rate -->
            <div class="glass-panel p-8 group hover:-translate-y-2 transition-all duration-500">
                <div class="flex items-center justify-between mb-6">
                    <div class="w-14 h-14 rounded-[1.5rem] bg-rose-500/10 flex items-center justify-center group-hover:bg-rose-500 transition-all duration-500">
                        <svg class="w-7 h-7 text-rose-600 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                        </svg>
                    </div>
                    @php
                        $attendanceStatus = match(true) {
                            $stats['today_attendance'] >= 90 => ['Excellent', 'emerald'],
                            $stats['today_attendance'] >= 75 => ['Stable', 'amber'],
                            default => ['Critical', 'rose']
                        };
                    @endphp
                    <span class="px-3 py-1 bg-{{ $attendanceStatus[1] }}-500/10 text-{{ $attendanceStatus[1] }}-600 rounded-lg text-[10px] font-black uppercase tracking-widest">{{ $attendanceStatus[0] }}</span>
                </div>
                <h4 class="text-sm font-black text-slate-400 uppercase tracking-widest mb-1">Today's Presence</h4>
                <p class="text-4xl font-black text-slate-900 tracking-tight">{{ $stats['today_attendance'] }}%</p>
                <div class="mt-4 pt-4 border-t border-slate-50">
                    <span class="text-xs font-bold text-slate-400 italic">Current Daily Statistic</span>
                </div>
            </div>

            <!-- Quick Access Menu -->
            <div class="glass-panel p-6 bg-slate-900 overflow-hidden relative group">
                <div class="absolute inset-0 bg-gradient-to-br from-indigo-600/20 to-purple-600/20 opacity-0 group-hover:opacity-100 transition-opacity duration-700"></div>
                <h4 class="text-xs font-black text-indigo-400 uppercase tracking-[0.2em] mb-4 relative z-10">Power Actions</h4>
                <div class="space-y-3 relative z-10">
                    <a href="{{ route('admin.students.create') }}" class="flex items-center justify-between p-3 rounded-xl bg-white/5 hover:bg-white/10 border border-white/5 transition-all group/item">
                        <span class="text-xs font-bold text-white tracking-wide">Register New Student</span>
                        <svg class="w-4 h-4 text-indigo-400 group-hover/item:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    </a>
                    <a href="{{ route('admin.section-grades.index') }}" class="flex items-center justify-between p-3 rounded-xl bg-white/5 hover:bg-white/10 border border-white/5 transition-all group/item">
                        <span class="text-xs font-bold text-white tracking-wide">Batch Grade Entry</span>
                        <svg class="w-4 h-4 text-emerald-400 group-hover/item:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    </a>
                    <a href="{{ route('admin.employees.create') }}" class="flex items-center justify-between p-3 rounded-xl bg-white/5 hover:bg-white/10 border border-white/5 transition-all group/item">
                        <span class="text-xs font-bold text-white tracking-wide">Onboard New Staff</span>
                        <svg class="w-4 h-4 text-amber-400 group-hover/item:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Enrollment Analytics -->
            <div class="glass-panel p-8 lg:col-span-2">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h3 class="text-xl font-black text-slate-900 uppercase tracking-tight">Enrollment Matrix</h3>
                        <p class="text-xs text-slate-400 font-bold uppercase tracking-widest mt-1">Student Distribution by Grade</p>
                    </div>
                </div>
                <div class="h-[300px]">
                    <canvas id="enrollmentChart"></canvas>
                </div>
            </div>

            <!-- Demographic Breakdown -->
            <div class="glass-panel p-8">
                <h3 class="text-xl font-black text-slate-900 uppercase tracking-tight mb-8">Demographics</h3>
                <div class="h-[200px] relative">
                    <canvas id="genderChart"></canvas>
                </div>
                <div class="mt-8 space-y-4">
                    <div class="flex items-center justify-between p-4 bg-slate-50 rounded-2xl border border-slate-100">
                        <div class="flex items-center gap-3">
                            <div class="w-2 h-2 rounded-full bg-indigo-500"></div>
                            <span class="text-xs font-black text-slate-500 uppercase tracking-widest">Male Students</span>
                        </div>
                        <span class="text-sm font-black text-slate-900">{{ $genderBreakdown['M'] ?? 0 }}</span>
                    </div>
                    <div class="flex items-center justify-between p-4 bg-slate-50 rounded-2xl border border-slate-100">
                        <div class="flex items-center gap-3">
                            <div class="w-2 h-2 rounded-full bg-rose-400"></div>
                            <span class="text-xs font-black text-slate-500 uppercase tracking-widest">Female Students</span>
                        </div>
                        <span class="text-sm font-black text-slate-900">{{ $genderBreakdown['F'] ?? 0 }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- System Status -->
            <div class="glass-panel p-8">
                <h3 class="text-xl font-black text-slate-900 uppercase tracking-tight mb-8">System Health</h3>
                <div class="space-y-4">
                    @php
                        $statusColors = [
                            'Online' => 'bg-emerald-500',
                            'Idle' => 'bg-emerald-500',
                            'Warm' => 'bg-emerald-500',
                            'Offline' => 'bg-rose-500',
                        ];
                    @endphp
                    <div class="flex items-center justify-between p-4 bg-slate-50 rounded-2xl border border-slate-100">
                        <div class="flex items-center gap-3">
                            <div class="w-2 h-2 rounded-full {{ $statusColors[$systemHealth['database']] ?? 'bg-slate-400' }}"></div>
                            <span class="text-xs font-black text-slate-500 uppercase tracking-widest">Database</span>
                        </div>
                        <span class="text-sm font-black text-slate-900">{{ $systemHealth['database'] }}</span>
                    </div>
                    <div class="flex items-center justify-between p-4 bg-slate-50 rounded-2xl border border-slate-100">
                        <div class="flex items-center gap-3">
                            <div class="w-2 h-2 rounded-full {{ $statusColors[$systemHealth['queue']] ?? 'bg-slate-400' }}"></div>
                            <span class="text-xs font-black text-slate-500 uppercase tracking-widest">Queue</span>
                        </div>
                        <span class="text-sm font-black text-slate-900">{{ $systemHealth['queue'] }}</span>
                    </div>
                    <div class="flex items-center justify-between p-4 bg-slate-50 rounded-2xl border border-slate-100">
                        <div class="flex items-center gap-3">
                            <div class="w-2 h-2 rounded-full {{ $statusColors[$systemHealth['cache']] ?? 'bg-slate-400' }}"></div>
                            <span class="text-xs font-black text-slate-500 uppercase tracking-widest">Cache</span>
                        </div>
                        <span class="text-sm font-black text-slate-900">{{ $systemHealth['cache'] }}</span>
                    </div>
                </div>
            </div>

            <!-- Timeline of Activity -->
            <div class="glass-panel p-8 lg:col-span-2">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h3 class="text-xl font-black text-slate-900 uppercase tracking-tight">System Pulse</h3>
                        <p class="text-xs text-slate-400 font-bold uppercase tracking-widest mt-1">Live Audit Feed</p>
                    </div>
                    <a href="{{ route('admin.audit-logs.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-indigo-600 hover:text-white rounded-xl text-[10px] font-black uppercase tracking-[0.2em] transition-all">Full Log</a>
                </div>
                
                <div class="relative space-y-6 before:absolute before:left-[17px] before:top-2 before:bottom-2 before:w-[2px] before:bg-slate-100">
                    @forelse($recentActivity->take(5) as $log)
                        @php
                            $eventConfig = match(true) {
                                str_contains($log->event, 'created') => ['Created', 'bg-emerald-500', 'text-emerald-500'],
                                str_contains($log->event, 'updated') => ['Updated', 'bg-indigo-500', 'text-indigo-500'],
                                str_contains($log->event, 'deleted') => ['Deleted', 'bg-rose-500', 'text-rose-500'],
                                default => ['Action', 'bg-slate-400', 'text-slate-500']
                            };
                            $modelName = str_replace('_', ' ', class_basename($log->auditable_type));
                        @endphp
                        <div class="relative pl-12 group">
                            <div class="absolute left-0 top-1 w-9 h-9 rounded-xl bg-white border-2 border-slate-100 group-hover:border-{{ $eventConfig[2] }} flex items-center justify-center transition-all z-10 shadow-sm">
                                <div class="w-2 h-2 rounded-full {{ $eventConfig[1] }}"></div>
                            </div>
                            <div class="flex flex-col md:flex-row md:items-center justify-between gap-2">
                                <div>
                                    <p class="text-sm font-black text-slate-900">
                                        <span class="text-indigo-600">{{ $log->user?->name ?? 'System' }}</span> 
                                        {{ strtolower($eventConfig[0]) }} a {{ strtolower($modelName) }}
                                    </p>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">{{ $log->created_at->diffForHumans() }}</p>
                                </div>
                                <span class="px-3 py-1 bg-slate-50 text-slate-400 border border-slate-100 rounded-lg text-xs font-black uppercase tracking-widest">#{{ $log->auditable_id }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12">
                            <p class="text-sm font-black text-slate-400 uppercase tracking-widest italic">All Quiet on the Front</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Grade Enrollment List -->
            <div class="glass-panel p-8">
                <h3 class="text-xl font-black text-slate-900 uppercase tracking-tight mb-8">Capacity Report</h3>
                <div class="space-y-3 max-h-[400px] overflow-y-auto custom-scrollbar pr-2">
                    @foreach($studentsByGrade as $grade)
                    <div class="group flex items-center justify-between p-4 bg-slate-50 hover:bg-indigo-600 hover:shadow-xl hover:shadow-indigo-200 rounded-2xl transition-all duration-300 border border-slate-100 hover:border-indigo-600">
                        <span class="text-xs font-black text-slate-700 uppercase tracking-widest group-hover:text-white transition-colors">{{ $grade->grade_name }}</span>
                        <div class="flex items-center gap-3">
                            <span class="text-lg font-black text-slate-900 group-hover:text-white transition-colors">{{ number_format($grade->count) }}</span>
                            <div class="w-2 h-8 bg-slate-200 group-hover:bg-white/20 rounded-full overflow-hidden">
                                <div class="bg-indigo-500 w-full h-1/2 rounded-full"></div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Enrollment Bar Chart - Modernized
            const enrollmentCtx = document.getElementById('enrollmentChart');
            if (enrollmentCtx) {
                const gradient = enrollmentCtx.getContext('2d').createLinearGradient(0, 0, 0, 400);
                gradient.addColorStop(0, '#4f46e5');
                gradient.addColorStop(1, '#818cf8');

                new Chart(enrollmentCtx.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: {!! json_encode($studentsByGrade->pluck('grade_name')) !!},
                        datasets: [{
                            label: 'Students',
                            data: {!! json_encode($studentsByGrade->pluck('count')) !!},
                            backgroundColor: gradient,
                            hoverBackgroundColor: '#4338ca',
                            borderRadius: 12,
                            barThickness: 32,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { 
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#0f172a',
                                titleFont: { size: 14, weight: '900', family: 'Inter' },
                                bodyFont: { size: 13, family: 'Inter' },
                                padding: 16,
                                cornerRadius: 16,
                                displayColors: false,
                            }
                        },
                        scales: {
                            y: { 
                                beginAtZero: true, 
                                grid: { color: '#f8fafc', drawBorder: false },
                                ticks: { font: { size: 11, weight: '700' }, color: '#94a3b8' }
                            },
                            x: { 
                                grid: { display: false },
                                ticks: { font: { size: 11, weight: '700' }, color: '#94a3b8' }
                            }
                        }
                    }
                });
            }
            
            // Gender Donut Chart - Modernized
            const genderCtx = document.getElementById('genderChart');
            if (genderCtx) {
                new Chart(genderCtx.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: ['Male', 'Female'],
                        datasets: [{
                            data: [{{ $genderBreakdown['M'] ?? 0 }}, {{ $genderBreakdown['F'] ?? 0 }}],
                            backgroundColor: ['#4f46e5', '#fb7185'],
                            borderWidth: 8,
                            borderColor: '#ffffff',
                            hoverOffset: 15,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '80%',
                        plugins: { 
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#0f172a',
                                padding: 16,
                                cornerRadius: 16,
                            }
                        }
                    }
                });
            }
        });
    </script>
    @endpush

    <!-- Performance Footer -->
    <div class="mt-12 text-center pb-8">
        <span class="text-[10px] font-black uppercase tracking-widest text-slate-300">
            Engine Performance: {{ $executionTime ?? 'N/A' }}s | Cached Metrics Ready
        </span>
    </div>
</x-admin-layout>
