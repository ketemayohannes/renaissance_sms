<x-admin-layout>
    <x-slot name="header">Gradebook Management</x-slot>

    <div class="space-y-8">
        <!-- Breadcrumb & Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <x-breadcrumb :items="[
                    ['label' => 'Gradebook', 'url' => '#']
                ]" />
                <h1 class="text-4xl font-black text-slate-900 tracking-tight mt-2">Gradebook</h1>
            </div>
            <div class="flex items-center gap-3">
                <div class="px-4 py-2 bg-indigo-50 rounded-2xl border border-indigo-100 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-indigo-600 flex items-center justify-center text-white shadow-lg shadow-indigo-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <span class="block text-[10px] font-black text-indigo-900 uppercase tracking-widest">Active System</span>
                        <span class="block text-xs font-bold text-indigo-600/80 mt-0.5">Automated Calculation On</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Selection Panel -->
        <div class="bg-white/40 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-xl shadow-slate-200/50 p-10">
            <div class="w-full">
                <div class="text-center mb-10">
                    <div class="w-20 h-20 bg-slate-900 rounded-[2rem] flex items-center justify-center text-white mx-auto mb-6 shadow-2xl shadow-slate-200">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                    </div>
                    <h2 class="text-3xl font-black text-slate-900 tracking-tight">Access Gradebook</h2>
                    <p class="text-slate-500 font-semibold mt-2">Select the academic parameters to begin entering or viewing grades.</p>
                </div>

                <form action="{{ route('admin.gradebook.entry') }}" method="GET" class="space-y-8">
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-6 items-end">
                        <div class="space-y-2">
                            <label for="academic_year_id" class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Academic Year</label>
                            <select name="academic_year_id" id="academic_year_id" required class="w-full bg-white/50 border-slate-200 rounded-2xl focus:ring-indigo-500 focus:border-indigo-500 font-bold text-sm py-4 px-6 transition-all appearance-none cursor-pointer">
                                @foreach($academicYears as $year)
                                    <option value="{{ $year->id }}" {{ $year->is_current ? 'selected' : '' }}>{{ $year->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label for="term_id" class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Academic Term</label>
                            <select name="term_id" id="term_id" required class="w-full bg-white/50 border-slate-200 rounded-2xl focus:ring-indigo-500 focus:border-indigo-500 font-bold text-sm py-4 px-6 transition-all appearance-none cursor-pointer">
                                @foreach($terms as $term)
                                    <option value="{{ $term->id }}" {{ $term->is_current ? 'selected' : '' }}>{{ $term->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label for="grade_level_id" class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Grade Level</label>
                            <select name="grade_level_id" id="grade_level_id" required class="w-full bg-white/50 border-slate-200 rounded-2xl focus:ring-indigo-500 focus:border-indigo-500 font-bold text-sm py-4 px-6 transition-all appearance-none cursor-pointer">
                                <option value="">Select Grade</option>
                                @foreach($gradeLevels as $grade)
                                    <option value="{{ $grade->id }}">{{ $grade->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label for="section_id" class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Class Section</label>
                            <select name="section_id" id="section_id" required class="w-full bg-white/50 border-slate-200 rounded-2xl focus:ring-indigo-500 focus:border-indigo-500 font-bold text-sm py-4 px-6 transition-all appearance-none cursor-pointer">
                                <option value="">Select Section</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label for="subject_id" class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Subject</label>
                            <select name="subject_id" id="subject_id" required class="w-full bg-white/50 border-slate-200 rounded-2xl focus:ring-indigo-500 focus:border-indigo-500 font-bold text-sm py-4 px-6 transition-all appearance-none cursor-pointer">
                                <option value="">Select Subject</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-center pt-4">
                        <button type="submit" class="w-full max-w-md py-5 bg-slate-900 text-white font-black text-sm uppercase tracking-[0.2em] rounded-2xl hover:bg-slate-800 transition-all shadow-xl shadow-slate-200 flex items-center justify-center gap-3 group">
                            Initialize Gradebook
                            <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Quick Info Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white/80 backdrop-blur-xl rounded-[2rem] p-8 border border-white shadow-xl shadow-slate-200/50 flex items-start gap-6 group hover:scale-[1.02] transition-transform">
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-600 shrink-0 shadow-sm border border-emerald-100 group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path></svg>
                </div>
                <div>
                    <h3 class="font-black text-slate-900 uppercase tracking-widest text-[10px]">Auto-Calibration</h3>
                    <p class="text-xs text-slate-500 font-semibold mt-1 leading-relaxed">System automatically applies pre-configured weights based on templates.</p>
                </div>
            </div>
            <div class="bg-white/80 backdrop-blur-xl rounded-[2rem] p-8 border border-white shadow-xl shadow-slate-200/50 flex items-start gap-6 group hover:scale-[1.02] transition-transform">
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600 shrink-0 shadow-sm border border-indigo-100 group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                </div>
                <div>
                    <h3 class="font-black text-slate-900 uppercase tracking-widest text-[10px]">Real-Time Pulse</h3>
                    <p class="text-xs text-slate-500 font-semibold mt-1 leading-relaxed">Grades are processed instantly, updating class averages and student rankings.</p>
                </div>
            </div>
            <div class="bg-white/80 backdrop-blur-xl rounded-[2rem] p-8 border border-white shadow-xl shadow-slate-200/50 flex items-start gap-6 group hover:scale-[1.02] transition-transform">
                <div class="w-12 h-12 rounded-2xl bg-violet-50 flex items-center justify-center text-violet-600 shrink-0 shadow-sm border border-violet-100 group-hover:bg-violet-600 group-hover:text-white transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
                <div>
                    <h3 class="font-black text-slate-900 uppercase tracking-widest text-[10px]">Audit Logs</h3>
                    <p class="text-xs text-slate-500 font-semibold mt-1 leading-relaxed">Every grade change is tracked and attributed to the specific faculty member.</p>
                </div>
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
                            termSelect.innerHTML += `<option value="${term.id}">${term.name}</option>`;
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
