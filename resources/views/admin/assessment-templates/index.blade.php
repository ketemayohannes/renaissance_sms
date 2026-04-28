<x-admin-layout>
    <x-slot name="header">Assessment Templates</x-slot>

    <div class="space-y-8">
        <!-- Breadcrumb & Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <x-breadcrumb :items="[
                    ['label' => 'Assessment Templates', 'url' => '#']
                ]" />
                <h1 class="text-4xl font-black text-slate-900 tracking-tight mt-2">Templates</h1>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.assessment-templates.reorder') }}" class="px-6 py-3 bg-white text-slate-700 font-black text-[10px] uppercase tracking-widest rounded-xl border border-slate-200 hover:bg-slate-50 transition-all shadow-sm flex items-center gap-2 group">
                    <svg class="w-4 h-4 text-slate-400 group-hover:text-indigo-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path></svg>
                    Reorder Templates
                </a>
                <a href="{{ route('admin.assessment-templates.create') }}" class="px-6 py-3 bg-slate-900 text-white font-black text-[10px] uppercase tracking-widest rounded-xl hover:bg-indigo-600 transition-all shadow-lg shadow-slate-200 flex items-center gap-2 group">
                    <svg class="w-4 h-4 group-hover:rotate-90 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Add New Template
                </a>
            </div>
        </div>

        <!-- Filter Panel -->
        <div class="bg-white/40 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-xl shadow-slate-200/50 p-8">
            <form action="{{ route('admin.assessment-templates.index') }}" method="GET" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6">
                    <!-- Search -->
                    <div class="space-y-2 lg:col-span-2">
                        <label for="search" class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Search Template</label>
                        <div class="relative">
                            <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Search by template name..." class="w-full bg-white/50 border-slate-200 rounded-2xl focus:ring-indigo-500 focus:border-indigo-500 font-bold text-sm py-3 pl-10 pr-4 transition-all">
                            <svg class="w-4 h-4 text-slate-400 absolute left-4 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                    </div>

                    <!-- Academic Year -->
                    <div class="space-y-2">
                        <label for="academic_year_id" class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Academic Year</label>
                        <select name="academic_year_id" id="academic_year_id" class="w-full bg-white/50 border-slate-200 rounded-2xl focus:ring-indigo-500 focus:border-indigo-500 font-bold text-sm py-3 px-4 transition-all">
                            <option value="">All Years</option>
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}" {{ request('academic_year_id') == $year->id ? 'selected' : '' }}>{{ $year->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Term -->
                    <div class="space-y-2">
                        <label for="term_id" class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Academic Term</label>
                        <select name="term_id" id="term_id" class="w-full bg-white/50 border-slate-200 rounded-2xl focus:ring-indigo-500 focus:border-indigo-500 font-bold text-sm py-3 px-4 transition-all">
                            <option value="">All Terms</option>
                            @foreach($terms as $term)
                                <option value="{{ $term->id }}" {{ request('term_id') == $term->id ? 'selected' : '' }}>{{ $term->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Grade Level -->
                    <div class="space-y-2">
                        <label for="grade_level_id" class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Grade Level</label>
                        <select name="grade_level_id" id="grade_level_id" class="w-full bg-white/50 border-slate-200 rounded-2xl focus:ring-indigo-500 focus:border-indigo-500 font-bold text-sm py-3 px-4 transition-all">
                            <option value="">All Grade Levels</option>
                            @foreach($gradeLevels as $grade)
                                <option value="{{ $grade->id }}" {{ request('grade_level_id') == $grade->id ? 'selected' : '' }}>{{ $grade->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Subject -->
                    <div class="space-y-2">
                        <label for="subject_id" class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Subject</label>
                        <select name="subject_id" id="subject_id" class="w-full bg-white/50 border-slate-200 rounded-2xl focus:ring-indigo-500 focus:border-indigo-500 font-bold text-sm py-3 px-4 transition-all">
                            <option value="">All Subjects</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Assessment Type -->
                    <div class="space-y-2">
                        <label for="assessment_type_id" class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Type</label>
                        <select name="assessment_type_id" id="assessment_type_id" class="w-full bg-white/50 border-slate-200 rounded-2xl focus:ring-indigo-500 focus:border-indigo-500 font-bold text-sm py-3 px-4 transition-all">
                            <option value="">All Types</option>
                            @foreach($assessmentTypes as $type)
                                <option value="{{ $type->id }}" {{ request('assessment_type_id') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-end gap-3 lg:col-span-1">
                        <button type="submit" class="flex-1 bg-slate-900 hover:bg-indigo-600 text-white font-black text-[10px] uppercase tracking-widest py-4 rounded-2xl shadow-lg shadow-indigo-200/50 transition-all flex items-center justify-center gap-2 group">
                            <svg class="w-4 h-4 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                            Filter
                        </button>
                        <a href="{{ route('admin.assessment-templates.index') }}" class="px-4 bg-white hover:bg-slate-50 text-slate-400 hover:text-rose-500 font-black text-[10px] uppercase tracking-widest py-4 rounded-2xl border border-slate-200 transition-all flex items-center justify-center gap-2 group" title="Clear Filters">
                            <svg class="w-4 h-4 group-hover:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                        </a>

                        @if(request('term_id') || request('academic_year_id'))
                        <form action="{{ route('admin.assessment-templates.destroy-by-term') }}" method="POST" class="inline delete-form" data-confirm-message="DANGER: Delete ALL templates matching this filter? This cannot be undone.">
                            @csrf
                            <input type="hidden" name="academic_year_id" value="{{ request('academic_year_id') ?: \App\Helpers\CachedData::activeAcademicYear()?->id }}">
                            <input type="hidden" name="term_id" value="{{ request('term_id') }}">
                            <button type="submit" class="px-4 bg-rose-50 hover:bg-rose-500 text-rose-500 hover:text-white font-black text-[10px] uppercase tracking-widest py-4 rounded-2xl border border-rose-100 hover:border-rose-500 transition-all flex items-center justify-center gap-2 group" title="Delete All Matching Templates">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        <!-- Table Panel -->
        <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-xl shadow-slate-200/50 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-separate border-spacing-0">
                    <thead>
                        <tr>
                            <th class="px-8 py-6 bg-slate-50/50 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Academic Year</th>
                            <th class="px-8 py-6 bg-slate-50/50 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Template Logic</th>
                            <th class="px-8 py-6 bg-slate-50/50 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Applied To</th>
                            <th class="px-8 py-6 bg-slate-50/50 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Applied Terms</th>
                            <th class="px-8 py-6 bg-slate-50/50 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 text-center">Weight</th>
                            <th class="px-8 py-6 bg-slate-50/50 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($groupedTemplates as $group)
                            <tr class="group hover:bg-slate-50/50 transition-all">
                                <td class="px-8 py-6 text-sm font-semibold text-slate-500 whitespace-nowrap">
                                    {{ $group['academic_year']->name }}
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-xl bg-orange-50 flex items-center justify-center text-orange-600 font-black text-xs shadow-sm border border-orange-100/50 group-hover:scale-110 transition-transform">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 01-2-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                        </div>
                                        <div>
                                            <span class="font-bold text-slate-700">{{ $group['name'] }}</span>
                                            <div class="flex items-center gap-2 mt-0.5">
                                                <span class="text-[9px] font-black text-indigo-400 uppercase tracking-widest">{{ $group['assessment_type']->name }}</span>
                                                <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                                <span class="text-[9px] font-bold text-slate-400 italic">{{ $group['assignment_count'] }} linked criteria</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="space-y-3">
                                        @if($group['grade_levels']->count() > 0)
                                        <div>
                                            <span class="text-[8px] font-black text-slate-300 uppercase tracking-widest block mb-1">Grade Levels</span>
                                            <div class="flex flex-wrap gap-1">
                                                @foreach($group['grade_levels'] as $grade)
                                                    <span class="px-2 py-0.5 bg-indigo-50 text-indigo-600 rounded-md text-[9px] font-bold border border-indigo-100/50">
                                                        {{ $grade->name }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                        @endif
                                        @if($group['subjects']->count() > 0)
                                        <div>
                                            <span class="text-[8px] font-black text-slate-300 uppercase tracking-widest block mb-1">Subjects</span>
                                            <div class="flex flex-wrap gap-1">
                                                @foreach($group['subjects'] as $subject)
                                                    <span class="px-2 py-0.5 bg-emerald-50 text-emerald-600 rounded-md text-[9px] font-bold border border-emerald-100/50">
                                                        {{ $subject->name }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach($group['terms'] as $term)
                                            <span class="px-2.5 py-1 bg-slate-100 rounded-lg text-[9px] font-black text-slate-500 uppercase tracking-widest border border-slate-200/50">
                                                {{ $term['name'] }}
                                            </span>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-8 py-6 text-center">
                                    <div class="inline-flex flex-col items-center">
                                        <span class="text-xl font-black text-slate-800 tracking-tighter">{{ $group['weight'] }}%</span>
                                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em]">Contribution</span>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex flex-col gap-4">
                                        <!-- Per Term Actions -->
                                        <div class="flex flex-col gap-2">
                                            <span class="text-[8px] font-black text-slate-300 uppercase tracking-widest block px-2">Manage by Term</span>
                                            @foreach($group['terms'] as $term)
                                                <div class="flex items-center justify-end gap-3 p-2 bg-slate-50/50 rounded-xl border border-transparent hover:border-slate-100 hover:bg-white transition-all group/item">
                                                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest group-hover/item:text-slate-600 transition-colors">{{ $term['name'] }}</span>
                                                    <div class="flex items-center gap-1">
                                                        <a href="{{ route('admin.assessment-templates.edit', $term['id']) }}" class="p-1.5 text-slate-300 hover:text-indigo-600 transition-colors" title="Edit {{ $term['name'] }}">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                                        </a>
                                                        <form action="{{ route('admin.assessment-templates.destroy', $term['id']) }}" method="POST" class="inline delete-form" data-confirm-message="Delete {{ $group['name'] }} for {{ $term['name'] }}?">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="p-1.5 text-slate-200 hover:text-rose-500 transition-colors" title="Delete {{ $term['name'] }}">
                                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>

                                        @if(count($group['terms']) > 1)
                                        <!-- Group Actions -->
                                        <div class="pt-3 border-t border-slate-50">
                                            <form action="{{ route('admin.assessment-templates.bulk-destroy') }}" method="POST" class="delete-form" data-confirm-message="DANGER: Delete this template from ALL {{ count($group['terms']) }} terms? This cannot be undone.">
                                                @csrf
                                                @foreach($group['ids'] as $id)
                                                    <input type="hidden" name="ids[]" value="{{ $id }}">
                                                @endforeach
                                                <button type="submit" class="w-full py-2 bg-rose-50 hover:bg-rose-500 text-rose-500 hover:text-white font-black text-[8px] uppercase tracking-widest rounded-lg border border-rose-100 hover:border-rose-500 transition-all flex items-center justify-center gap-2">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                    Delete All Terms
                                                </button>
                                            </form>
                                        </div>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-8 py-20 text-center">
                                    <div class="flex flex-col items-center">
                                        <div class="w-20 h-20 bg-slate-50 rounded-[2rem] flex items-center justify-center text-slate-200 mb-4">
                                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 01-2-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                        </div>
                                        <p class="text-slate-400 font-bold tracking-tight">No assessment templates found</p>
                                        <p class="text-slate-300 text-xs mt-1">Foundational data required for operation.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-admin-layout>
