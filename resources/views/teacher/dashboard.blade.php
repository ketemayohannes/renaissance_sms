<x-teacher-layout>
    <div class="space-y-6">
        <!-- Welcome Section -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 font-heading">Welcome back, {{ Auth::user()->name }}!</h1>
                <p class="text-slate-500 mt-1">Here's what's happening in your classes today.</p>
            </div>
            <div class="flex gap-3">
                <button class="px-4 py-2 bg-white border border-slate-200 rounded-xl text-slate-600 font-medium hover:bg-slate-50 transition-colors shadow-sm">
                    View Calendar
                </button>
                <button class="px-4 py-2 bg-indigo-600 text-white rounded-xl font-medium hover:bg-indigo-700 transition-colors shadow-lg shadow-indigo-200">
                    + Create Assignment
                </button>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- My Classes -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4">
                <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center text-blue-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest">My Classes</h3>
                    <p class="text-2xl font-black text-slate-900 mt-1">5</p>
                </div>
            </div>

            @if($metrics['has_homeroom'])
            <!-- Homeroom Section -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-indigo-100 bg-indigo-50/30 flex items-center gap-4 group">
                <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition-all">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-xs font-bold text-indigo-400 uppercase tracking-widest">Homeroom: {{ $metrics['homeroom_section'] }}</h3>
                    <p class="text-2xl font-black text-slate-900 mt-1">{{ $metrics['homeroom_student_count'] }} <span class="text-sm font-medium text-slate-400">Students</span></p>
                </div>
            </div>
            @endif

            @if($metrics['is_dept_head'])
            <!-- Department Oversight -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-emerald-100 bg-emerald-50/30 flex items-center gap-4 group">
                <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white transition-all">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-xs font-bold text-emerald-400 uppercase tracking-widest">Dept Head</h3>
                    <p class="text-lg font-black text-slate-900 mt-1">{{ implode(', ', $metrics['headed_departments']) }}</p>
                </div>
            </div>
            @endif

            <!-- Today's Attendance -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4">
                <div class="w-12 h-12 bg-rose-50 rounded-xl flex items-center justify-center text-rose-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest">Attendance</h3>
                    <p class="text-2xl font-black text-slate-900 mt-1">94% <span class="text-sm font-medium text-slate-400">Avg</span></p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Today's Schedule -->
            <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <h2 class="text-lg font-bold text-slate-900 mb-6 font-heading">Today's Schedule</h2>
                <div class="space-y-6 relative before:absolute before:inset-y-0 before:left-4 before:w-0.5 before:bg-slate-100">
                    <!-- Class Item -->
                    <div class="relative pl-10">
                        <div class="absolute left-2 top-2 w-4 h-4 rounded-full border-2 border-white ring-2 ring-indigo-500 bg-indigo-500"></div>
                        <div class="bg-slate-50 p-4 rounded-xl border border-slate-100 hover:border-indigo-200 transition-colors">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-bold text-indigo-600">08:30 AM - 09:15 AM</span>
                                <span class="px-2 py-1 text-xs font-bold text-indigo-700 bg-indigo-100 rounded-lg">Coming Up</span>
                            </div>
                            <h3 class="font-bold text-slate-900">Mathematics - Algebra</h3>
                            <p class="text-sm text-slate-500 mt-1">Grade 9 - Section A • Room 101</p>
                        </div>
                    </div>

                    <!-- Class Item -->
                    <div class="relative pl-10">
                        <div class="absolute left-2 top-2 w-4 h-4 rounded-full border-2 border-white ring-2 ring-slate-200 bg-slate-200"></div>
                        <div class="bg-white p-4 rounded-xl border border-slate-100 hover:border-slate-200 transition-colors opacity-75">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-bold text-slate-500">10:00 AM - 10:45 AM</span>
                            </div>
                            <h3 class="font-bold text-slate-900">Physics - Motion</h3>
                            <p class="text-sm text-slate-500 mt-1">Grade 10 - Section B • Lab 2</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <h2 class="text-lg font-bold text-slate-900 mb-6 font-heading">Quick Actions</h2>
                <div class="grid grid-cols-1 gap-3">
                    <button class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 hover:bg-slate-100 transition-colors text-left group">
                        <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center text-indigo-600 group-hover:scale-110 transition-transform">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                            </svg>
                        </div>
                        <div>
                            <span class="block font-bold text-slate-900 text-sm">Take Attendance</span>
                            <span class="block text-xs text-slate-500">Record daily attendance</span>
                        </div>
                    </button>
                    
                    <button class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 hover:bg-slate-100 transition-colors text-left group">
                        <div class="w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center text-emerald-600 group-hover:scale-110 transition-transform">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </div>
                        <div>
                            <span class="block font-bold text-slate-900 text-sm">Enter Grades</span>
                            <span class="block text-xs text-slate-500">Update student marks</span>
                        </div>
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-teacher-layout>
