<x-admin-layout>
    <x-slot name="header">Dashboard</x-slot>
    
    <!-- Alert Banner (if sections missing attendance) -->
    @if($sectionsMissingAttendance > 0)
    <div class="mb-6 alert-warning flex items-center justify-between">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
            </div>
            <div>
                <p class="text-sm font-semibold text-amber-900">Attendance Pending</p>
                <p class="text-sm text-amber-700">{{ $sectionsMissingAttendance }} section(s) have not marked attendance today.</p>
            </div>
        </div>
        <a href="{{ route('admin.attendance.index') }}" class="btn-primary bg-amber-600 hover:bg-amber-700 whitespace-nowrap">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
            Mark Now
        </a>
    </div>
    @endif
    
    <!-- KPI Cards Row -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6 mb-6">
        <!-- Total Students -->
        <div class="card p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 rounded-xl bg-indigo-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </div>
                <span class="badge badge-success">Active</span>
            </div>
            <p class="kpi-value">{{ number_format($stats['total_students']) }}</p>
            <p class="kpi-label mt-1">Total Students</p>
        </div>
        
        <!-- Total Staff -->
        <div class="card p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 rounded-xl bg-emerald-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
            </div>
            <p class="kpi-value">{{ $stats['total_staff'] }}</p>
            <p class="kpi-label mt-1">Total Staff</p>
        </div>
        
        <!-- Today's Attendance -->
        <div class="card p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                    </svg>
                </div>
                @if($stats['today_attendance'] >= 90)
                    <span class="badge badge-success">Excellent</span>
                @elseif($stats['today_attendance'] >= 75)
                    <span class="badge badge-warning">Good</span>
                @else
                    <span class="badge badge-danger">Low</span>
                @endif
            </div>
            <p class="kpi-value">{{ $stats['today_attendance'] }}%</p>
            <p class="kpi-label mt-1">Today's Attendance</p>
        </div>
        
        <!-- Quick Actions -->
        <div class="card p-6">
            <p class="text-sm font-semibold text-slate-900 mb-4">Quick Actions</p>
            <div class="space-y-3">
                <a href="{{ route('admin.students.create') }}" class="flex items-center gap-3 text-sm text-slate-600 hover:text-indigo-600 transition-colors group">
                    <div class="w-8 h-8 rounded-lg bg-slate-100 group-hover:bg-indigo-100 flex items-center justify-center transition-colors">
                        <svg class="w-4 h-4 text-slate-500 group-hover:text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                        </svg>
                    </div>
                    <span class="font-medium">Register Student</span>
                </a>
                <a href="{{ route('admin.attendance.index') }}" class="flex items-center gap-3 text-sm text-slate-600 hover:text-indigo-600 transition-colors group">
                    <div class="w-8 h-8 rounded-lg bg-slate-100 group-hover:bg-indigo-100 flex items-center justify-center transition-colors">
                        <svg class="w-4 h-4 text-slate-500 group-hover:text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                        </svg>
                    </div>
                    <span class="font-medium">Mark Attendance</span>
                </a>
                <a href="{{ route('admin.section-grades.index') }}" class="flex items-center gap-3 text-sm text-slate-600 hover:text-indigo-600 transition-colors group">
                    <div class="w-8 h-8 rounded-lg bg-slate-100 group-hover:bg-indigo-100 flex items-center justify-center transition-colors">
                        <svg class="w-4 h-4 text-slate-500 group-hover:text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </div>
                    <span class="font-medium">Enter Grades</span>
                </a>
            </div>
        </div>
    </div>
    
    <!-- Analytics Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6 mb-6">
        <!-- Enrollment by Grade (Bar Chart) -->
        <div class="card p-6 lg:col-span-2">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="section-header">Enrollment by Grade</h3>
                    <p class="text-xs text-slate-400 mt-1">Current academic year distribution</p>
                </div>
                <span class="section-subheader">{{ $academicYear?->name ?? 'All Time' }}</span>
            </div>
            <div class="h-64">
                <canvas id="enrollmentChart"></canvas>
            </div>
        </div>
        
        <!-- Gender Demographics (Donut Chart) -->
        <div class="card p-6">
            <h3 class="section-header mb-6">Demographics</h3>
            <div class="h-48 flex items-center justify-center">
                <canvas id="genderChart"></canvas>
            </div>
            <div class="flex justify-center gap-6 mt-6">
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 rounded-full bg-indigo-500"></div>
                    <span class="text-sm text-slate-600">Male ({{ $genderBreakdown['M'] ?? 0 }})</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 rounded-full bg-rose-400"></div>
                    <span class="text-sm text-slate-600">Female ({{ $genderBreakdown['F'] ?? 0 }})</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bottom Row: Activity + Grade Tiers -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6">
        <!-- Recent Activity -->
        <div class="card p-6 lg:col-span-2">
            <div class="flex items-center justify-between mb-6">
                <h3 class="section-header">Recent Activity</h3>
                <a href="{{ route('admin.audit-logs.index') }}" class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">View All →</a>
            </div>
            <div class="space-y-1 max-h-80 overflow-y-auto custom-scrollbar">
                @forelse($recentActivity as $log)
                    @php
                        $eventLabel = match(true) {
                            str_contains($log->event, 'created') => 'created',
                            str_contains($log->event, 'updated') => 'updated',
                            str_contains($log->event, 'deleted') => 'deleted',
                            default => $log->event
                        };
                        $badgeClass = match($eventLabel) {
                            'created' => 'badge-success',
                            'updated' => 'badge-info',
                            'deleted' => 'badge-danger',
                            default => 'badge-neutral'
                        };
                        $modelName = str_replace('_', ' ', class_basename($log->auditable_type));
                    @endphp
                    <div class="flex items-start gap-4 p-3 rounded-lg hover:bg-slate-50 transition-colors">
                        <div class="w-9 h-9 rounded-full bg-slate-100 flex items-center justify-center flex-shrink-0">
                            <span class="text-xs font-semibold text-slate-600">{{ substr($log->user?->name ?? 'S', 0, 1) }}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-slate-900">
                                <span class="font-medium">{{ $log->user?->name ?? 'System' }}</span>
                                <span class="badge {{ $badgeClass }} ml-2">{{ $eventLabel }}</span>
                                <span class="text-slate-600 ml-1">{{ $modelName }}</span>
                            </p>
                            <p class="text-xs text-slate-400 mt-1">{{ $log->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12">
                        <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <p class="text-sm text-slate-500">No recent activity</p>
                    </div>
                @endforelse
            </div>
        </div>
        
        <!-- Enrollment Tiers -->
        <div class="card p-6">
            <h3 class="section-header mb-4">Enrollment by Section</h3>
            <div class="space-y-3 max-h-80 overflow-y-auto custom-scrollbar">
                @foreach($studentsByGrade as $grade)
                <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg hover:bg-slate-100 transition-colors">
                    <span class="text-sm font-medium text-slate-700">{{ $grade->grade_name }}</span>
                    <span class="text-sm font-bold text-indigo-600">{{ $grade->count }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Enrollment Bar Chart
            const enrollmentCtx = document.getElementById('enrollmentChart');
            if (enrollmentCtx) {
                new Chart(enrollmentCtx.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: {!! json_encode($studentsByGrade->pluck('grade_name')) !!},
                        datasets: [{
                            label: 'Students',
                            data: {!! json_encode($studentsByGrade->pluck('count')) !!},
                            backgroundColor: '#4f46e5',
                            borderRadius: 8,
                            barThickness: 28,
                            maxBarThickness: 40,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { 
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#1e293b',
                                titleFont: { size: 13, weight: '600' },
                                bodyFont: { size: 12 },
                                padding: 12,
                                cornerRadius: 8,
                            }
                        },
                        scales: {
                            y: { 
                                beginAtZero: true, 
                                grid: { color: '#f1f5f9', drawBorder: false },
                                ticks: { font: { size: 11 }, color: '#64748b' }
                            },
                            x: { 
                                grid: { display: false },
                                ticks: { font: { size: 11 }, color: '#64748b' }
                            }
                        }
                    }
                });
            }
            
            // Gender Donut Chart
            const genderCtx = document.getElementById('genderChart');
            if (genderCtx) {
                new Chart(genderCtx.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: ['Male', 'Female'],
                        datasets: [{
                            data: [{{ $genderBreakdown['M'] ?? 0 }}, {{ $genderBreakdown['F'] ?? 0 }}],
                            backgroundColor: ['#4f46e5', '#fb7185'],
                            borderWidth: 0,
                            hoverOffset: 4,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '70%',
                        plugins: { 
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#1e293b',
                                titleFont: { size: 13, weight: '600' },
                                bodyFont: { size: 12 },
                                padding: 12,
                                cornerRadius: 8,
                            }
                        }
                    }
                });
            }
        });
    </script>
    @endpush
</x-admin-layout>
