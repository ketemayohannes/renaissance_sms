<x-teacher-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('teacher.exams.index') }}" class="p-2 text-slate-400 hover:text-slate-900 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <h1 class="text-3xl font-black text-slate-900 tracking-tight">Edit Exam Paper</h1>
                <p class="text-slate-500 text-sm mt-1 font-medium">Update your exam details and content.</p>
            </div>
        </div>
    </x-slot>

    @if($exam->status === 'rejected')
        <div class="max-w-7xl mx-auto mt-8">
            <div class="bg-rose-50 border border-rose-100 p-8 rounded-[2rem] flex gap-6">
                <div class="w-14 h-14 bg-rose-500 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-rose-200 shrink-0">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
                <div>
                    <h3 class="text-lg font-black text-rose-900 leading-tight">Revision Required</h3>
                    <p class="text-rose-600 font-bold text-sm mt-1">Reviewer Feedback: <span class="italic font-medium text-rose-500">"{{ $exam->review_comments }}"</span></p>
                </div>
            </div>
        </div>
    @endif

    <div class="space-y-8">

        <form action="{{ route('teacher.exams.update', $exam) }}" method="POST" enctype="multipart/form-data" class="max-w-7xl mx-auto space-y-8">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">
                <!-- Left Column: Metadata -->
                <div class="lg:col-span-1 space-y-6">
                    <div class="bg-white p-8 rounded-[2rem] border border-slate-100 shadow-xl shadow-slate-200/50 space-y-6">
                        <h3 class="text-xs font-black text-slate-400 uppercase tracking-[0.2em]">Exam Details</h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 ml-1">Academic Year</label>
                                <select name="academic_year_id" class="w-full bg-slate-50 border-0 rounded-2xl focus:ring-2 focus:ring-indigo-500 font-bold text-slate-700">
                                    @foreach($academicYears as $year)
                                        <option value="{{ $year->id }}" {{ $exam->academic_year_id == $year->id ? 'selected' : '' }}>{{ $year->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 ml-1">Term</label>
                                <select name="term_id" class="w-full bg-slate-50 border-0 rounded-2xl focus:ring-2 focus:ring-indigo-500 font-bold text-slate-700">
                                    @foreach($terms as $term)
                                        <option value="{{ $term->id }}" {{ $exam->term_id == $term->id ? 'selected' : '' }}>{{ $term->name }}</option>
                                        <option value="{{ $term->id }}" {{ old('term_id', $exam->term_id) == $term->id ? 'selected' : '' }}>{{ $term->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 ml-1">Subject & Grade</label>
                                <select name="assignment_selection" id="assignment_selection" class="w-full bg-slate-50 border-0 rounded-2xl focus:ring-2 focus:ring-indigo-500 font-bold text-slate-700 @error('subject_id') ring-2 ring-rose-500 @enderror">
                                    <option value="">Select Assignment</option>
                                    @foreach($assignments as $assignment)
                                        @php
                                            $isSelected = old('subject_id', $exam->subject_id) == $assignment->subject_id && old('grade_level_id', $exam->grade_level_id) == $assignment->section->grade_level_id;
                                        @endphp
                                        <option value="{{ $assignment->subject_id }}|{{ $assignment->section->grade_level_id }}" {{ $isSelected ? 'selected' : '' }}>
                                            {{ $assignment->section->gradeLevel->name }} - {{ $assignment->subject->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="subject_id" id="subject_id" value="{{ old('subject_id', $exam->subject_id) }}">
                                <input type="hidden" name="grade_level_id" id="grade_level_id" value="{{ old('grade_level_id', $exam->grade_level_id) }}">
                                @error('subject_id')
                                    <p class="text-rose-500 text-[10px] font-black mt-2 ml-1 uppercase tracking-widest">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 ml-1">Submission Type</label>
                                <div class="grid grid-cols-2 gap-2 p-1 bg-slate-100 rounded-2xl">
                                    <label class="cursor-pointer">
                                        <input type="radio" name="type" value="text" class="sr-only peer" {{ old('type', $exam->type) === 'text' ? 'checked' : '' }}>
                                        <div class="py-2 px-4 rounded-xl text-center text-xs font-black uppercase tracking-widest transition-all peer-checked:bg-white peer-checked:shadow-sm peer-checked:text-indigo-600 text-slate-500">
                                            Text Editor
                                        </div>
                                    </label>
                                    <label class="cursor-pointer">
                                        <input type="radio" name="type" value="upload" class="sr-only peer" {{ old('type', $exam->type) === 'upload' ? 'checked' : '' }}>
                                        <div class="py-2 px-4 rounded-xl text-center text-xs font-black uppercase tracking-widest transition-all peer-checked:bg-white peer-checked:shadow-sm peer-checked:text-indigo-600 text-slate-500">
                                            File Upload
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Content -->
                <div class="lg:col-span-4 space-y-6">
                    <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-xl shadow-slate-200/50 space-y-6">
                        <div>
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 ml-1">Exam Title</label>
                            <input type="text" name="title" value="{{ old('title', $exam->title) }}" placeholder="e.g. Midterm 1 - Mathematics" class="w-full bg-slate-50 border-0 rounded-2xl focus:ring-2 focus:ring-indigo-500 font-bold text-slate-700 px-6 py-4 placeholder:text-slate-300 @error('title') ring-2 ring-rose-500 @enderror">
                            @error('title')
                                <p class="text-rose-500 text-[10px] font-black mt-2 ml-1 uppercase tracking-widest">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Editor Container -->
                        <div id="text_container" class="{{ old('type', $exam->type) === 'upload' ? 'hidden' : '' }} space-y-4">
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 ml-1 text-center">Exam Paper Content</label>
                            <div class="prose max-w-none border border-slate-100 rounded-3xl overflow-hidden min-h-[400px]">
                                <textarea name="content" id="editor">{{ old('content', $exam->content) }}</textarea>
                            </div>
                            @error('content')
                                <p class="text-rose-500 text-[10px] font-black mt-2 ml-1 uppercase tracking-widest text-center">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Upload Container -->
                        <div id="upload_container" class="{{ old('type', $exam->type) === 'text' ? 'hidden' : '' }} space-y-4">
                            @if($exam->file_path)
                                <div class="bg-slate-50 p-6 rounded-2xl flex items-center justify-between">
                                    <div class="flex items-center gap-4 text-slate-700 font-bold text-sm">
                                        <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.707 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                        Currently Uploaded File
                                    </div>
                                    <a href="{{ route('teacher.exams.download', $exam) }}" class="text-xs font-black text-indigo-600 hover:indigo-700 uppercase tracking-widest">Download</a>
                                </div>
                            @endif

                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 ml-1 text-center">Upload New File (Optional if unchanged)</label>
                            <div class="relative group">
                                <input type="file" name="file" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                                <div class="border-4 border-dashed border-slate-100 rounded-[2.5rem] p-12 text-center group-hover:border-indigo-100 transition-colors bg-slate-50/50">
                                    <div class="w-16 h-16 bg-white rounded-2xl shadow-sm flex items-center justify-center mx-auto mb-4 text-indigo-500">
                                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                                    </div>
                                    <p id="file-name-display" class="text-sm font-bold text-slate-600">Drag & Drop or Click to Upload</p>
                                    <p id="file-hint" class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1">MAX 10MB • PDF, DOC, DOCX</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-4">
                        <button type="submit" name="draft" class="px-8 py-4 bg-white border border-slate-200 text-slate-600 font-black text-xs uppercase tracking-[0.2em] rounded-2xl hover:bg-slate-50 transition-all shadow-sm">
                            Save Changes
                        </button>
                        <button type="submit" name="submit" class="px-10 py-4 bg-indigo-600 text-white font-black text-xs uppercase tracking-[0.2em] rounded-2xl hover:bg-indigo-700 transition-all shadow-xl shadow-indigo-100">
                            Resubmit for Review
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
    <script src="https://cdn.ckeditor.com/ckeditor5/40.0.0/classic/ckeditor.js"></script>
    <script>
        ClassicEditor
            .create(document.querySelector('#editor'), {
                toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote', 'insertTable', 'undo', 'redo'],
                placeholder: 'Start writing your exam questions here...'
            })
            .catch(error => { console.error(error); });

        const typeRadios = document.querySelectorAll('input[name="type"]');
        const textContainer = document.getElementById('text_container');
        const uploadContainer = document.getElementById('upload_container');

        const updateTypeContainers = (val) => {
            if (val === 'text') {
                textContainer.classList.remove('hidden');
                uploadContainer.classList.add('hidden');
            } else {
                textContainer.classList.add('hidden');
                uploadContainer.classList.remove('hidden');
            }
        };

        // Initialize based on old input
        const checkedRadio = document.querySelector('input[name="type"]:checked');
        if (checkedRadio) updateTypeContainers(checkedRadio.value);

        typeRadios.forEach(radio => {
            radio.addEventListener('change', (e) => updateTypeContainers(e.target.value));
        });

        const selection = document.getElementById('assignment_selection');
        const subjectId = document.getElementById('subject_id');
        const gradeId = document.getElementById('grade_level_id');

        selection.addEventListener('change', (e) => {
            const val = e.target.value;
            if (val) {
                const parts = val.split('|');
                subjectId.value = parts[0];
                gradeId.value = parts[1];
            }
        });

        // File Name Display
        const fileInput = document.querySelector('input[name="file"]');
        const fileNameDisplay = document.getElementById('file-name-display');
        const fileHint = document.getElementById('file-hint');

        if (fileInput && fileNameDisplay) {
            fileInput.addEventListener('change', (e) => {
                if (e.target.files.length > 0) {
                    fileNameDisplay.textContent = e.target.files[0].name;
                    fileNameDisplay.classList.add('text-indigo-600');
                    if (fileHint) fileHint.classList.add('hidden');
                } else {
                    fileNameDisplay.textContent = 'Drag & Drop or Click to Upload';
                    fileNameDisplay.classList.remove('text-indigo-600');
                    if (fileHint) fileHint.classList.remove('hidden');
                }
            });
        }
    </script>
    <style>
        .ck-editor__editable {
            min-height: 400px;
            background-color: #f8fafc !important;
            border: 0 !important;
            padding: 2rem !important;
        }
        .ck-editor__editable h1 {
            font-size: 2em;
            font-weight: 800;
            margin: 0.67em 0;
            line-height: 1.2;
        }
        .ck-editor__editable h2 {
            font-size: 1.5em;
            font-weight: 700;
            margin: 0.75em 0;
            line-height: 1.3;
        }
        .ck-editor__editable h3 {
            font-size: 1.25em;
            font-weight: 700;
            margin: 0.75em 0;
            line-height: 1.4;
        }
        .ck-editor__editable h4 {
            font-size: 1.1em;
            font-weight: 600;
            margin: 0.75em 0;
        }
        .ck-editor__editable p {
            margin: 0.5em 0;
            line-height: 1.7;
        }
        .ck-editor__editable ul {
            list-style-type: disc;
            padding-left: 1.5em;
            margin: 0.5em 0;
        }
        .ck-editor__editable ol {
            list-style-type: decimal;
            padding-left: 1.5em;
            margin: 0.5em 0;
        }
        .ck-editor__editable li {
            margin: 0.25em 0;
        }
        .ck-editor__editable blockquote {
            border-left: 4px solid #6366f1;
            padding-left: 1em;
            margin: 1em 0;
            color: #475569;
            font-style: italic;
        }
        .ck-editor__editable a {
            color: #4f46e5;
            text-decoration: underline;
        }
        .ck-toolbar {
            background-color: #fff !important;
            border: 0 !important;
            border-bottom: 1px solid #f1f5f9 !important;
            padding: 0.5rem 1rem !important;
        }
    </style>
    @endpush
</x-teacher-layout>
