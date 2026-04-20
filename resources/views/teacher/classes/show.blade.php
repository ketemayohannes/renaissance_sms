<x-teacher-layout>
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex items-center gap-4">
            <a href="{{ route('teacher.classes.index') }}" class="p-2 bg-white border border-slate-200 rounded-xl text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 transition-colors shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-slate-900 font-heading">
                    {{ $assignment->subject->name }} - Section {{ $assignment->section->name }}
                </h1>
                <p class="text-slate-500 mt-1">
                    {{ $assignment->section->gradeLevel->name }} • {{ $assignment->section->enrollments->where('status', 'active')->count() }} Students enrolled
                </p>
            </div>
            <div class="ml-auto flex gap-3">
                <button class="px-4 py-2 bg-white border border-slate-200 rounded-xl text-slate-600 font-medium hover:bg-slate-50 transition-colors shadow-sm">
                    Export Roster
                </button>
                <a href="{{ route('teacher.gradebook.entry', $assignment->id) }}" class="px-4 py-2 bg-indigo-600 text-white rounded-xl font-medium hover:bg-indigo-700 transition-colors shadow-lg shadow-indigo-200">
                    Enter Grades
                </a>
            </div>
        </div>

        <!-- Class Roster Table -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                <h2 class="text-lg font-bold text-slate-900">Class Roster</h2>
                <div class="relative">
                    <input type="text" placeholder="Search students..." class="pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors w-64">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-slate-50 border-b border-slate-100 text-slate-500 font-bold uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="px-6 py-4">Student ID</th>
                            <th class="px-6 py-4">Name</th>
                            <th class="px-6 py-4">Gender</th>
                            <th class="px-6 py-4 text-right">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($assignment->section->enrollments->where('status', 'active') as $enrollment)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-4 font-medium text-slate-900">
                                    {{ $enrollment->student->student_id }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-600 font-bold text-xs uppercase">
                                            {{ substr($enrollment->student->first_name, 0, 1) }}{{ substr($enrollment->student->last_name, 0, 1) }}
                                        </div>
                                        <span class="font-medium text-slate-700">{{ $enrollment->student->full_name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-slate-500 capitalize">
                                    {{ $enrollment->student->gender }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <span class="px-2.5 py-1 bg-emerald-50 text-emerald-600 text-[10px] font-bold uppercase tracking-widest rounded-lg">Active</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-slate-500">
                                    No active students found in this section.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-teacher-layout>
