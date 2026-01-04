<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full relative z-10">
            <h2 class="text-2xl font-black text-slate-800 uppercase tracking-tight">Instructional Matrix</h2>
            <a href="{{ route('admin.teacher-assignments.create') }}" class="inline-flex items-center px-6 py-3 bg-slate-900 text-white text-xs font-black rounded-2xl hover:bg-slate-800 transition-all gap-3 shadow-xl uppercase tracking-widest">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
                Deploy Assignment
            </a>
        </div>
    </x-slot>

    <div class="space-y-8">
        <!-- Breadcrumb & Nav -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 relative z-30">
            <x-breadcrumb :items="[['label' => 'Teacher Assignments', 'url' => '#']]" />
            <div class="flex items-center gap-3">
                 <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest bg-slate-100 px-3 py-1 rounded-lg border border-slate-200">Active Year: {{ $activeYear->name ?? 'N/A' }}</span>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <!-- Summary Stats -->
            <div class="lg:col-span-3 space-y-6">
                <div class="bg-indigo-600 rounded-[2.5rem] p-8 text-white shadow-xl shadow-indigo-100">
                    <p class="text-[10px] font-black uppercase tracking-[0.2em] opacity-60 mb-2">Faculty Deployment</p>
                    <h3 class="text-4xl font-black">{{ $teachers->count() }}</h3>
                    <p class="text-xs font-bold mt-2 opacity-80 leading-relaxed italic">Qualified instructional personnel active in system.</p>
                </div>
                
                <div class="bg-white rounded-[2.5rem] p-8 border border-slate-100 shadow-xl shadow-slate-200/50">
                    <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-6">Matrix Health</h4>
                    <div class="space-y-6">
                        @php
                            $totalAssignments = \App\Models\TeacherAssignment::where('academic_year_id', $activeYear->id ?? 0)->count();
                            $unassignedTeachers = $teachers->filter(fn($t) => !isset($assignments[$t->user_id]))->count();
                        @endphp
                        <div>
                            <div class="flex justify-between items-end mb-2">
                                <span class="text-[10px] font-bold text-slate-500 uppercase">Coverage</span>
                                <span class="text-sm font-black text-slate-800">{{ $teachers->count() > 0 ? round((($teachers->count() - $unassignedTeachers) / $teachers->count()) * 100) : 0 }}%</span>
                            </div>
                            <div class="w-full h-2 bg-slate-50 rounded-full overflow-hidden">
                                <div class="h-full bg-emerald-500 rounded-full" style="width: {{ $teachers->count() > 0 ? (($teachers->count() - $unassignedTeachers) / $teachers->count()) * 100 : 0 }}%"></div>
                            </div>
                        </div>
                        <div class="flex items-center justify-between py-4 border-t border-slate-50 text-[10px] uppercase font-black tracking-widest text-slate-400 italic">
                            <span>Total Links</span>
                            <span class="text-slate-900">{{ $totalAssignments }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- List -->
            <div class="lg:col-span-9">
                <div class="glass-panel border-white shadow-2xl overflow-hidden rounded-[3rem]">
                    <div class="p-8 border-b border-slate-50 flex items-center justify-between bg-slate-50/30">
                        <div>
                            <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest">Faculty Assignment Registry</h3>
                            <p class="text-xs text-slate-500 font-medium mt-1">Cross-referencing teachers with academic sections and subjects.</p>
                        </div>
                    </div>

                    <div class="divide-y divide-slate-50">
                        @forelse($teachers as $teacher)
                            <div class="p-8 hover:bg-slate-50/50 transition-all flex flex-col md:flex-row md:items-center justify-between gap-8">
                                <div class="flex items-center gap-5 w-full md:w-1/3">
                                    <div class="w-16 h-16 rounded-[1.5rem] bg-slate-900 overflow-hidden shadow-lg border-4 border-white flex-shrink-0">
                                        @if($teacher->photo)
                                            <img src="{{ Storage::url($teacher->photo) }}" class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-white font-black text-xl bg-slate-800">
                                                {{ substr($teacher->first_name, 0, 1) }}
                                            </div>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <h4 class="text-lg font-black text-slate-800 tracking-tight truncate">{{ $teacher->full_name }}</h4>
                                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ $teacher->employee_id }} | {{ $teacher->designation }}</p>
                                    </div>
                                </div>

                                <div class="flex-1 flex flex-wrap gap-2">
                                    @if(isset($assignments[$teacher->user_id]))
                                        @foreach($assignments[$teacher->user_id] as $assignment)
                                            <div class="group relative flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 rounded-xl shadow-sm hover:border-indigo-400 transition-all">
                                                <div class="flex flex-col">
                                                    <span class="text-[9px] font-black text-slate-800 uppercase tracking-tight">{{ $assignment->subject->name }}</span>
                                                    <span class="text-[8px] font-bold text-indigo-500 uppercase">{{ $assignment->section->gradeLevel->name }} - {{ $assignment->section->name }}</span>
                                                </div>
                                                <form action="{{ route('admin.teacher-assignments.destroy', $assignment) }}" method="POST" onsubmit="return confirm('Remove this assignment?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="p-1 text-slate-300 hover:text-rose-500 transition-colors" title="Remove Assignment">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                    </button>
                                                </form>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="px-4 py-2 bg-slate-50 rounded-xl border border-dashed border-slate-200 flex items-center gap-2">
                                            <div class="w-1.5 h-1.5 rounded-full bg-slate-300 animate-pulse"></div>
                                            <span class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Awaiting Deployment</span>
                                        </div>
                                    @endif
                                </div>

                                <div class="flex-shrink-0">
                                    <a href="{{ route('admin.employees.edit', $teacher) }}" class="p-3 bg-white border-2 border-slate-100 rounded-2xl text-slate-400 hover:text-indigo-600 hover:border-indigo-200 transition-all flex items-center justify-center">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="p-20 text-center">
                                <p class="text-sm font-black text-slate-400 uppercase tracking-[0.2em] italic">No Faculty Operational in System</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
