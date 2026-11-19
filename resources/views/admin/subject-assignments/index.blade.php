<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full relative z-10">
            <h2 class="text-2xl font-black text-slate-800 uppercase tracking-tight">Academic Distribution</h2>
            <a href="{{ route('admin.subject-assignments.bulk-assign', ['academic_year_id' => $academicYear?->id]) }}" class="vibrant-btn-blue">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Assign Subjects in Bulk
            </a>
        </div>
    </x-slot>

    <div class="space-y-8">
        <!-- Breadcrumb & Nav -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 relative z-30">
            <x-breadcrumb :items="[['label' => 'Subject Assignments', 'url' => '#']]" />
            <form method="GET" action="{{ route('admin.subject-assignments.index') }}" class="flex items-center gap-3">
                <label for="academic_year_id" class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Academic Year</label>
                <select id="academic_year_id" name="academic_year_id" onchange="this.form.submit()" class="rounded-xl border-slate-200 text-sm font-bold text-slate-700 focus:border-indigo-500 focus:ring-indigo-500">
                    @foreach($academicYears as $year)
                        <option value="{{ $year->id }}" @selected($academicYear && $academicYear->id === $year->id)>{{ $year->name }}{{ $year->is_active ? ' (active)' : '' }}</option>
                    @endforeach
                </select>
            </form>
        </div>

        <div class="glass-panel overflow-hidden border-white/40 shadow-2xl">
            <div class="p-8 border-b border-slate-50 bg-slate-50/30">
                <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest">Subject Assignments</h3>
                <p class="text-xs text-slate-500 font-medium mt-1 italic">Subjects taught in each grade in {{ $academicYear?->name ?? 'the selected year' }}. Changes apply to that year only.</p>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50 border-b border-slate-100">
                            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Grade Level</th>
                            <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Parent Division</th>
                            <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Number of Subjects</th>
                            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($gradeLevels as $grade)
                            <tr class="hover:bg-indigo-50/30 transition-all group">
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-xl bg-indigo-500/10 flex items-center justify-center text-indigo-600 font-black text-sm group-hover:bg-indigo-600 group-hover:text-white transition-all">
                                            {{ substr($grade->name, 0, 1) }}
                                        </div>
                                        <span class="font-bold text-slate-900 tracking-tight">{{ $grade->name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-6 font-semibold text-slate-600 text-sm">
                                    <span class="flex items-center gap-2">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-300"></span>
                                        {{ $grade->division->name }}
                                    </span>
                                </td>
                                <td class="px-6 py-6">
                                    @php($count = $subjectCounts[$grade->id] ?? 0)
                                    <span class="px-3 py-1 bg-indigo-500/10 text-indigo-600 rounded-full text-[10px] font-black uppercase tracking-widest border border-indigo-500/20">
                                        {{ $count }} Active Subjects
                                    </span>
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <a href="{{ route('admin.subject-assignments.edit', ['grade_level' => $grade, 'academic_year_id' => $academicYear?->id]) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 rounded-xl text-indigo-600 hover:bg-indigo-600 hover:text-white hover:border-indigo-600 font-black text-[10px] uppercase tracking-widest transition-all shadow-sm">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        Manage Subjects
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-admin-layout>
