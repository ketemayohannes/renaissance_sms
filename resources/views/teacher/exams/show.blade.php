<x-teacher-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('teacher.exams.index') }}" class="p-2 text-slate-400 hover:text-slate-900 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <h1 class="text-3xl font-black text-slate-900 tracking-tight">View Exam Paper</h1>
                <p class="text-slate-500 text-sm mt-1 font-medium">Review your exam paper details and content.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Left: Paper Content -->
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white p-12 rounded-[3rem] border border-slate-100 shadow-2xl shadow-slate-200/50 min-h-[800px]">
                    @if($exam->type === 'text')
                        <div class="prose prose-slate max-w-none prose-headings:font-black prose-p:font-medium prose-p:text-slate-600 ck-content">
                            {!! clean($exam->content) !!}
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center py-24 text-center">
                            <div class="w-24 h-24 bg-indigo-50 rounded-[2rem] flex items-center justify-center text-indigo-600 mb-6">
                                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.707 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                            </div>
                            <h2 class="text-2xl font-black text-slate-900 mb-2">Uploaded Exam Document</h2>
                            <p class="text-slate-500 mb-8 max-w-md">This paper was submitted as a file. You can download it using the button below.</p>
                            @if($exam->file_path)
                                <a href="{{ route('teacher.exams.download', $exam) }}" class="px-8 py-4 bg-indigo-600 text-white font-black text-xs uppercase tracking-widest rounded-2xl hover:bg-indigo-700 transition-all shadow-xl shadow-indigo-100 flex items-center gap-3 inline-flex">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                    Download Document
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <!-- Right: Submission Info -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Info Card -->
                <div class="bg-white p-8 rounded-[2rem] border border-slate-100 shadow-xl shadow-slate-200/50 space-y-6">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xs font-black text-slate-400 uppercase tracking-[0.2em]">Paper Information</h3>
                        @if(in_array($exam->status, ['draft', 'rejected']))
                            <a href="{{ route('teacher.exams.edit', $exam) }}" class="text-[10px] font-black text-indigo-600 uppercase tracking-widest hover:text-indigo-700 transition-colors">
                                Edit Paper
                            </a>
                        @endif
                    </div>
                    
                    <div class="space-y-6">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-400">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                            </div>
                            <div>
                                <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Subject & Grade</div>
                                <div class="text-sm font-black text-slate-900 leading-tight">{{ $exam->subject->name }} ({{ $exam->gradeLevel->name }})</div>
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-400">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            </div>
                            <div>
                                <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Status</div>
                                <div class="mt-1">
                                    @php
                                        $statusColors = [
                                            'draft' => 'bg-slate-100 text-slate-600',
                                            'submitted' => 'bg-amber-100 text-amber-600',
                                            'under_review' => 'bg-indigo-100 text-indigo-600',
                                            'approved' => 'bg-emerald-100 text-emerald-600',
                                            'rejected' => 'bg-rose-100 text-rose-600',
                                        ];
                                    @endphp
                                    <span class="px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest {{ $statusColors[$exam->status] }}">
                                        {{ str_replace('_', ' ', $exam->status) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        @if($exam->submitted_at)
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-400">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                                <div>
                                    <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Submitted At</div>
                                    <div class="text-sm font-black text-slate-900 leading-tight">{{ $exam->submitted_at->format('M d, Y H:i') }}</div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                @if($exam->status === 'rejected' && $exam->review_comments)
                    <div class="bg-rose-50 border border-rose-100 p-8 rounded-[2rem] space-y-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-rose-500 rounded-xl flex items-center justify-center text-white shadow-lg shadow-rose-200">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            </div>
                            <h3 class="text-sm font-black text-rose-900 uppercase tracking-widest">Review Feedback</h3>
                        </div>
                        <p class="text-rose-700 font-medium text-sm leading-relaxed italic">
                            "{{ $exam->review_comments }}"
                        </p>
                        <div class="pt-2">
                            <a href="{{ route('teacher.exams.edit', $exam) }}" class="inline-flex items-center gap-2 px-6 py-3 bg-rose-600 text-white font-black text-[10px] uppercase tracking-widest rounded-xl hover:bg-rose-700 transition-all shadow-lg shadow-rose-100">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                Resolve & Re-submit
                            </a>
                        </div>
                    </div>
                @endif

                @if(in_array($exam->status, ['draft', 'rejected']))
                    <!-- Danger Zone -->
                    <div class="bg-rose-50 p-8 rounded-[2rem] border border-rose-100 shadow-xl shadow-rose-200/50 space-y-6">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-rose-500 rounded-xl flex items-center justify-center text-white shadow-lg shadow-rose-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </div>
                            <h3 class="text-xs font-black text-rose-900 uppercase tracking-[0.2em]">Danger Zone</h3>
                        </div>
                        <p class="text-[10px] font-bold text-rose-700 uppercase tracking-widest leading-relaxed">
                            Permanently delete this exam paper. This action cannot be undone.
                        </p>
                        <form action="{{ route('teacher.exams.destroy', $exam) }}" method="POST" id="delete-exam-form">
                            @csrf
                            @method('DELETE')
                            <button type="button" x-data @click="window.confirmUI({title: 'Delete Exam', message: 'Are you sure you want to permanently delete this exam paper? This action cannot be undone.', type: 'danger', buttonText: 'Yes, Delete', callback: () => document.getElementById('delete-exam-form').submit()})" class="w-full py-4 bg-white text-rose-600 border border-rose-200 font-black text-xs uppercase tracking-widest rounded-2xl hover:bg-rose-50 hover:border-rose-300 transition-all shadow-sm">
                                Delete Exam Paper
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        .ck-content h1 { font-size: 2em; font-weight: 800; margin: 0.67em 0; line-height: 1.2; }
        .ck-content h2 { font-size: 1.5em; font-weight: 700; margin: 0.75em 0; line-height: 1.3; }
        .ck-content h3 { font-size: 1.25em; font-weight: 700; margin: 0.75em 0; line-height: 1.4; }
        .ck-content h4 { font-size: 1.1em; font-weight: 600; margin: 0.75em 0; }
        .ck-content p { margin: 0.5em 0; line-height: 1.7; }
        .ck-content ul { list-style-type: disc; padding-left: 1.5em; margin: 0.5em 0; }
        .ck-content ol { list-style-type: decimal; padding-left: 1.5em; margin: 0.5em 0; }
        .ck-content blockquote { border-left: 4px solid #6366f1; padding-left: 1em; margin: 1em 0; color: #475569; font-style: italic; }
    </style>
    @endpush
</x-teacher-layout>
