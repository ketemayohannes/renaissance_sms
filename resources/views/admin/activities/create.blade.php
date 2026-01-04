<x-admin-layout>
    <x-slot name="header">
        <h1 class="text-2xl font-bold text-slate-800 font-heading">New Activity</h1>
    </x-slot>

    <div class="px-6 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Breadcrumb & Header -->
        <div class="mb-8">
            <a href="{{ route('admin.activities.index') }}" class="inline-flex items-center gap-2 text-slate-500 hover:text-indigo-600 transition-colors mb-4">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Activities
            </a>
            <h1 class="text-3xl font-bold text-slate-900 font-heading tracking-tight">Create Academic Activity</h1>
            <p class="text-slate-500 mt-1">Configure your new homework, assignment, or online exam.</p>
        </div>

        <script>
            window.activityData = {
                assignments: @json($mappedAssignments),
                grades: @json($mappedGrades),
                subjects: @json($mappedSubjects)
            };

            function activityForm() {
                return {
                    type: 'homework',
                    assignments: window.activityData.assignments,
                    allGrades: window.activityData.grades,
                    allSubjects: window.activityData.subjects,
                    
                    gradeId: '{{ old('grade_id') }}',
                    sectionId: '{{ old('section_id') }}',
                    subjectId: '',
                    assignmentId: '{{ old('teacher_assignment_id') }}',
                    
                    templates: [],
                    termId: '',
                    
                    get availableGrades() {
                        return this.allGrades;
                    },
                    get availableSections() {
                        if (!this.gradeId) return [];
                        const grade = this.allGrades.find(g => g.id == this.gradeId);
                        return grade ? grade.sections : [];
                    },
                    get availableSubjects() {
                        if (!this.gradeId) return [];
                        const grade = this.allGrades.find(g => g.id == this.gradeId);
                        return grade ? grade.subjects : [];
                    },
                    get availableTerms() {
                        const terms = new Map();
                        this.templates.forEach(t => {
                            if (t.term) {
                                terms.set(t.term.id, t.term.name);
                            } else {
                                terms.set('general', 'General');
                            }
                        });
                        return Array.from(terms, ([id, name]) => ({ id, name }));
                    },
                    get filteredTemplates() {
                        if (!this.termId) return [];
                        return this.templates.filter(t => {
                            const tTermId = t.term ? t.term.id : 'general';
                            return tTermId == this.termId;
                        });
                    },
                    checkAssignment() {
                        const match = this.assignments.find(a => 
                            a.grade_id == this.gradeId && 
                            a.section_id == this.sectionId && 
                            a.subject_id == this.subjectId
                        );
                        this.assignmentId = match ? match.id : '';
                        this.fetchTemplates();
                    },
                    init() {
                        if (this.gradeId && this.sectionId && this.subjectId) {
                            this.checkAssignment();
                        }
                    },
                    fetchTemplates() {
                        if(!this.gradeId || !this.subjectId) return;
                        
                        let query = `?grade_id=${this.gradeId}&subject_id=${this.subjectId}`;
                        if(this.assignmentId) query += `&teacher_assignment_id=${this.assignmentId}`;

                        fetch(`{{ route('admin.activities.get-templates') }}${query}`)
                            .then(r => r.json())
                            .then(data => {
                                this.templates = data;
                                // Auto-select first term if available
                                if (this.availableTerms.length > 0) {
                                    this.termId = this.availableTerms[0].id;
                                }
                            });
                    }
                }
            }
        </script>

        <form action="{{ route('admin.activities.store') }}" method="POST" enctype="multipart/form-data" 
              x-data="activityForm()">
            @csrf
            <input type="hidden" name="teacher_assignment_id" x-model="assignmentId">
            
            <div class="space-y-6">
                <!-- Basic Configuration Card -->
                <div class="bg-white/70 backdrop-blur-xl border border-slate-200 rounded-[2.5rem] p-8 shadow-sm">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <!-- Grade Selection -->
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-widest px-1">1. Select Grade</label>
                            <select x-model="gradeId" @change="sectionId = ''; assignmentId = ''"
                                    class="w-full h-14 bg-slate-50 border-0 rounded-2xl px-4 font-semibold text-slate-700 focus:ring-2 focus:ring-indigo-500 transition-all">
                                <option value="">-- Choose Grade --</option>
                                <template x-for="grade in availableGrades" :key="grade.id">
                                    <option :value="grade.id" x-text="grade.name"></option>
                                </template>
                            </select>
                        </div>

                        <!-- Section Selection -->
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-widest px-1">2. Select Section</label>
                            <select x-model="sectionId" @change="assignmentId = ''" :disabled="!gradeId"
                                    class="w-full h-14 bg-slate-50 border-0 rounded-2xl px-4 font-semibold text-slate-700 focus:ring-2 focus:ring-indigo-500 transition-all disabled:opacity-50">
                                <option value="">-- Choose Section --</option>
                                <template x-for="section in availableSections" :key="section.id">
                                    <option :value="section.id" x-text="section.name"></option>
                                </template>
                            </select>
                        </div>

                        <!-- Subject Selection -->
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-widest px-1">3. Select Subject</label>
                            <select x-model="subjectId" @change="checkAssignment()" :disabled="!sectionId"
                                    class="w-full h-14 bg-slate-50 border-0 rounded-2xl px-4 font-semibold text-slate-700 focus:ring-2 focus:ring-indigo-500 transition-all disabled:opacity-50">
                                <option value="">-- Choose Subject --</option>
                                <template x-for="subject in availableSubjects" :key="subject.id">
                                    <option :value="subject.id" x-text="subject.name"></option>
                                </template>
                            </select>
                            <div x-show="subjectId && !assignmentId" class="mt-2 text-rose-500 text-xs font-bold px-1 animate-pulse">
                                Warning: No teacher explicitly assigned to this combination.
                            </div>
                        </div>

                        <!-- Activity Type -->
                        <div class="col-span-full">
                            <label class="text-sm font-bold text-slate-700 uppercase tracking-wider mb-4 block">Select Activity Type</label>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                @foreach(['homework' => 'Emerald', 'assignment' => 'Indigo', 'exam' => 'Rose'] as $key => $color)
                                <label class="relative cursor-pointer group">
                                    <input type="radio" name="type" value="{{ $key }}" class="peer sr-only" x-model="type" {{ old('type', 'homework') == $key ? 'checked' : '' }}>
                                    <div class="p-4 rounded-3xl border-2 border-slate-100 bg-slate-50 group-hover:bg-white transition-all peer-checked:border-{{ strtolower($color) }}-500 peer-checked:bg-white peer-checked:ring-4 peer-checked:ring-{{ strtolower($color) }}-50">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-2xl bg-{{ strtolower($color) }}-100 flex items-center justify-center text-{{ strtolower($color) }}-600 font-bold">
                                                {{ strtoupper(substr($key, 0, 1)) }}
                                            </div>
                                            <div>
                                                <p class="font-bold text-slate-900 capitalize">{{ $key }}</p>
                                                <p class="text-[10px] text-slate-500">Quick Configuration</p>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Selection Indicator -->
                                    <div class="absolute -top-2 -right-2 w-6 h-6 bg-{{ strtolower($color) }}-500 rounded-full items-center justify-center text-white hidden peer-checked:flex shadow-lg shadow-{{ strtolower($color) }}-100">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </div>
                                </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- Assessment Mapping (Two-step selection) -->
                        <div class="space-y-4 col-span-full" x-show="type !== 'homework'">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <!-- Term Selection -->
                                <div class="space-y-2">
                                    <label class="text-xs font-bold text-slate-500 uppercase tracking-widest px-1">Select Term</label>
                                    <select x-model="termId" 
                                            class="w-full h-14 bg-slate-50 border-0 rounded-2xl px-4 font-semibold text-slate-700 focus:ring-2 focus:ring-indigo-500 transition-all">
                                        <template x-for="term in availableTerms" :key="term.id">
                                            <option :value="term.id" x-text="term.name"></option>
                                        </template>
                                    </select>
                                </div>

                                <!-- Template Selection -->
                                <div class="space-y-2">
                                    <label for="assessment_template_id" class="text-xs font-bold text-slate-500 uppercase tracking-widest px-1">Official Assessment</label>
                                    <select name="assessment_template_id" id="assessment_template_id" 
                                            class="w-full h-14 bg-slate-50 border-0 rounded-2xl px-4 font-semibold text-slate-700 focus:ring-2 focus:ring-indigo-500 transition-all">
                                        <option value="">-- Select Assessment --</option>
                                        <template x-for="t in filteredTemplates" :key="t.id">
                                            <option :value="t.id" x-text="`${t.name} (Max: ${t.max_score})`"></option>
                                        </template>
                                    </select>
                                </div>
                            </div>
                            <p class="text-[10px] text-indigo-500 font-medium px-1">Selection here automates Gradebook syncing.</p>
                        </div>

                        <!-- Title -->
                        <div class="space-y-2 col-span-full">
                            <label for="title" class="text-xs font-bold text-slate-500 uppercase tracking-widest px-1">Activity Title</label>
                            <input type="text" name="title" id="title" value="{{ old('title') }}" required placeholder="e.g. Q1 Mathematical Foundations Assignment"
                                   class="w-full h-14 bg-slate-50 border-0 rounded-2xl px-4 font-semibold text-slate-700 focus:ring-2 focus:ring-indigo-500 transition-all">
                        </div>

                        <!-- Description -->
                        <div class="space-y-2 col-span-full">
                            <label for="description" class="text-xs font-bold text-slate-500 uppercase tracking-widest px-1">Instructions / Description</label>
                            <textarea name="description" id="description" rows="4" 
                                      class="w-full bg-slate-50 border-0 rounded-2xl p-4 font-semibold text-slate-700 focus:ring-2 focus:ring-indigo-500 transition-all"
                                      placeholder="Provide detailed instructions for the students..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- Logistics Card -->
                <div class="bg-white/70 backdrop-blur-xl border border-slate-200 rounded-[2.5rem] p-8 shadow-sm">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Due Date -->
                        <div class="space-y-2 text-rose-600">
                            <label for="due_date" class="text-xs font-bold uppercase tracking-widest px-1 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Submission Deadline
                            </label>
                            <input type="datetime-local" name="due_date" id="due_date" required
                                   class="w-full h-14 bg-rose-50 border-0 rounded-2xl px-4 font-semibold text-rose-700 focus:ring-2 focus:ring-rose-500 transition-all">
                        </div>

                        <!-- Max Score -->
                        <div class="space-y-2">
                            <label for="max_score" class="text-xs font-bold text-slate-500 uppercase tracking-widest px-1">Total Possible Points</label>
                            <input type="number" name="max_score" id="max_score" value="{{ old('max_score', 100) }}" step="0.5"
                                   class="w-full h-14 bg-slate-50 border-0 rounded-2xl px-4 font-semibold text-slate-700 focus:ring-2 focus:ring-indigo-500 transition-all">
                        </div>

                        <!-- Attachments (Dossier Link) -->
                        <div class="col-span-full space-y-2">
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-widest px-1 block mb-2">Supporting Materials / Downloadable Task</label>
                            <div class="relative group h-32 border-2 border-dashed border-slate-200 rounded-[2rem] hover:border-indigo-400 transition-all flex flex-col items-center justify-center bg-slate-50/50">
                                <input type="file" name="attachments[]" multiple class="absolute inset-0 opacity-0 cursor-pointer">
                                <svg class="w-8 h-8 text-slate-300 group-hover:text-indigo-500 transition-colors mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                </svg>
                                <p class="text-sm font-semibold text-slate-400 group-hover:text-slate-600 transition-colors">Drag files here or click to upload</p>
                                <p class="text-[10px] text-slate-400">PDF, Images, Docs (Max 10MB per file)</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end gap-4 pb-12">
                    <button type="button" onclick="history.back()" class="px-8 py-4 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-3xl font-bold transition-all">
                        Discard Changes
                    </button>
                    <button type="submit" class="px-10 py-4 bg-indigo-600 hover:bg-indigo-700 text-white rounded-3xl font-bold shadow-xl shadow-indigo-100 transition-all transform hover:-translate-y-1">
                        Publish Activity
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
</x-admin-layout>


