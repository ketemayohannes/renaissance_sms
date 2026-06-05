<x-admin-layout>
    <x-slot name="header">Section Grade Entry (Master Sheet)</x-slot>

    <div class="space-y-8">
        <!-- Breadcrumb & Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <x-breadcrumb :items="[
                    ['label' => 'Section Grades', 'url' => '#']
                ]" />
                <h1 class="text-4xl font-black text-slate-900 tracking-tight mt-2">Master Sheet</h1>
            </div>
            <div class="flex items-center gap-3">
                <div class="px-4 py-2 bg-rose-50 rounded-2xl border border-rose-100 flex items-center gap-3 shadow-sm shadow-rose-100/50">
                    <div class="w-8 h-8 rounded-xl bg-rose-600 flex items-center justify-center text-white shadow-lg shadow-rose-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    </div>
                    <div>
                        <span class="block text-[10px] font-black text-rose-900 uppercase tracking-widest">Master View</span>
                        <span class="block text-xs font-bold text-rose-600/80 mt-0.5">Bulk Score Management</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Selection Panel -->
        <div class="bg-white/40 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-xl shadow-slate-200/50 p-10">
            <div class="w-full">
                <div class="text-center mb-10">
                    <div class="w-20 h-20 bg-slate-900 rounded-[2rem] flex items-center justify-center text-white mx-auto mb-6 shadow-2xl shadow-slate-200">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 01-2-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    </div>
                    <h2 class="text-3xl font-black text-slate-900 tracking-tight">Access Master Sheet</h2>
                    <p class="text-slate-500 font-semibold mt-2 text-balance lg:text-center px-10">Select parameters to open the comprehensive section grades overview for batch processing.</p>
                </div>

                <form action="{{ route('admin.section-grades.entry') }}" method="GET" class="space-y-8">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 items-end">
                        <div class="space-y-2">
                            <label for="academic_year_id" class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Academic Year</label>
                            <select name="academic_year_id" id="academic_year_id" required class="w-full bg-white/50 border-slate-200 rounded-2xl focus:ring-emerald-500 focus:border-emerald-500 font-bold text-sm py-4 px-6 transition-all appearance-none cursor-pointer">
                                @foreach($academicYears as $year)
                                    <option value="{{ $year->id }}" {{ $year->is_active ? 'selected' : '' }}>{{ $year->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label for="term_id" class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Academic Term</label>
                            <select name="term_id" id="term_id" required class="w-full bg-white/50 border-slate-200 rounded-2xl focus:ring-emerald-500 focus:border-emerald-500 font-bold text-sm py-4 px-6 transition-all appearance-none cursor-pointer">
                                <option value="">Select Term</option>
                                @foreach($terms as $term)
                                    <option value="{{ $term->id }}" {{ ($term->is_grading_open || $term->is_master_grading_open) ? 'selected' : '' }}>{{ $term->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label for="grade_level_id" class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Grade Level</label>
                            <select name="grade_level_id" id="grade_level_id" required class="w-full bg-white/50 border-slate-200 rounded-2xl focus:ring-emerald-500 focus:border-emerald-500 font-bold text-sm py-4 px-6 transition-all appearance-none cursor-pointer">
                                <option value="">Select Grade</option>
                                @foreach($gradeLevels as $grade)
                                    <option value="{{ $grade->id }}">{{ $grade->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label for="section_id" class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Class Section</label>
                            <select name="section_id" id="section_id" required class="w-full bg-white/50 border-slate-200 rounded-2xl focus:ring-emerald-500 focus:border-emerald-500 font-bold text-sm py-4 px-6 transition-all appearance-none cursor-pointer">
                                <option value="">Select Section</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-center pt-4">
                        <button type="submit" class="w-full max-w-md py-5 bg-slate-900 text-white font-black text-sm uppercase tracking-[0.2em] rounded-2xl hover:bg-slate-800 transition-all shadow-xl shadow-slate-200 flex items-center justify-center gap-3 group">
                            Open Master Sheet
                            <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 01-2-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Master Sheet Guide -->
        <div class="bg-slate-900 rounded-[2.5rem] p-10 overflow-hidden relative">
            <div class="absolute top-0 right-0 p-10 opacity-10">
                <svg class="w-40 h-40 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div class="relative z-10 max-w-xl">
                <h3 class="text-xl font-black text-white tracking-tight mb-4">Why use the Master Sheet?</h3>
                <ul class="space-y-4">
                    <li class="flex items-start gap-3">
                        <div class="w-5 h-5 rounded-full bg-emerald-500/20 flex items-center justify-center text-emerald-400 mt-0.5">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                        <p class="text-slate-400 text-sm font-semibold">Batch-process all student scores for a specific subject in one view.</p>
                    </li>
                    <li class="flex items-start gap-3">
                        <div class="w-5 h-5 rounded-full bg-emerald-500/20 flex items-center justify-center text-emerald-400 mt-0.5">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                        <p class="text-slate-400 text-sm font-semibold">Real-time validation ensures scores stay within assessment limits.</p>
                    </li>
                    <li class="flex items-start gap-3">
                        <div class="w-5 h-5 rounded-full bg-emerald-500/20 flex items-center justify-center text-emerald-400 mt-0.5">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                        <p class="text-slate-400 text-sm font-semibold">Instantly see class averages and grade distribution as you type.</p>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const yearSelect = document.getElementById('academic_year_id');
            const termSelect = document.getElementById('term_id');
            const gradeSelect = document.getElementById('grade_level_id');
            const sectionSelect = document.getElementById('section_id');
            const subjectSelect = document.getElementById('subject_id');

            yearSelect.addEventListener('change', function() {
                const yearId = this.value;
                if (!yearId) return;
                
                fetch(`{{ route('admin.gradebook.get-terms') }}?academic_year_id=${yearId}`)
                    .then(response => response.json())
                    .then(data => {
                        termSelect.innerHTML = '<option value="">Select Term</option>';
                        data.forEach(term => {
                            if (term.type !== 'yearly') {
                                termSelect.innerHTML += `<option value="${term.id}">${term.name}</option>`;
                            }
                        });
                    });
                
                resetDependentDropdowns();
            });

            gradeSelect.addEventListener('change', function() {
                const gradeId = this.value;
                const yearId = yearSelect.value;
                if (!gradeId || !yearId) return;

                // Load Sections
                fetch(`{{ route('admin.gradebook.get-sections') }}?academic_year_id=${yearId}&grade_level_id=${gradeId}`)
                    .then(response => response.json())
                    .then(data => {
                        sectionSelect.innerHTML = '<option value="">Select Section</option>';
                        data.forEach(section => {
                            sectionSelect.innerHTML += `<option value="${section.id}">${section.name}</option>`;
                        });
                    });
                
                // Load Subjects
                fetch(`{{ route('admin.gradebook.get-subjects') }}?academic_year_id=${yearId}&grade_level_id=${gradeId}`)
                    .then(response => response.json())
                    .then(data => {
                        subjectSelect.innerHTML = '<option value="">Select Subject</option>';
                        data.forEach(subject => {
                            subjectSelect.innerHTML += `<option value="${subject.id}">${subject.name}</option>`;
                        });
                    });
            });

            function resetDependentDropdowns() {
                sectionSelect.innerHTML = '<option value="">Select Section</option>';
                subjectSelect.innerHTML = '<option value="">Select Subject</option>';
            }
        });
    </script>
    @endpush
</x-admin-layout>
