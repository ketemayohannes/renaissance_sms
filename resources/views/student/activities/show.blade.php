<x-student-layout header="Activity Details">

    <div class="px-6 py-8">
    <!-- Header -->
    <div class="mb-8">
        <a href="{{ route('student.activities.index') }}" class="inline-flex items-center gap-2 text-slate-500 hover:text-indigo-600 transition-colors mb-4">
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
                    <span class="text-slate-400 text-sm font-medium">{{ $activity->teacherAssignment->subject->name }} &bull; Assigned by {{ $activity->creator->name }}</span>
                </div>
                <h1 class="text-3xl font-bold text-slate-900 font-heading">{{ $activity->title }}</h1>
            </div>
            @if($activity->type === 'exam' && !$submission)
            <a href="{{ route('student.activities.exam', $activity) }}" 
               class="px-8 py-3 bg-rose-600 hover:bg-rose-700 text-white rounded-2xl font-bold transition-all shadow-lg shadow-rose-100 flex items-center gap-2 animate-pulse">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                </svg>
                Enter Examination
            </a>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 text-primary">
        <!-- Left: Instructions & Feedback -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Description Card -->
            <div class="bg-white/70 backdrop-blur-xl border border-slate-200 rounded-[2.5rem] p-8 shadow-sm">
                <h3 class="text-sm font-bold text-slate-400 uppercase tracking-widest mb-6">Task Instructions</h3>
                <div class="prose prose-indigo max-w-none text-slate-600 leading-relaxed font-medium">
                    {!! nl2br(e($activity->description)) !!}
                </div>
                
                @if($activity->attachments->count() > 0)
                <div class="mt-8 pt-8 border-t border-slate-100">
                    <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-4">Downloadable Assets</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($activity->attachments as $attachment)
                        <a href="#" class="flex items-center gap-3 p-4 bg-slate-50 border border-slate-100 rounded-2xl hover:border-indigo-300 transition-all group">
                            <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center text-slate-400 group-hover:text-indigo-500 shadow-sm transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-bold text-slate-700 truncate">{{ $attachment->file_name }}</p>
                                <p class="text-[9px] text-slate-400 uppercase tracking-tighter">{{ strtoupper($attachment->file_type) }}</p>
                            </div>
                        </a>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            <!-- Feedback Card (If graded) -->
            @if($submission && $submission->status === 'graded')
            <div class="bg-indigo-600 rounded-[2.5rem] p-1 shadow-xl shadow-indigo-100 overflow-hidden">
                <div class="bg-indigo-50/90 backdrop-blur rounded-[calc(2.5rem-4px)] p-8">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-12 h-12 rounded-2xl bg-indigo-600 flex items-center justify-center text-white font-bold text-xl shadow-lg">
                            {{ $submission->score }}
                        </div>
                        <div>
                            <h3 class="font-bold text-indigo-900">Teacher's Evaluation</h3>
                            <p class="text-[10px] text-indigo-400 font-bold uppercase tracking-widest">Score: {{ $submission->score }} / {{ $activity->max_score }}</p>
                        </div>
                    </div>
                    @if($submission->feedback)
                    <div class="bg-white/50 border border-indigo-100 rounded-2xl p-4 italic text-indigo-800 text-sm font-medium leading-relaxed">
                        "{{ $submission->feedback }}"
                    </div>
                    @else
                    <p class="text-sm text-indigo-400 italic font-medium">No written feedback provided.</p>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <!-- Right: Submission Form -->
        <div class="space-y-8">
            <div class="bg-white/70 backdrop-blur-xl border border-slate-200 rounded-[2.5rem] p-8 shadow-sm">
                @if($submission)
                <div class="text-center py-6">
                    <div class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900">Submission Received</h3>
                    <p class="text-slate-400 text-xs mt-1 uppercase font-bold tracking-widest">Sent on: {{ $submission->submitted_at->format('M d, Y') }}</p>
                    
                    @if($submission->status === 'submitted')
                    <div class="mt-6 p-4 bg-indigo-50 rounded-2xl border border-indigo-100">
                        <p class="text-[10px] font-bold text-indigo-600 uppercase tracking-widest">Status: Under Review</p>
                    </div>
                    @endif
                </div>
                @elseif($activity->type !== 'exam')
                <h3 class="text-sm font-bold text-slate-500 uppercase tracking-widest mb-6">Submit Your Work</h3>
                <form action="{{ route('student.activities.submit', $activity) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="space-y-6">
                        <div class="relative group h-40 border-2 border-dashed border-slate-200 rounded-[2rem] hover:border-indigo-400 transition-all flex flex-col items-center justify-center bg-slate-50/50">
                            <input type="file" name="attachments[]" multiple required class="absolute inset-0 opacity-0 cursor-pointer">
                            <svg class="w-10 h-10 text-slate-300 group-hover:text-indigo-500 transition-colors mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                            </svg>
                            <p class="text-xs font-bold text-slate-400 group-hover:text-slate-600 transition-colors">Select Files to Upload</p>
                        </div>
                        
                        <div class="p-4 bg-rose-50 rounded-2xl border border-rose-100">
                            <p class="text-[9px] font-bold text-rose-600 uppercase tracking-widest mb-1">Due Date Reminder</p>
                            <p class="text-xs font-bold text-rose-800">{{ $activity->due_date->format('l, M d @ h:i A') }}</p>
                        </div>

                        <button type="submit" class="w-full py-4 bg-indigo-600 hover:bg-indigo-700 text-white rounded-3xl font-bold shadow-xl shadow-indigo-100 transition-all transform hover:-translate-y-1">
                            Turn In Work
                        </button>
                    </div>
                </form>
                @else
                <!-- Exam placeholder -->
                <div class="text-center py-6">
                    <div class="w-16 h-16 bg-rose-100 text-rose-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900">Online Examination</h3>
                    <p class="text-slate-500 text-sm mt-2">This activity is an online exam. You must complete it within the system.</p>
                    <p class="text-rose-500 text-xs font-bold mt-4 uppercase tracking-widest leading-relaxed">Ensure a stable connection <br> before starting.</p>
                </div>
                @endif
            </div>
            
            <!-- Logistics Sidebar -->
            <div class="bg-slate-900 rounded-[2.5rem] p-8 text-white shadow-xl">
                <h3 class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-6">Academic Metadata</h3>
                <div class="space-y-6">
                    <div>
                        <p class="text-[10px] font-bold text-indigo-400 uppercase tracking-widest mb-1">Section</p>
                        <p class="font-bold text-lg">{{ $activity->teacherAssignment->section->name }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-indigo-400 uppercase tracking-widest mb-1">Subject</p>
                        <p class="font-bold text-lg">{{ $activity->teacherAssignment->subject->name }}</p>
                    </div>
                    @if($activity->assessmentTemplate)
                    <div>
                        <p class="text-[10px] font-bold text-emerald-400 uppercase tracking-widest mb-1">Grading Category</p>
                        <p class="font-bold">{{ $activity->assessmentTemplate->name }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
</x-student-layout>
