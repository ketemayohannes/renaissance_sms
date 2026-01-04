<x-admin-layout>
    <x-slot name="header">
        <h1 class="text-2xl font-bold text-slate-800 font-heading">Activities & Exams</h1>
    </x-slot>

    <div class="px-6 py-8">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 font-heading">Activities & Exams</h1>
                <p class="text-slate-500 mt-1">Manage homework, assignments, and online exams for your sections.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.activities.create') }}" 
                   class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl font-semibold transition-all shadow-lg shadow-indigo-100 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    New Activity
                </a>
            </div>
        </div>

        <!-- Filters placeholder -->
        <div class="bg-white/70 backdrop-blur-xl border border-slate-200 rounded-[2rem] p-6 mb-8 shadow-sm">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Filter logic here later -->
            </div>
        </div>

        <!-- Activity Cards/Table -->
        <div class="bg-white/70 backdrop-blur-xl border border-slate-200 rounded-[2.5rem] overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50 border-b border-slate-200/60">
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Activity Title</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Class & Subject</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Due Date</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Submissions</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($activities as $activity)
                        <tr class="hover:bg-slate-50/40 transition-colors group">
                            <td class="px-6 py-5">
                                @php
                                    $typeColors = [
                                        'homework' => 'bg-emerald-100 text-emerald-700',
                                        'assignment' => 'bg-indigo-100 text-indigo-700',
                                        'exam' => 'bg-rose-100 text-rose-700',
                                    ];
                                @endphp
                                <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $typeColors[$activity->type] ?? 'bg-slate-100 text-slate-700' }}">
                                    {{ $activity->type }}
                                </span>
                            </td>
                            <td class="px-6 py-5">
                                <div class="flex flex-col">
                                    <span class="font-bold text-slate-900 leading-tight">{{ $activity->title }}</span>
                                    @if($activity->assessmentTemplate)
                                    <span class="text-[11px] text-slate-400 mt-0.5 flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                        </svg>
                                        Linked to: {{ $activity->assessmentTemplate->name }}
                                    </span>
                                    @else
                                    <span class="text-[11px] text-slate-400 mt-0.5 italic">Non-graded / Supplemental</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-5">
                                <div class="flex flex-col">
                                    <span class="text-sm font-semibold text-slate-700">{{ $activity->teacherAssignment->section->name }}</span>
                                    <span class="text-xs text-slate-400">{{ $activity->teacherAssignment->subject->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-5">
                                <span class="text-sm font-medium text-slate-600">{{ $activity->due_date->format('M d, Y h:i A') }}</span>
                            </td>
                            <td class="px-6 py-5">
                                <div class="flex items-center gap-3">
                                    <span class="text-sm font-bold text-slate-900">{{ $activity->submissions_count ?? 0 }}</span>
                                    <div class="w-24 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                        <div class="bg-indigo-500 h-full rounded-full" style="width: {{ min(100, (($activity->submissions_count ?? 0) / 40) * 100) }}%"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-5 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.activities.show', $activity) }}" class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition-all">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </a>
                                    @if($activity->type === 'exam')
                                    <a href="{{ route('admin.activities.questions', $activity) }}" class="p-2 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-xl transition-all" title="Manage Questions">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </a>
                                    @endif
                                    <a href="{{ route('admin.activities.evaluate', $activity) }}" class="p-2 text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-xl transition-all" title="Grade Submissions">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-20 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center mb-4">
                                        <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>
                                    </div>
                                    <h3 class="font-bold text-slate-900">No activities found</h3>
                                    <p class="text-slate-500 mt-1 max-w-xs mx-auto text-sm">You haven't created any homework, assignments, or exams yet. Click 'New Activity' to get started.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($activities->hasPages())
            <div class="px-6 py-4 bg-slate-50/50 border-t border-slate-200/60">
                {{ $activities->links() }}
            </div>
            @endif
        </div>
    </div>
</x-admin-layout>
