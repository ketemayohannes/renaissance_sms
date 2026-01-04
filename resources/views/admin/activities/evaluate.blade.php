<x-admin-layout>
    <x-slot name="header">
        <h1 class="text-2xl font-bold text-slate-800 font-heading">Evaluate Activity</h1>
    </x-slot>

    <div class="px-6 py-8">
        <!-- Header -->
        <div class="mb-8">
            <a href="{{ route('admin.activities.show', $activity) }}" class="inline-flex items-center gap-2 text-slate-500 hover:text-indigo-600 transition-colors mb-4">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Back to Activity
        </a>
        <h1 class="text-3xl font-bold text-slate-900 font-heading">Evaluate: {{ $activity->title }}</h1>
        <p class="text-slate-500 mt-1">Bulk entry for {{ $activity->teacherAssignment->section->name }} &bull; {{ $activity->teacherAssignment->subject->name }}</p>
    </div>

    <!-- Instructions Alert -->
    <div class="mb-8 p-6 bg-indigo-50 border border-indigo-100 rounded-[2rem] flex items-start gap-4">
        <div class="w-10 h-10 rounded-2xl bg-indigo-100 flex items-center justify-center text-indigo-600 flex-shrink-0">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>
        <div>
            <h4 class="font-bold text-indigo-900">Grading Configuration</h4>
            <p class="text-sm text-indigo-700 mt-1">
                Total Points: <span class="font-bold text-indigo-900">{{ $activity->max_score }}</span>.
                @if($activity->assessmentTemplate)
                Scores will be automatically scaled and synced to the <span class="font-bold text-indigo-900">{{ $activity->assessmentTemplate->name }}</span> (Official Max: {{ $activity->assessmentTemplate->max_score }}).
                @else
                This activity is not linked to an official assessment; grades will be stored for feedback only.
                @endif
            </p>
        </div>
    </div>

    <form action="{{ route('admin.activities.evaluate.store', $activity) }}" method="POST">
        @csrf
        
        <div class="bg-white/70 backdrop-blur-xl border border-slate-200 rounded-[2.5rem] overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50 border-b border-slate-200/60">
                            <th class="px-8 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Student Name</th>
                            <th class="px-8 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Submission Date</th>
                            <th class="px-8 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider w-32">Score</th>
                            <th class="px-8 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Feedback / Remarks</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @php
                            // Get all enrolled students for this section to ensure zero-marking is possible
                            $students = $activity->teacherAssignment->section->students;
                            $submissions = $activity->submissions->keyBy('student_id');
                        @endphp
                        
                        @foreach($students as $index => $student)
                        @php
                            $submission = $submissions->get($student->id);
                        @endphp
                        <tr class="hover:bg-slate-50/30 transition-colors">
                            <td class="px-8 py-5">
                                <div class="flex items-center gap-3">
                                    <span class="text-xs font-bold text-slate-300">{{ $index + 1 }}</span>
                                    <span class="font-bold text-slate-900">{{ $student->full_name }}</span>
                                    <input type="hidden" name="submissions[{{ $index }}][student_id]" value="{{ $student->id }}">
                                </div>
                            </td>
                            <td class="px-8 py-5">
                                @if($submission && $submission->submitted_at)
                                <span class="text-xs font-bold text-indigo-600 bg-indigo-50 px-3 py-1 rounded-full uppercase tracking-tighter">
                                    {{ $submission->submitted_at->format('M d, H:i') }}
                                </span>
                                @else
                                <span class="text-xs font-bold text-rose-400 uppercase tracking-tighter italic">No Submission</span>
                                @endif
                            </td>
                            <td class="px-8 py-5">
                                <div class="relative">
                                    <input type="number" name="submissions[{{ $index }}][score]" 
                                           step="0.1" max="{{ $activity->max_score }}" min="0"
                                           value="{{ old('submissions.'.$index.'.score', $submission ? $submission->score : '') }}"
                                           placeholder="--"
                                           class="w-full h-12 bg-slate-50 border-0 rounded-xl px-4 font-bold text-slate-900 focus:ring-2 focus:ring-indigo-500 transition-all text-center">
                                </div>
                            </td>
                            <td class="px-8 py-5">
                                <input type="text" name="submissions[{{ $index }}][feedback]" 
                                       value="{{ old('submissions.'.$index.'.feedback', $submission ? $submission->feedback : '') }}"
                                       placeholder="Well done, keep it up!"
                                       class="w-full h-12 bg-slate-50 border-0 rounded-xl px-4 text-sm font-medium text-slate-600 focus:ring-2 focus:ring-emerald-500 transition-all">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Footer Actions -->
            <div class="px-8 py-6 bg-slate-50/50 border-t border-slate-200/60 flex items-center justify-between">
                <p class="text-xs text-slate-400 font-medium italic">* Only students with scores will be synchronized to the gradebook.</p>
                <div class="flex items-center gap-3">
                    <button type="button" onclick="history.back()" class="px-6 py-3 bg-white border border-slate-200 text-slate-600 rounded-2xl font-bold hover:bg-slate-50 transition-all">
                        Cancel
                    </button>
                    <button type="submit" class="px-8 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl font-bold shadow-lg shadow-indigo-100 transition-all transform hover:-translate-y-1 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                        </svg>
                        Save & Sync Marks
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
</x-admin-layout>
