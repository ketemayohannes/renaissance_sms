<x-admin-layout>
    <x-slot name="header">
        <h1 class="text-2xl font-bold text-slate-800 font-heading">Activity Details</h1>
    </x-slot>

    <div class="px-6 py-8 text-primary">
    <!-- Header/Breadcrumb -->
    <div class="mb-8">
        <a href="{{ route('admin.activities.index') }}" class="inline-flex items-center gap-2 text-slate-500 hover:text-indigo-600 transition-colors mb-4">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Back to Activities
        </a>
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-indigo-100 text-indigo-700">
                        {{ $activity->type }}
                    </span>
                    <span class="text-slate-400 text-sm font-medium">{{ $activity->teacherAssignment->section->name }} &bull; {{ $activity->teacherAssignment->subject->name }}</span>
                </div>
                <h1 class="text-3xl font-bold text-slate-900 font-heading">{{ $activity->title }}</h1>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.activities.evaluate', $activity) }}" 
                   class="px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl font-bold transition-all shadow-lg shadow-emerald-100 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                    </svg>
                    Evaluate Submissions
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content (Submissions) -->
        <div class="lg:col-span-2 space-y-8">
            <div class="bg-white/70 backdrop-blur-xl border border-slate-200 rounded-[2.5rem] shadow-sm overflow-hidden text-primary">
                <div class="px-8 py-6 border-b border-slate-100 flex items-center justify-between bg-slate-50/30">
                    <h2 class="font-bold text-slate-800 flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                        Student Submissions
                    </h2>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">{{ $activity->submissions->count() }} Submissions</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-[10px] font-bold text-slate-400 uppercase tracking-widest bg-slate-50/20">
                                <th class="px-8 py-4">Student</th>
                                <th class="px-8 py-4">Status</th>
                                <th class="px-8 py-4 text-center">Score</th>
                                <th class="px-8 py-4 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($activity->submissions as $submission)
                            <tr class="hover:bg-slate-50/40 transition-colors">
                                <td class="px-8 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 font-bold text-xs">
                                            {{ substr($submission->student->full_name, 0, 1) }}
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-sm font-bold text-slate-900">{{ $submission->student->full_name }}</span>
                                            <span class="text-[10px] text-slate-400 font-medium uppercase tracking-tighter">{{ $submission->submitted_at ? $submission->submitted_at->format('M d, h:i A') : 'N/A' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-4">
                                    @php
                                        $statusStyles = [
                                            'pending' => 'bg-amber-100 text-amber-700',
                                            'submitted' => 'bg-indigo-100 text-indigo-700',
                                            'graded' => 'bg-emerald-100 text-emerald-700',
                                            'late' => 'bg-rose-100 text-rose-700',
                                        ];
                                    @endphp
                                    <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $statusStyles[$submission->status] ?? 'bg-slate-100 text-slate-700' }}">
                                        {{ $submission->status }}
                                    </span>
                                </td>
                                <td class="px-8 py-4 text-center">
                                    <span class="font-bold text-slate-900">{{ $submission->score ?? '-' }}</span>
                                    <span class="text-slate-400 text-[10px]">/ {{ $activity->max_score }}</span>
                                </td>
                                <td class="px-8 py-4 text-right text-primary">
                                    <button class="text-indigo-600 hover:text-indigo-800 font-bold text-sm">View Work</button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-8 py-12 text-center text-slate-400 italic text-sm">No submissions recorded yet.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Sidebar Content (Info & Attachments) -->
        <div class="space-y-8">
            <!-- Activity Info Card -->
            <div class="bg-white/70 backdrop-blur-xl border border-slate-200 rounded-[2.5rem] p-8 shadow-sm">
                <h3 class="text-sm font-bold text-slate-500 uppercase tracking-widest mb-6">Activity Details</h3>
                <div class="space-y-6">
                    <div class="flex items-start gap-4 p-4 bg-slate-50/50 rounded-2xl border border-slate-100 text-primary">
                        <div class="w-10 h-10 rounded-xl bg-rose-100 flex items-center justify-center text-rose-600 flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Deadline</p>
                            <p class="font-bold text-slate-900">{{ $activity->due_date->format('M d, Y') }}</p>
                            <p class="text-xs text-rose-500 font-medium">{{ $activity->due_date->format('h:i A') }} (Local Time)</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4 p-4 bg-slate-50/50 rounded-2xl border border-slate-100 text-primary">
                        <div class="w-10 h-10 rounded-xl bg-indigo-100 flex items-center justify-center text-indigo-600 flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Grading Sync</p>
                            <p class="font-bold text-indigo-900 text-sm">
                                {{ $activity->assessmentTemplate ? 'Synced to: ' . $activity->assessmentTemplate->name : 'Non-Synchronized' }}
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="mt-8">
                    <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3">Task Instructions</h4>
                    <div class="prose prose-sm text-slate-600 leading-relaxed">
                        {!! nl2br(e($activity->description)) !!}
                    </div>
                </div>
            </div>

            <!-- Attachments Card -->
            <div class="bg-white/70 backdrop-blur-xl border border-slate-200 rounded-[2.5rem] p-8 shadow-sm">
                <h3 class="text-sm font-bold text-slate-500 uppercase tracking-widest mb-6">Study Materials</h3>
                <div class="space-y-3">
                    @forelse($activity->attachments as $attachment)
                    <a href="#" class="flex items-center gap-3 p-3 bg-slate-50 hover:bg-white border border-slate-100 hover:border-indigo-200 rounded-2xl transition-all group">
                        <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center text-slate-400 group-hover:text-indigo-500 shadow-sm transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold text-slate-900 truncate">{{ $attachment->file_name }}</p>
                            <p class="text-[10px] text-slate-400 uppercase tracking-tighter">{{ strtoupper($attachment->file_type) }}</p>
                        </div>
                    </a>
                    @empty
                    <div class="p-6 bg-slate-50/50 rounded-2xl border border-dashed border-slate-200 text-center">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">No materials attached</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
</x-admin-layout>
