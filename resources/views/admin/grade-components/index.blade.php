<x-admin-layout>
    <x-slot name="header">Grade Components</x-slot>

    <div class="space-y-6">
        <div class="flex justify-between items-center bg-white/40 backdrop-blur-xl border border-white shadow-xl shadow-slate-200/50 p-8 rounded-[2.5rem]">
            <div>
                <x-breadcrumb :items="[
                    ['label' => 'Grade Components', 'url' => '#']
                ]" />
                <h1 class="text-4xl font-black text-slate-900 tracking-tight mt-2">Grade Components</h1>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.grade-components.create') }}" class="px-6 py-3 bg-slate-900 text-white font-black text-[10px] uppercase tracking-widest rounded-xl hover:bg-indigo-600 transition-all shadow-lg shadow-slate-200 flex items-center gap-2 group">
                    <svg class="w-4 h-4 group-hover:rotate-90 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Add Component
                </a>
            </div>
        </div>

        @if(isset($weightTotals) && $weightTotals->isNotEmpty())
            <div class="bg-amber-50/50 backdrop-blur-xl border border-amber-100 rounded-[2rem] p-6 flex flex-col md:flex-row gap-6 items-start">
                <div class="w-12 h-12 rounded-2xl bg-amber-100 flex items-center justify-center text-amber-600 shrink-0 shadow-sm">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
                <div>
                    <h3 class="text-sm font-black text-amber-900 uppercase tracking-widest">Weight Selection Logic Integrity</h3>
                    <p class="text-xs text-amber-700/80 mt-1 font-semibold">The following subject mappings have component weights that do not total 100%. Master calculations may be inconsistent.</p>
                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach($weightTotals as $warning)
                            <span class="px-3 py-1 bg-white/50 border border-amber-200/50 rounded-lg text-[9px] font-black {{ $warning['total'] > 100 ? 'text-rose-600' : 'text-amber-600' }} uppercase tracking-widest">
                                {{ $warning['subject'] }} ({{ $warning['grade_level'] }}): {{ $warning['total'] }}%
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- Filter Panel -->
        <div class="bg-white/40 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-xl shadow-slate-200/50 p-8">
            <form action="{{ route('admin.grade-components.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-6 items-end">
                <div class="space-y-2">
                    <label for="academic_year_id" class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Academic Year</label>
                    <select name="academic_year_id" id="academic_year_id" class="w-full bg-white/50 border-slate-200 rounded-2xl focus:ring-indigo-500 focus:border-indigo-500 font-bold text-sm py-3 px-4 transition-all">
                        <option value="">All Years</option>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" {{ request('academic_year_id') == $year->id ? 'selected' : '' }}>{{ $year->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-2">
                    <label for="term_id" class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Academic Term</label>
                    <select name="term_id" id="term_id" class="w-full bg-white/50 border-slate-200 rounded-2xl focus:ring-indigo-500 focus:border-indigo-500 font-bold text-sm py-3 px-4 transition-all">
                        <option value="">All Terms</option>
                        @foreach($terms as $term)
                            <option value="{{ $term->id }}" {{ request('term_id') == $term->id ? 'selected' : '' }}>{{ $term->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-2">
                    <label for="grade_level_id" class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Grade Level</label>
                    <select name="grade_level_id" id="grade_level_id" class="w-full bg-white/50 border-slate-200 rounded-2xl focus:ring-indigo-500 focus:border-indigo-500 font-bold text-sm py-3 px-4 transition-all">
                        <option value="">All Grades</option>
                        @foreach($gradeLevels as $gl)
                            <option value="{{ $gl->id }}" {{ request('grade_level_id') == $gl->id ? 'selected' : '' }}>{{ $gl->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="bg-white hover:bg-slate-50 text-slate-700 font-black text-[10px] uppercase tracking-widest py-4 rounded-2xl border border-slate-200 shadow-sm transition-all flex items-center justify-center gap-2 group">
                    <svg class="w-4 h-4 text-slate-400 group-hover:text-indigo-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                    Filter Components
                </button>
            </form>
        </div>

        <!-- Table Panel -->
        <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-xl shadow-slate-200/50 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-separate border-spacing-0">
                    <thead>
                        <tr>
                            <th class="px-8 py-6 bg-slate-50/50 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Scope</th>
                            <th class="px-8 py-6 bg-slate-50/50 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Component</th>
                            <th class="px-8 py-6 bg-slate-50/50 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Assessment Type</th>
                            <th class="px-8 py-6 bg-slate-50/50 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 text-center">Weight</th>
                            <th class="px-8 py-6 bg-slate-50/50 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($groupedComponents as $group)
                            <tr class="group hover:bg-slate-50/50 transition-all">
                                <td class="px-8 py-6">
                                    <div class="flex flex-col">
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($group['grade_levels'] as $gl)
                                                <span class="px-2 py-0.5 bg-indigo-50 text-indigo-700 rounded text-[9px] font-black uppercase">{{ $gl->name }}</span>
                                            @endforeach
                                        </div>
                                        <div class="mt-2 flex flex-wrap gap-1">
                                            @foreach($group['subjects'] as $sub)
                                                <span class="text-[10px] font-bold text-slate-500">{{ $sub->name }}</span>{{ !$loop->last ? ',' : '' }}
                                            @endforeach
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 font-black text-sm">
                                            {{ substr($group['name'], 0, 1) }}
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-sm font-black text-slate-800 tracking-tight">{{ $group['name'] }}</span>
                                            <span class="text-[10px] font-bold text-slate-400">{{ $group['academic_year']->name }} {{ $group['term']->name ?? 'All Terms' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <span class="px-3 py-1 bg-slate-100 rounded-lg text-[9px] font-black text-slate-500 uppercase tracking-widest">
                                        {{ $group['assessment_type']->name }}
                                    </span>
                                </td>
                                <td class="px-8 py-6 text-center">
                                    <span class="text-lg font-black text-slate-900 tracking-tighter">{{ $group['weight'] }}%</span>
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.grade-components.edit', $group['id']) }}" class="p-2 bg-slate-50 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition-all">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </a>
                                        <form action="{{ route('admin.grade-components.destroy', $group['id']) }}" method="POST" class="inline delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 bg-slate-50 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-all">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-8 py-20 text-center">
                                    <div class="flex flex-col items-center">
                                        <div class="w-20 h-20 bg-slate-50 rounded-[2.5rem] flex items-center justify-center text-slate-200 mb-6">
                                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        </div>
                                        <h3 class="text-lg font-black text-slate-800 tracking-tight">No Grade Components Found</h3>
                                        <p class="text-sm text-slate-400 mt-1 font-medium italic">No assessment logic has been mapped for the selected criteria.</p>
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
