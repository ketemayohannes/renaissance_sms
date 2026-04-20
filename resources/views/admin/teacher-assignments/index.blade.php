<x-admin-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="font-heading font-bold text-2xl text-slate-900">Teacher Assignments</h2>
                <p class="text-slate-500 text-sm mt-1">Manage class and subject assignments for teaching staff.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.teacher-assignments.create') }}" 
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl transition-all duration-200 shadow-lg shadow-indigo-100 gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path>
                    </svg>
                    New Assignment
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <x-breadcrumb :items="[['label' => 'Teacher Assignments', 'url' => '#']]" />

        <div class="bg-white/80 backdrop-blur-xl rounded-[2rem] border border-white shadow-xl shadow-slate-200/50 overflow-hidden">
            <div class="p-6">
                <!-- Filter Section -->
                <div class="mb-8 p-6 bg-slate-50/50 rounded-3xl border border-slate-100">
                    <form action="{{ route('admin.teacher-assignments.index') }}" method="GET">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div class="md:col-span-3">
                                <label for="search" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Search Teacher</label>
                                <div class="relative">
                                    <input type="text" name="search" id="search" value="{{ request('search') }}" 
                                           placeholder="Search by Name or Employee ID..." 
                                           class="w-full pl-10 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all">
                                    <svg class="w-5 h-5 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex items-end">
                                <button type="submit" class="w-full py-2.5 bg-slate-900 text-white font-bold text-sm rounded-xl hover:bg-slate-800 transition-all active:scale-95">
                                    Apply Filter
                                </button>
                                @if(request('search'))
                                    <a href="{{ route('admin.teacher-assignments.index') }}" class="ml-2 p-2.5 bg-rose-50 text-rose-600 rounded-xl hover:bg-rose-100 transition-all">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Registry Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100">
                                <th class="px-6 py-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider w-1/4">Teacher</th>
                                <th class="px-6 py-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider w-1/2">Current Assignments</th>
                                <th class="px-6 py-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100/50">
                            @forelse($teachers as $teacher)
                                <tr class="hover:bg-indigo-50/20 transition-all duration-300 group/row">
                                    <td class="px-6 py-6 border-b border-slate-50/50">
                                        <div class="flex items-center gap-4">
                                            <div class="w-12 h-12 rounded-2xl overflow-hidden bg-slate-100 shadow-sm transition-transform group-hover/row:scale-105 border-2 border-white">
                                                @if($teacher->photo)
                                                    <img src="{{ Storage::url($teacher->photo) }}" class="w-full h-full object-cover">
                                                @else
                                                    <div class="w-full h-full flex items-center justify-center bg-slate-900 text-white font-bold text-lg">
                                                        {{ substr($teacher->first_name, 0, 1) }}
                                                    </div>
                                                @endif
                                            </div>
                                            <div>
                                                <p class="text-sm font-bold text-slate-900 group-hover/row:text-indigo-600 transition-colors">{{ $teacher->full_name }}</p>
                                                <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-tight">{{ $teacher->employee_id }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-6 border-b border-slate-50/50">
                                        <div class="flex flex-wrap gap-2">
                                            @if(isset($assignments[$teacher->user_id]))
                                                @foreach($assignments[$teacher->user_id] as $assignment)
                                                    <div class="group/badge relative flex items-center gap-2 px-3 py-1.5 bg-white border border-slate-200 rounded-xl shadow-sm hover:border-indigo-400 transition-all">
                                                        <div class="flex flex-col">
                                                            <span class="text-[9px] font-black text-slate-800 uppercase tracking-tight leading-none">{{ $assignment->subject->name }}</span>
                                                            <span class="text-[8px] font-bold text-indigo-500 uppercase mt-0.5">{{ $assignment->section->gradeLevel->name }} - {{ $assignment->section->name }}</span>
                                                        </div>
                                                        <form action="{{ route('admin.teacher-assignments.destroy', $assignment) }}" method="POST" class="delete-form" data-confirm-message="Remove this assignment?">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="p-1 text-slate-200 hover:text-rose-500 transition-colors" title="Remove Assignment">
                                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                            </button>
                                                        </form>
                                                    </div>
                                                @endforeach
                                            @else
                                                <span class="text-[9px] font-bold text-slate-300 uppercase italic tracking-widest">No active assignments</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-6 text-right border-b border-slate-50/50">
                                        <div class="flex justify-end gap-2">
                                            <a href="{{ route('admin.teacher-assignments.create', ['teacher_user_id' => $teacher->user_id]) }}" 
                                               class="p-2.5 text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-xl transition-all shadow-sm"
                                               title="Edit Assignments">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            </a>
                                            <a href="{{ route('admin.employees.edit', $teacher) }}" 
                                               class="p-2.5 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition-all shadow-sm"
                                               title="View Staff Profile">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-24 text-center">
                                        <div class="flex flex-col items-center">
                                             <div class="w-20 h-20 bg-slate-50 rounded-3xl flex items-center justify-center mb-6">
                                                 <svg class="w-10 h-10 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                             </div>
                                             <h4 class="text-xl font-bold text-slate-900 text-[11px] uppercase tracking-wider">No Teachers Found</h4>
                                             <p class="text-slate-400 text-sm mt-1">Try adjusting your search criteria.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($teachers->hasPages())
                    <div class="px-6 py-6 border-t border-slate-100 flex justify-between items-center bg-slate-50/30">
                        <div class="text-xs font-bold text-slate-400 uppercase tracking-widest leading-none">
                            Showing {{ $teachers->firstItem() }}-{{ $teachers->lastItem() }} of {{ $teachers->total() }}
                        </div>
                        <div class="teacher-pagination">
                            {{ $teachers->links() }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-admin-layout>
