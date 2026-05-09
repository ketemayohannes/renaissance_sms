<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.exams.index') }}" class="p-2 text-slate-400 hover:text-slate-900 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <h1 class="text-3xl font-black text-slate-900 tracking-tight">Review Exam Paper</h1>
                <p class="text-slate-500 text-sm mt-1 font-medium">Verify content and provide feedback.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 max-w-[90rem] mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Left: Paper Content -->
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white p-12 rounded-[3rem] border border-slate-100 shadow-2xl shadow-slate-200/50 min-h-[800px]">
                    @if($exam->type === 'text')
                        <div class="prose prose-slate max-w-none prose-headings:font-black prose-p:font-medium prose-p:text-slate-600">
                            {!! $exam->content !!}
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center py-24 text-center">
                            <div class="w-24 h-24 bg-indigo-50 rounded-[2rem] flex items-center justify-center text-indigo-600 mb-6">
                                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.707 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                            </div>
                            <h2 class="text-2xl font-black text-slate-900 mb-2">Uploaded Exam Document</h2>
                            <p class="text-slate-500 mb-8 max-w-md">This paper was submitted as a file. Please download it using the button below to review the contents.</p>
                            <a href="{{ route('admin.exams.download', $exam) }}" class="px-8 py-4 bg-indigo-600 text-white font-black text-xs uppercase tracking-widest rounded-2xl hover:bg-indigo-700 transition-all shadow-xl shadow-indigo-100 flex items-center gap-3">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                Download Document
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Right: Submission Info & Review Tools -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Info Card -->
                <div class="bg-white p-8 rounded-[2rem] border border-slate-100 shadow-xl shadow-slate-200/50 space-y-6">
                    <h3 class="text-xs font-black text-slate-400 uppercase tracking-[0.2em]">Paper Information</h3>
                    
                    <div class="space-y-6">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-400">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            </div>
                            <div>
                                <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Teacher</div>
                                <div class="text-sm font-black text-slate-900 leading-tight">{{ $exam->teacher->full_name }}</div>
                            </div>
                        </div>

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
                                <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Submitted At</div>
                                <div class="text-sm font-black text-slate-900 leading-tight">{{ $exam->submitted_at?->format('M d, Y H:i') ?? 'N/A' }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Review Form -->
                <div class="bg-white p-8 rounded-[2rem] border border-slate-100 shadow-xl shadow-slate-200/50 space-y-6">
                    <h3 class="text-xs font-black text-slate-400 uppercase tracking-[0.2em]">Decision</h3>
                    
                    <form action="{{ route('admin.exams.review', $exam) }}" method="POST" class="space-y-6">
                        @csrf
                        <div class="grid grid-cols-2 gap-3">
                            <label class="cursor-pointer">
                                <input type="radio" name="status" value="approved" class="sr-only peer" {{ $exam->status === 'approved' ? 'checked' : '' }}>
                                <div class="py-4 px-4 rounded-2xl text-center text-[10px] font-black uppercase tracking-widest transition-all peer-checked:bg-emerald-600 peer-checked:text-white bg-slate-50 text-slate-500 hover:bg-slate-100">
                                    Approve
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="status" value="rejected" class="sr-only peer" {{ $exam->status === 'rejected' ? 'checked' : '' }}>
                                <div class="py-4 px-4 rounded-2xl text-center text-[10px] font-black uppercase tracking-widest transition-all peer-checked:bg-rose-600 peer-checked:text-white bg-slate-50 text-slate-500 hover:bg-slate-100">
                                    Needs Revision
                                </div>
                            </label>
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 ml-1">Reviewer Comments</label>
                            <textarea name="review_comments" rows="5" class="w-full bg-slate-50 border-0 rounded-2xl focus:ring-2 focus:ring-indigo-500 font-bold text-slate-700 p-6 placeholder:text-slate-300" placeholder="Provide feedback or reasons for rejection...">{{ $exam->review_comments }}</textarea>
                        </div>

                        <button type="submit" class="w-full py-4 bg-slate-900 text-white font-black text-xs uppercase tracking-widest rounded-2xl hover:bg-indigo-600 transition-all shadow-xl shadow-slate-200">
                            Submit Review
                        </button>
                    </form>
                </div>

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
                    <form action="{{ route('admin.exams.destroy', $exam) }}" method="POST" id="delete-exam-form">
                        @csrf
                        @method('DELETE')
                        <button type="button" x-data @click="window.confirmUI({title: 'Delete Exam', message: 'Are you sure you want to permanently delete this exam paper? This action cannot be undone.', type: 'danger', buttonText: 'Yes, Delete', callback: () => document.getElementById('delete-exam-form').submit()})" class="w-full py-4 bg-white text-rose-600 border border-rose-200 font-black text-xs uppercase tracking-widest rounded-2xl hover:bg-rose-50 hover:border-rose-300 transition-all shadow-sm">
                            Delete Exam Paper
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
