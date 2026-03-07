<x-teacher-layout>
    <x-slot name="header">
        Department Oversight: {{ $department->name }}
    </x-slot>

    <div class="space-y-6">
        <!-- Department Selector (if multiple) -->
        @if($departments->count() > 1)
        <div class="flex items-center gap-3 overflow-x-auto pb-2 custom-scrollbar">
            @foreach($departments as $dept)
                <a href="{{ route('teacher.department.show', $dept->id) }}" 
                   class="px-4 py-2 rounded-xl text-sm font-bold transition-all whitespace-nowrap
                   {{ $department->id === $dept->id ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-200' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50' }}">
                    {{ $dept->name }}
                </a>
            @endforeach
        </div>
        @endif

        <!-- Key Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4">
                <div class="w-12 h-12 bg-emerald-50 rounded-xl flex items-center justify-center text-emerald-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-slate-500">Subjects</h3>
                    <p class="text-2xl font-bold text-slate-900 mt-1">{{ $department->subjects_count ?? $department->subjects()->count() }}</p>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4">
                <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center text-blue-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-slate-500">Total Entries</h3>
                    <p class="text-2xl font-bold text-slate-900 mt-1">{{ $analysis->sum('entries_count') }}</p>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4">
                <div class="w-12 h-12 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-slate-500">Avg Performance</h3>
                    <p class="text-2xl font-bold text-slate-900 mt-1">
                        {{ $analysis->count() > 0 ? number_format($analysis->avg('average_score'), 1) : '--' }}%
                    </p>
                </div>
            </div>
        </div>

        <!-- Result Analysis Table -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                <h2 class="text-lg font-bold text-slate-900 font-heading">Subject Performance Analysis</h2>
                <div class="flex gap-2">
                    <button class="p-2 text-slate-400 hover:text-indigo-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-50/50">
                        <tr>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest">Subject</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest">Average</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest">Distribution</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest">Range (Low-High)</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest text-right">Entries</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($analysis as $stat)
                        <tr class="hover:bg-slate-50/50 transition-colors group">
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900">{{ $stat->subject->name }}</div>
                                <div class="text-xs text-slate-500">{{ $stat->subject->code }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <div class="w-16 h-2 bg-slate-100 rounded-full overflow-hidden">
                                        <div class="h-full bg-indigo-500 rounded-full" style="width: {{ $stat->average_score }}%"></div>
                                    </div>
                                    <span class="text-sm font-bold text-slate-900">{{ number_format($stat->average_score, 1) }}%</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-1">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-50 text-rose-600" title="Below 50%">
                                        {{ $stat->below_50 }}
                                    </span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-600" title="50-75%">
                                        {{ $stat->bracket_50_75 }}
                                    </span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-600" title="75-90%">
                                        {{ $stat->bracket_75_90 }}
                                    </span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-600" title="Above 90%">
                                        {{ $stat->above_90 }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-xs font-medium text-slate-500">{{ number_format($stat->lowest_score, 1) }}% - {{ number_format($stat->highest_score, 1) }}%</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="text-sm font-medium text-slate-600">{{ $stat->entries_count }}</div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-500 italic">
                                No result data available for this department in the current academic year.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-teacher-layout>
