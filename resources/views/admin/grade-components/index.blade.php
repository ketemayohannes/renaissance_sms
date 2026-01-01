<x-admin-layout>
    <x-slot name="header">Grade Components</x-slot>

    <div class="space-y-6">
        <div class="flex justify-between items-center bg-white p-4 rounded-lg shadow-sm border border-slate-200">
            <div>
                <h2 class="text-lg font-bold text-slate-800">Assessment Breakdown</h2>
                <p class="text-sm text-slate-500">Manage how subjects are graded across terms.</p>
            </div>
            <a href="{{ route('admin.grade-components.create') }}" class="btn-primary">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add New Component
            </a>
        </div>

        <x-breadcrumb :items="[
            ['label' => 'Grade Components', 'url' => '#']
        ]" />


            @if($weightTotals->isNotEmpty())
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800">Weight Validation Warnings</h3>
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

        @if($weightWarnings->isNotEmpty())
            <div class="bg-amber-50/50 backdrop-blur-xl border border-amber-100 rounded-[2rem] p-6 flex flex-col md:flex-row gap-6 items-start">
                <div class="w-12 h-12 rounded-2xl bg-amber-100 flex items-center justify-center text-amber-600 shrink-0 shadow-sm">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
                <div>
                    <h3 class="text-sm font-black text-amber-900 uppercase tracking-widest">Weight Selection Logic Integrity</h3>
                    <p class="text-xs text-amber-700/80 mt-1 font-semibold">The following subject mappings have component weights that do not total 100%. Master calculations may be inconsistent.</p>
                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach($weightWarnings as $warning)
                            <span class="px-3 py-1 bg-white/50 border border-amber-200/50 rounded-lg text-[9px] font-black {{ $warning['total'] > 100 ? 'text-rose-600' : 'text-amber-600' }} uppercase tracking-widest">
                                {{ $warning['subject'] }} ({{ $warning['section'] }}): {{ $warning['total'] }}%
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
                    <label for="section_id" class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Class Section</label>
                    <select name="section_id" id="section_id" class="w-full bg-white/50 border-slate-200 rounded-2xl focus:ring-indigo-500 focus:border-indigo-500 font-bold text-sm py-3 px-4 transition-all">
                        <option value="">All Sections</option>
                        @foreach($sections as $section)
                            <option value="{{ $section->id }}" {{ request('section_id') == $section->id ? 'selected' : '' }}>{{ $section->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="bg-white hover:bg-slate-50 text-slate-700 font-black text-[10px] uppercase tracking-widest py-4 rounded-2xl border border-slate-200 shadow-sm transition-all flex items-center justify-center gap-2 group">
                    <svg class="w-4 h-4 text-slate-400 group-hover:text-indigo-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                    Sync Gradebook
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
                            <th class="px-8 py-6 bg-slate-50/50 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Component Logic</th>
                            <th class="px-8 py-6 bg-slate-50/50 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Category</th>
                            <th class="px-8 py-6 bg-slate-50/50 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 text-center">Weight</th>
                            <th class="px-8 py-6 bg-slate-50/50 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($groupedComponents as $key => $group)
                            @php
                                $first = $group->first();
                            @endphp
                            <tr class="group hover:bg-slate-50/50 transition-all">
                                <td class="px-8 py-6">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-slate-700">{{ $first->section->name }}</span>
                                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-wider mt-0.5">{{ $first->subject->name }}</span>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex flex-col gap-2">
                                        @foreach($group as $component)
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600 font-black text-[10px] shadow-sm border border-indigo-100/50">
                                                    {{ substr($component->name, 0, 1) }}
                                                </div>
                                                <div class="flex flex-col">
                                                    <span class="text-sm font-semibold text-slate-700">{{ $component->name }}</span>
                                                    <span class="text-[9px] font-bold text-slate-400">{{ $component->assessmentType->name }}</span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex flex-col gap-2">
                                        @foreach($group as $component)
                                            <div class="h-8 flex items-center">
                                                <span class="px-2 py-0.5 bg-slate-100 rounded text-[9px] font-black text-slate-500 uppercase tracking-widest border border-slate-200/50">
                                                    {{ $component->assessmentType->name }}
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-8 py-6 text-center">
                                    <div class="flex flex-col gap-2 items-center">
                                        @foreach($group as $component)
                                            <div class="h-8 flex flex-col items-center justify-center">
                                                <span class="text-sm font-black text-slate-800 tracking-tighter">{{ $component->weight }}%</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <div class="flex flex-col gap-2 items-end">
                                        @foreach($group as $component)
                                            <div class="h-8 flex items-center gap-1">
                                                <a href="{{ route('admin.grade-components.edit', $component) }}" class="p-1.5 text-slate-300 hover:text-indigo-600 transition-colors">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                                </a>
                                                <form action="{{ route('admin.grade-components.destroy', $component) }}" method="POST" class="inline delete-form" data-confirm-message="Are you sure?">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="p-1.5 text-slate-200 hover:text-rose-500 transition-colors">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                    </button>
                                                </form>
                                            </div>
                                        @endforeach
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-8 py-20 text-center">
                                    <div class="flex flex-col items-center">
                                        <div class="w-20 h-20 bg-slate-50 rounded-[2rem] flex items-center justify-center text-slate-200 mb-4">
                                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        </div>
                                        <p class="text-slate-400 font-bold tracking-tight">No grade components configured</p>
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
