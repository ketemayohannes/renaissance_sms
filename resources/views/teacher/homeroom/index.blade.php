<x-teacher-layout>
    <x-slot name="header">
        {{ $section->gradeLevel->name }} - Section {{ $section->name }}
    </x-slot>

    <div class="space-y-6">
        <!-- Overview Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4">
                <div class="w-12 h-12 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-slate-500">Total Students</h3>
                    <p class="text-2xl font-bold text-slate-900 mt-1">{{ $stats['total'] }}</p>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4">
                <div class="w-12 h-12 {{ $atRiskStudents->count() > 0 ? 'bg-amber-50 text-amber-600' : 'bg-emerald-50 text-emerald-600' }} rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-slate-500">At Risk (Absence)</h3>
                    <p class="text-2xl font-bold {{ $stats['at_risk'] > 0 ? 'text-amber-600' : 'text-slate-900' }} mt-1">
                        {{ $stats['at_risk'] }}
                    </p>
                </div>
            </div>

            <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-100 flex items-center justify-around">
                <div class="text-center">
                    <span class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Male</span>
                    <span class="text-lg font-bold text-blue-600">{{ $stats['male'] }}</span>
                </div>
                <div class="w-px h-10 bg-slate-100"></div>
                <div class="text-center">
                    <span class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Female</span>
                    <span class="text-lg font-bold text-pink-600">{{ $stats['female'] }}</span>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex flex-wrap gap-4">
            <a href="{{ route('teacher.homeroom.attendance') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-xl font-medium hover:bg-indigo-700 transition-colors shadow-lg shadow-indigo-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                </svg>
                Mark Daily Attendance
            </a>
            <a href="{{ route('teacher.homeroom.behavior') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-700 dark:text-slate-200 font-medium hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors shadow-sm">
                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                </svg>
                Manage Behavior & Protocol
            </a>
            <button class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-700 dark:text-slate-200 font-medium hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors shadow-sm">
                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                </svg>
                Print Roster
            </button>
        </div>

        @if($atRiskStudents->count() > 0)
        <!-- At Risk Alert Box -->
        <div class="bg-amber-50 border border-amber-200 p-4 rounded-2xl flex items-start gap-3">
            <div class="w-10 h-10 bg-amber-100 rounded-full flex items-center justify-center text-amber-600 flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
            <div class="flex-1">
                <h4 class="text-sm font-bold text-amber-900">Attendance Warning</h4>
                <p class="text-xs text-amber-700 mt-1">
                    {{ $atRiskStudents->count() }} @choice('student has|students have', $atRiskStudents->count()) been absent for 3 or more consecutive days:
                    <span class="font-bold">{{ $atRiskStudents->pluck('user.name')->join(', ') }}</span>.
                    Please perform a welfare check.
                </p>
            </div>
        </div>
        @endif

        <!-- Student Roster Table -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-6 border-b border-slate-100">
                <h2 class="text-lg font-bold text-slate-900 font-heading">Class Roster</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-50/50">
                        <tr>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest w-12 text-center">#</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest">Student</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest">ID Number</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest">Gender</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest">Guardian Info</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($students as $enrollment)
                        <tr class="hover:bg-slate-50/50 transition-colors group">
                            <td class="px-6 py-4 text-xs font-black text-slate-400 text-center bg-slate-50/30">
                                {{ $loop->iteration }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 font-bold overflow-hidden border border-slate-100">
                                        @if($enrollment->student->photo_path)
                                            <img src="{{ Storage::url($enrollment->student->photo_path) }}" alt="Student photo" class="w-full h-full object-cover">
                                        @else
                                            {{ substr($enrollment->student->user->name, 0, 1) }}
                                        @endif
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900 group-hover:text-indigo-600 transition-colors">{{ $enrollment->student->user->name }}</div>
                                        <div class="text-xs text-slate-500">{{ $enrollment->student->admission_number }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-mono text-sm text-slate-600">{{ $enrollment->student->student_id }}</span>
                            </td>
                            <td class="px-6 py-4 uppercase text-xs font-bold tracking-widest {{ in_array(strtoupper($enrollment->student->gender), ['M', 'MALE']) ? 'text-blue-500' : 'text-pink-500' }}">
                                {{ $enrollment->student->gender }}
                            </td>
                             <td class="px-6 py-4">
                                <div class="text-sm font-bold text-slate-700">
                                    {{ $enrollment->student->primaryGuardian->full_name ?? 'N/A' }}
                                </div>
                                <div class="text-[10px] text-slate-400 font-mono">
                                    {{ $enrollment->student->primaryGuardian->phone ?? '' }}
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-teacher-layout>
