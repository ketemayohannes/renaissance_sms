<x-teacher-layout>
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 font-heading">My Classes</h1>
                <p class="text-slate-500 mt-1">Manage your assigned sections and subjects.</p>
            </div>
            <div class="flex gap-3">
                <button class="px-4 py-2 bg-white border border-slate-200 rounded-xl text-slate-600 font-medium hover:bg-slate-50 transition-colors shadow-sm">
                    Export List
                </button>
            </div>
        </div>

        @if($assignments->isEmpty())
            <div class="bg-white rounded-2xl border border-dashed border-slate-300 p-12 text-center">
                <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-400">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-slate-900">No classes assigned yet</h3>
                <p class="text-slate-500 mt-1 max-w-sm mx-auto">You haven't been assigned to any classes for the current academic year. Please contact the registrar if this is an error.</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($assignments as $assignment)
                    <div class="group bg-white rounded-2xl shadow-sm border border-slate-200 hover:border-indigo-300 hover:shadow-md transition-all duration-300 flex flex-col overflow-hidden">
                        <!-- Card Header -->
                        <div class="p-6 pb-0 flex items-start justify-between">
                            <div class="w-12 h-12 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600 group-hover:scale-110 transition-transform">
                                <span class="font-bold text-lg">{{ substr($assignment->subject->name, 0, 1) }}</span>
                            </div>
                            <div class="flex flex-col items-end">
                                <span class="px-2.5 py-1 text-xs font-bold bg-slate-100 text-slate-600 rounded-lg">
                                    {{ $assignment->section->gradeLevel->name }}
                                </span>
                                <span class="mt-2 text-sm font-bold text-indigo-600">Section {{ $assignment->section->name }}</span>
                            </div>
                        </div>

                        <!-- Card Body -->
                        <div class="p-6 pt-4 flex-grow">
                            <h3 class="text-xl font-bold text-slate-900 group-hover:text-indigo-600 transition-colors">
                                {{ $assignment->subject->name }}
                            </h3>
                            <div class="mt-4 space-y-2">
                                <div class="flex items-center text-sm text-slate-500 gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                    </svg>
                                    <span>{{ $assignment->section->enrollments_count ?? '0' }} Students enrolled</span>
                                </div>
                                <div class="flex items-center text-sm text-slate-500 gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                                    </svg>
                                    <span>{{ $assignment->grading_status }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Card Footer -->
                        <div class="p-4 bg-slate-50 border-t border-slate-100 grid grid-cols-2 gap-3 mt-auto">
                            <a href="{{ route('teacher.classes.show', $assignment->id) }}" class="px-3 py-2 text-center text-xs font-bold text-indigo-600 bg-white border border-indigo-100 rounded-lg hover:bg-indigo-600 hover:text-white transition-all shadow-sm">
                                View Roster
                            </a>
                            <a href="{{ route('teacher.gradebook.entry', $assignment->id) }}" class="px-3 py-2 text-center text-xs font-bold text-emerald-600 bg-white border border-emerald-100 rounded-lg hover:bg-emerald-600 hover:text-white transition-all shadow-sm">
                                Enter Grades
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-teacher-layout>
