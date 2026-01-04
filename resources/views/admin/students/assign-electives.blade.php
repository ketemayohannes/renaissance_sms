<x-admin-layout>
    <x-slot name="header">Assign Elective Subjects: {{ $student->full_name }}</x-slot>

    <div class="space-y-6">
        <x-breadcrumb :items="[
            ['label' => 'Students', 'url' => route('admin.students.index')],
            ['label' => $student->full_name, 'url' => route('admin.students.show', $student)],
            ['label' => 'Assign Electives', 'url' => '#']
        ]" />

        <!-- Profile Header Section (Decorative) -->
        <div class="relative mb-6">
            <div class="absolute inset-0 h-32 bg-gradient-to-r from-violet-600 to-indigo-600 rounded-[2.5rem] opacity-10 blur-2xl -z-10"></div>
            <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-xl shadow-slate-200/50 p-6 flex flex-col md:flex-row items-center gap-6">
                <div class="w-20 h-20 rounded-2xl bg-violet-50 border-2 border-white shadow-sm overflow-hidden text-violet-300 flex items-center justify-center">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                </div>
                <div class="text-center md:text-left">
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight">Assign Elective Subjects</h1>
                    <p class="text-slate-500 font-semibold text-sm">{{ $student->full_name }} • {{ $student->student_id }}</p>
                </div>
            </div>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            <!-- Left Column: Context -->
            <div class="lg:col-span-1">
                <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-xl shadow-slate-200/50 p-8 sticky top-6">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-10 h-10 rounded-xl bg-violet-50 flex items-center justify-center text-violet-600 shadow-sm border border-violet-100/50">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900">Current Scope</h3>
                    </div>

                    <div class="space-y-4">
                        <div class="p-4 bg-slate-50/50 rounded-2xl border border-slate-100">
                            <span class="text-[10px] text-slate-400 font-black uppercase tracking-widest block mb-1">Academic Year</span>
                            <span class="font-bold text-slate-700">{{ $currentEnrollment->academicYear->name }}</span>
                        </div>
                        <div class="p-4 bg-slate-50/50 rounded-2xl border border-slate-100">
                            <span class="text-[10px] text-slate-400 font-black uppercase tracking-widest block mb-1">Grade Level</span>
                            <span class="font-bold text-slate-700 text-sm">{{ $currentEnrollment->section->gradeLevel->name }}</span>
                        </div>
                    </div>

                    <div class="mt-8 pt-6 border-t border-slate-50">
                        <p class="text-xs text-slate-400 font-medium leading-relaxed italic">
                            Select the elective subjects the student will be taking this academic year.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Right Column: Elective Selection -->
            <div class="lg:col-span-3">
                <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-xl shadow-slate-200/50 p-8">
                    @if($availableElectives->isEmpty())
                        <div class="flex flex-col items-center justify-center py-20 text-slate-300">
                            <div class="w-20 h-20 rounded-[2rem] bg-slate-50 flex items-center justify-center mb-4 border border-slate-100 shadow-inner">
                                <svg class="w-10 h-10 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                            </div>
                            <p class="font-black text-sm uppercase tracking-widest">No Elective Subjects Available</p>
                            <p class="text-xs font-semibold mt-1">Please ensure subjects are marked as 'Elective' for this grade level.</p>
                            <a href="{{ route('admin.students.show', $student) }}" class="mt-6 px-6 py-2 bg-slate-100 text-slate-600 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-200 transition-all">Back to Profile</a>
                        </div>
                    @else
                        <form action="{{ route('admin.students.assign-electives.store', $student) }}" method="POST" class="space-y-8">
                            @csrf
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @foreach($availableElectives as $subject)
                                    <label for="subject_{{ $subject->id }}" class="relative group cursor-pointer">
                                        <input id="subject_{{ $subject->id }}" 
                                               name="electives[]" 
                                               value="{{ $subject->id }}" 
                                               type="checkbox" 
                                               class="peer sr-only"
                                               {{ in_array($subject->id, $assignedElectiveIds) ? 'checked' : '' }}>
                                        
                                        <div class="flex items-center gap-4 p-4 bg-slate-50/50 rounded-2xl border-2 border-slate-100/50 peer-checked:border-indigo-600 peer-checked:bg-indigo-50/50 transition-all group-hover:scale-[1.02] active:scale-[0.98]">
                                            <div class="w-12 h-12 rounded-xl bg-white border border-slate-100 flex items-center justify-center text-slate-400 group-hover:text-indigo-400 peer-checked:text-indigo-600 peer-checked:border-indigo-100 shadow-sm transition-colors">
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                                            </div>
                                            <div class="flex-grow">
                                                <h4 class="font-black text-slate-800 text-sm peer-checked:text-indigo-900 group-hover:text-indigo-600 transition-colors">{{ $subject->name }}</h4>
                                                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">{{ $subject->code }}</p>
                                            </div>
                                            <div class="opacity-0 peer-checked:opacity-100 transition-opacity">
                                                <div class="w-6 h-6 rounded-full bg-indigo-600 flex items-center justify-center text-white">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                </div>
                                            </div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>

                            <div class="flex items-center justify-end gap-3 pt-8 border-t border-slate-50">
                                <a href="{{ route('admin.students.show', $student) }}" class="px-8 py-4 bg-slate-100 text-slate-600 font-black text-[10px] uppercase tracking-widest rounded-2xl hover:bg-slate-200 transition-all">
                                    Cancel
                                </a>
                                <button type="submit" class="px-10 py-4 bg-indigo-600 text-white font-black text-[10px] uppercase tracking-widest rounded-2xl hover:bg-white hover:text-indigo-600 hover:ring-2 hover:ring-indigo-600 transition-all shadow-lg shadow-indigo-100 flex items-center gap-2 group">
                                    <svg class="w-4 h-4 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                                    Save Assignments
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
