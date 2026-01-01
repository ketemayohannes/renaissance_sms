<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full relative z-10">
            <span class="text-xl font-bold text-slate-800">Academic Intelligence</span>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.report-cards.settings') }}" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-600 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-50 transition-all shadow-sm flex items-center gap-2">
                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path></svg>
                    Interface Config
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-8 pb-12" x-data="academicReports()">
        <x-breadcrumb :items="[['label' => 'Academic Reports', 'url' => '#']]" />

        <!-- High-Impact Hero Section -->
        <div class="relative overflow-hidden rounded-[3rem] bg-slate-900 p-10 md:p-16 text-white shadow-2xl">
            <div class="absolute inset-0 bg-gradient-to-br from-indigo-600 via-violet-600 to-purple-800 opacity-90"></div>
            <div class="absolute -right-20 -top-20 w-80 h-80 bg-white/10 rounded-full blur-3xl animate-pulse"></div>
            <div class="absolute -left-20 -bottom-20 w-60 h-60 bg-indigo-400/20 rounded-full blur-2xl"></div>
            
            <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-8">
                <div class="max-w-xl">
                    <h2 class="text-3xl md:text-5xl font-black mb-4 uppercase tracking-tighter leading-none italic">Report Engine</h2>
                    <p class="text-indigo-100/80 text-sm font-bold uppercase tracking-widest leading-relaxed">Synthesis of student performance into professional certification and analytical matrices.</p>
                </div>
                <div class="bg-white/10 backdrop-blur-xl rounded-[2rem] p-6 border border-white/20 shadow-2xl">
                    <p class="text-[10px] font-black text-indigo-200 uppercase tracking-[0.3em] mb-1">Active Infrastructure</p>
                    <p class="font-black text-2xl tracking-tighter italic">{{ $academicYears->firstWhere('is_active', true)?->name ?? 'N/A' }}</p>
                </div>
            </div>
        </div>

        <!-- Analytical Configuration Matrix -->
        <form id="reportForm" action="{{ route('admin.academic-reports.show') }}" method="GET" class="space-y-8">
            <div class="glass-panel overflow-hidden border-white/40 shadow-2xl">
                <!-- Data Scope Section -->
                <div class="p-8 md:p-12 border-b border-slate-100">
                    <div class="flex items-center gap-4 mb-10">
                        <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 text-indigo-600 flex items-center justify-center shadow-inner">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-slate-900 uppercase tracking-tight">Scope Calibration</h3>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1 italic">Define the target environment and timeline</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                        <div class="space-y-3">
                            <label class="px-1 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Academic Cycle</label>
                            <select name="academic_year_id" x-model="selectedYear" @change="loadTerms()" class="premium-select w-full" required>
                                @foreach($academicYears as $year)
                                    <option value="{{ $year->id }}" {{ $year->is_active ? 'selected' : '' }}>{{ $year->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="space-y-3">
                            <label class="px-1 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Target Term</label>
                            <select name="term_id" x-model="selectedTerm" class="premium-select w-full" required>
                                <option value="">Select Timeline</option>
                                <template x-for="term in terms" :key="term.id">
                                    <option :value="term.id" x-text="term.name"></option>
                                </template>
                            </select>
                        </div>

                        <div class="space-y-3" x-show="!hideGrade" x-transition>
                            <label class="px-1 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Deployment Grade</label>
                            <select name="grade_level_id" x-model="selectedGrade" @change="loadSections(); loadSubjects()" class="premium-select w-full" :required="!hideGrade">
                                <option value="">Select Level</option>
                                @foreach($gradeLevels as $grade)
                                    <option value="{{ $grade->id }}">{{ $grade->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="space-y-3" x-show="!hideSection" x-transition>
                            <label class="px-1 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Target Section</label>
                            <select name="section_id" x-model="selectedSection" class="premium-select w-full" :required="!hideSection && !disableSection" :disabled="disableSection">
                                <option value="">Select Unit</option>
                                <template x-for="section in sections" :key="section.id">
                                    <option :value="section.id" x-text="section.name"></option>
                                </template>
                            </select>
                        </div>
                        
                        <div class="space-y-3" x-show="hideGrade" x-transition x-cloak>
                            <label class="px-1 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Division Matrix</label>
                            <select name="division_id" x-model="selectedDivision" class="premium-select w-full">
                                <option value="">All Divisions</option>
                                @foreach($divisions as $division)
                                    <option value="{{ $division->id }}">{{ $division->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <!-- Subject Injection for Analysis -->
                    <div x-show="reportType === 'grade_subject_analysis'" x-transition x-cloak class="mt-10 p-6 bg-indigo-50/50 rounded-[2rem] border border-indigo-100 animate-fade-in shadow-inner">
                        <label class="px-1 text-[10px] font-black text-indigo-400 uppercase tracking-[0.2em] mb-4 block">Analytical Variable: Subject</label>
                        <select name="subject_id" x-model="selectedSubject" class="premium-select w-full max-w-lg border-indigo-200" :required="reportType === 'grade_subject_analysis'">
                            <option value="">Select Discipline for Analysis</option>
                            <template x-for="subject in subjects" :key="subject.id">
                                <option :value="subject.id" x-text="subject.name"></option>
                            </template>
                        </select>
                    </div>
                </div>

                <!-- Modality Selection -->
                <div class="p-8 md:p-12 bg-slate-50/40">
                    <div class="flex items-center gap-4 mb-10">
                        <div class="w-12 h-12 rounded-2xl bg-purple-500/10 text-purple-600 flex items-center justify-center shadow-inner">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-slate-900 uppercase tracking-tight">Report Modality</h3>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1 italic">Choose the final output format</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6">
                        @foreach([
                            ['val' => 'report_card', 'label' => 'Report Card', 'desc' => 'Individual Student certification', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'color' => 'indigo'],
                            ['val' => 'roster', 'label' => 'Term Roster', 'desc' => 'Comprehensive class marksheet', 'icon' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'color' => 'emerald'],
                            ['val' => 'result_analysis', 'label' => 'Performance', 'desc' => 'Section-wise grade distribution', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10', 'color' => 'rose'],
                            ['val' => 'grade_subject_analysis', 'label' => 'Subject Intel', 'desc' => 'Cross-section subject benchmarks', 'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253', 'color' => 'purple'],
                            ['val' => 'consolidated_matrix', 'label' => 'School Matrix', 'desc' => 'Global grade level aggregation', 'icon' => 'M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z', 'color' => 'amber']
                        ] as $item)
                        <label class="group relative cursor-pointer">
                            <input type="radio" name="report_type" value="{{ $item['val'] }}" x-model="reportType" class="sr-only peer" required>
                            <div class="h-full p-6 rounded-[2rem] border-2 border-slate-100 bg-white transition-all duration-500 peer-checked:border-{{ $item['color'] }}-500 peer-checked:bg-{{ $item['color'] }}-50 hover:shadow-2xl hover:-translate-y-2">
                                <div class="w-12 h-12 rounded-2xl bg-{{ $item['color'] }}-500 flex items-center justify-center mb-6 shadow-xl shadow-{{ $item['color'] }}-200">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"></path></svg>
                                </div>
                                <h4 class="font-black text-slate-800 tracking-tight leading-none group-hover:text-{{ $item['color'] }}-700 transition-colors uppercase text-xs">{{ $item['label'] }}</h4>
                                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-3 leading-relaxed">{{ $item['desc'] }}</p>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>

                <!-- Footer Engagement -->
                <div class="p-8 md:p-12 bg-white border-t border-slate-100 flex flex-col md:flex-row items-center justify-between gap-8">
                    <div class="flex items-center gap-4 group">
                        <div class="w-12 h-12 rounded-full bg-slate-50 flex items-center justify-center text-amber-500 group-hover:scale-110 transition-transform">
                             <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] leading-relaxed max-w-xs italic">
                            Verification is mandatory: Confirm grade finalization status before execution.
                        </p>
                    </div>
                    <button type="submit" class="vibrant-btn-blue h-16 px-12 text-lg shadow-2xl hover:scale-105 transition-all">
                        <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04M12 2.944V22m0-19.056c1.1 0 2.1.2 3 .6a11.955 11.955 0 018.618 3.04M12 2.944a11.955 11.955 0 00-8.618 3.04"></path></svg>
                        Initiate Generator
                    </button>
                </div>
            </div>
        </form>

        <!-- Dynamic Information Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @foreach([
                ['label' => 'Active Roster', 'val' => \App\Models\Student::where('is_active', true)->count(), 'unit' => 'Students', 'color' => 'indigo'],
                ['label' => 'Operational Units', 'val' => \App\Models\Section::whereHas('academicYear', fn($q) => $q->where('is_active', true))->count(), 'unit' => 'Sections', 'color' => 'emerald'],
                ['label' => 'Academic Phases', 'val' => \App\Models\Term::whereHas('academicYear', fn($q) => $q->where('is_active', true))->count(), 'unit' => 'Terms', 'color' => 'purple']
            ] as $stat)
            <div class="glass-panel p-8 group hover:shadow-2xl transition-all">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-[0.3em] group-hover:text-{{ $stat['color'] }}-500 transition-colors">{{ $stat['label'] }}</span>
                    <div class="w-1.5 h-1.5 rounded-full bg-{{ $stat['color'] }}-400 animate-pulse"></div>
                </div>
                <div class="flex items-baseline gap-2">
                    <span class="text-4xl font-black text-slate-900 italic tracking-tighter">{{ $stat['val'] }}</span>
                    <span class="text-xs font-black text-slate-400 uppercase tracking-widest">{{ $stat['unit'] }}</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    @push('scripts')
    <script>
        function academicReports() {
            return {
                selectedYear: '{{ $academicYears->firstWhere('is_active', true)?->id ?? '' }}',
                selectedTerm: '',
                selectedGrade: '',
                selectedSection: '',
                selectedDivision: '',
                selectedSubject: '',
                reportType: '',
                terms: [],
                sections: [],
                subjects: [],

                get hideGrade() { return this.reportType === 'consolidated_matrix'; },
                get hideSection() { return this.reportType === 'consolidated_matrix'; },
                get disableSection() { return this.reportType === 'grade_subject_analysis'; },

                init() {
                    if (this.selectedYear) { this.loadTerms(); }
                    
                    this.$el.closest('form')?.addEventListener('submit', (e) => {
                        if (this.reportType === 'grade_subject_analysis') {
                            e.preventDefault();
                            const params = new URLSearchParams(new FormData(e.target)).toString();
                            window.location.href = `{{ route('admin.academic-reports.subject-analysis') }}?${params}`;
                        } else if (this.reportType === 'consolidated_matrix') {
                            e.preventDefault();
                            const params = new URLSearchParams(new FormData(e.target)).toString();
                            window.location.href = `{{ route('admin.academic-reports.grade-matrix') }}?${params}`;
                        }
                    });
                },

                async loadTerms() {
                    if (!this.selectedYear) return;
                    try {
                        const response = await fetch(`{{ route('admin.gradebook.get-terms') }}?academic_year_id=${this.selectedYear}`);
                        this.terms = await response.json();
                    } catch (error) { console.error('Error loading terms:', error); }
                },

                async loadSections() {
                    if (!this.selectedYear || !this.selectedGrade) return;
                    try {
                        const response = await fetch(`{{ route('admin.gradebook.get-sections') }}?academic_year_id=${this.selectedYear}&grade_level_id=${this.selectedGrade}`);
                        this.sections = await response.json();
                    } catch (error) { console.error('Error loading sections:', error); }
                },

                async loadSubjects() {
                    if (!this.selectedYear || !this.selectedGrade) return;
                    try {
                        const response = await fetch(`{{ route('admin.gradebook.get-subjects') }}?academic_year_id=${this.selectedYear}&grade_level_id=${this.selectedGrade}`);
                        this.subjects = await response.json();
                    } catch (error) { console.error('Error loading subjects:', error); }
                }
            }
        }
    </script>
    @endpush
</x-admin-layout>
