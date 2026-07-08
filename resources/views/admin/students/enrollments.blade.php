<x-admin-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="font-heading font-bold text-2xl text-slate-900">Enrollment Management</h2>
                <p class="text-slate-500 text-sm mt-1">View and manage all student enrollment records across academic years.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.promotions.history') }}"
                   class="inline-flex items-center px-4 py-2 bg-white border border-slate-200 text-slate-700 text-sm font-semibold rounded-xl hover:bg-slate-50 transition-all gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    Promotion History
                </a>
                <a href="{{ route('admin.students.index') }}"
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl transition-all gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20H2v-2a3 3 0 015.356-1.857"/>
                    </svg>
                    All Students
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        {{-- Breadcrumb --}}
        <x-breadcrumb :items="[
            ['label' => 'Students', 'url' => route('admin.students.index')],
            ['label' => 'Enrollments', 'url' => '#']
        ]" />

        {{-- Stats Row --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <x-ui.glass-card class="p-4 flex items-center gap-4">
                <div class="w-11 h-11 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Records</p>
                    <p class="text-xl font-bold text-slate-900">{{ number_format($totalCount) }}</p>
                </div>
            </x-ui.glass-card>

            <x-ui.glass-card class="p-4 flex items-center gap-4">
                <div class="w-11 h-11 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Active</p>
                    <p class="text-xl font-bold text-slate-900">{{ number_format($activeCount) }}</p>
                </div>
            </x-ui.glass-card>

            <x-ui.glass-card class="p-4 flex items-center gap-4">
                <div class="w-11 h-11 rounded-2xl bg-amber-50 flex items-center justify-center text-amber-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Showing</p>
                    <p class="text-xl font-bold text-slate-900">{{ number_format($enrollments->total()) }}</p>
                </div>
            </x-ui.glass-card>

            <x-ui.glass-card class="p-4 flex items-center gap-4">
                <div class="w-11 h-11 rounded-2xl bg-purple-50 flex items-center justify-center text-purple-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Page</p>
                    <p class="text-xl font-bold text-slate-900">{{ $enrollments->currentPage() }} / {{ $enrollments->lastPage() }}</p>
                </div>
            </x-ui.glass-card>
        </div>

        {{-- Filters --}}
        <x-ui.glass-card class="p-6">
            <form method="GET" action="{{ route('admin.students.enrollments') }}" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                    {{-- Search --}}
                    <div class="lg:col-span-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 block">Search Student</label>
                        <div class="relative">
                            <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            <input type="text" name="search" value="{{ request('search') }}"
                                   placeholder="Name, Student ID, Admission #..."
                                   class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all">
                        </div>
                    </div>

                    {{-- Academic Year --}}
                    <div>
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 block">Academic Year</label>
                        <select name="academic_year_id" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all">
                            <option value="">All Years</option>
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}" {{ request('academic_year_id') == $year->id ? 'selected' : '' }}>
                                    {{ $year->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Grade Level --}}
                    <div>
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 block">Grade Level</label>
                        <select name="grade_level_id" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all">
                            <option value="">All Grades</option>
                            @foreach($gradeLevels as $grade)
                                <option value="{{ $grade->id }}" {{ request('grade_level_id') == $grade->id ? 'selected' : '' }}>
                                    {{ $grade->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Status --}}
                    <div>
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 block">Status</label>
                        <select name="status" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all">
                            <option value="">All Statuses</option>
                            <option value="active"      {{ request('status') === 'active'      ? 'selected' : '' }}>Active</option>
                            <option value="transferred" {{ request('status') === 'transferred' ? 'selected' : '' }}>Transferred</option>
                            <option value="graduated"   {{ request('status') === 'graduated'   ? 'selected' : '' }}>Graduated</option>
                            <option value="withdrawn"   {{ request('status') === 'withdrawn'   ? 'selected' : '' }}>Withdrawn</option>
                            <option value="retained"    {{ request('status') === 'retained'    ? 'selected' : '' }}>Retained</option>
                        </select>
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-1">
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl transition-all shadow-sm shadow-indigo-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/></svg>
                        Apply Filters
                    </button>
                    @if(request()->hasAny(['search', 'academic_year_id', 'grade_level_id', 'status']))
                        <a href="{{ route('admin.students.enrollments') }}"
                           class="inline-flex items-center gap-2 px-5 py-2.5 bg-white border border-slate-200 text-slate-600 text-sm font-semibold rounded-xl hover:bg-slate-50 transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            Clear Filters
                        </a>
                    @endif
                </div>
            </form>
        </x-ui.glass-card>

        {{-- Enrollment Table --}}
        <x-ui.glass-card class="overflow-hidden p-0">
            @if($enrollments->isEmpty())
                <div class="flex flex-col items-center justify-center py-20 text-slate-400">
                    <svg class="w-16 h-16 mb-4 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    <p class="text-sm font-semibold">No enrollment records found.</p>
                    <p class="text-xs mt-1">Try adjusting your filters.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50/70">
                                <th class="text-left px-5 py-3.5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Student</th>
                                <th class="text-left px-4 py-3.5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Grade / Section</th>
                                <th class="text-left px-4 py-3.5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Academic Year</th>
                                <th class="text-left px-4 py-3.5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Enrolled</th>
                                <th class="text-left px-4 py-3.5 text-[10px] font-black text-slate-400 uppercase tracking-widest">End Date</th>
                                <th class="text-left px-4 py-3.5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Roll #</th>
                                <th class="text-left px-4 py-3.5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Status</th>
                                <th class="px-4 py-3.5"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach($enrollments as $enrollment)
                                @php
                                    $student = $enrollment->student;
                                    if (!$student) continue;
                                    $statusColors = [
                                        'active'      => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200',
                                        'transferred' => 'bg-amber-50 text-amber-700 ring-1 ring-amber-200',
                                        'graduated'   => 'bg-purple-50 text-purple-700 ring-1 ring-purple-200',
                                        'withdrawn'   => 'bg-red-50 text-red-700 ring-1 ring-red-200',
                                        'retained'    => 'bg-slate-100 text-slate-600 ring-1 ring-slate-200',
                                    ];
                                    $statusColor = $statusColors[$enrollment->status] ?? 'bg-slate-100 text-slate-600';
                                @endphp
                                <tr class="hover:bg-slate-50/50 transition-colors group">
                                    {{-- Student --}}
                                    <td class="px-5 py-3.5">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-xl bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-xs flex-shrink-0">
                                                {{ strtoupper(substr($student->first_name ?? '?', 0, 1)) }}
                                            </div>
                                            <div>
                                                <p class="font-semibold text-slate-800 text-sm leading-tight">
                                                    {{ $student->first_name }} {{ $student->father_name }}
                                                </p>
                                                <p class="text-xs text-slate-400 font-medium">{{ $student->student_id }}</p>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Grade / Section --}}
                                    <td class="px-4 py-3.5">
                                        <p class="font-semibold text-slate-700">{{ $enrollment->section->gradeLevel->name ?? '—' }}</p>
                                        <p class="text-xs text-slate-400">Section {{ $enrollment->section->name ?? '—' }}</p>
                                    </td>

                                    {{-- Academic Year --}}
                                    <td class="px-4 py-3.5">
                                        <span class="text-slate-700 font-medium">{{ $enrollment->academicYear->name ?? '—' }}</span>
                                    </td>

                                    {{-- Enrolled date --}}
                                    <td class="px-4 py-3.5 text-slate-600 text-xs font-medium">
                                        {{ $enrollment->enrollment_date?->format('M d, Y') ?? '—' }}
                                    </td>

                                    {{-- End date --}}
                                    <td class="px-4 py-3.5 text-slate-500 text-xs font-medium">
                                        {{ $enrollment->end_date?->format('M d, Y') ?? '—' }}
                                    </td>

                                    {{-- Roll number --}}
                                    <td class="px-4 py-3.5">
                                        @if($enrollment->getRawOriginal('roll_number') !== null)
                                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-slate-100 text-slate-700 text-xs font-black">
                                                {{ $enrollment->getRawOriginal('roll_number') }}
                                            </span>
                                        @else
                                            <span class="text-slate-300 text-xs">—</span>
                                        @endif
                                    </td>

                                    {{-- Status badge --}}
                                    <td class="px-4 py-3.5">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider {{ $statusColor }}">
                                            {{ ucfirst($enrollment->status) }}
                                        </span>
                                    </td>

                                    {{-- Actions --}}
                                    <td class="px-4 py-3.5">
                                        <a href="{{ route('admin.students.show', $student) }}"
                                           class="inline-flex items-center gap-1 px-3 py-1.5 bg-white border border-slate-200 text-slate-600 text-xs font-semibold rounded-lg hover:bg-indigo-50 hover:text-indigo-700 hover:border-indigo-200 transition-all opacity-0 group-hover:opacity-100">
                                            View
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if($enrollments->hasPages())
                    <div class="px-5 py-4 border-t border-slate-100">
                        {{ $enrollments->links() }}
                    </div>
                @endif
            @endif
        </x-ui.glass-card>
    </div>
</x-admin-layout>
