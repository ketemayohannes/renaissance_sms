<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full relative z-10">
            <span class="text-xl font-bold text-slate-800">Academic Reports</span>
            @unlessrole('Principal')
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.academic-reports.settings') }}" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-600 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-emerald-50 hover:text-emerald-600 transition-all shadow-sm flex items-center gap-2 group">
                    <svg class="w-4 h-4 text-emerald-500 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Roster Config
                </a>
                <a href="{{ route('admin.report-cards.settings') }}" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-600 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-50 transition-all shadow-sm flex items-center gap-2">
                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path></svg>
                    Interface Config
                </a>
            </div>
            @endunlessrole
        </div>
    </x-slot>

    <div class="space-y-8 pb-12">
        <x-breadcrumb :items="[['label' => 'Academic Reports', 'url' => '#']]" />

        <!-- High-Impact Compact Hero Section -->
        <div class="relative overflow-hidden rounded-[2.5rem] bg-slate-900 px-8 py-10 md:px-12 md:py-12 text-white shadow-2xl">
            <div class="absolute inset-0 bg-gradient-to-br from-indigo-600 via-indigo-700 to-violet-800 opacity-95"></div>
            <div class="absolute -right-20 -top-20 w-80 h-80 bg-white/10 rounded-full blur-3xl animate-pulse"></div>
            <div class="absolute -left-20 -bottom-20 w-60 h-60 bg-indigo-400/20 rounded-full blur-2xl"></div>
            
            <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                <div class="max-w-xl">
                    <div class="inline-flex items-center gap-2 px-3 py-1 bg-white/10 backdrop-blur-md rounded-full border border-white/20 mb-4">
                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-400 animate-ping"></span>
                        <span class="text-[9px] font-black uppercase tracking-[0.2em] text-indigo-100">Reports Engine</span>
                    </div>
                    <h2 class="text-3xl md:text-4xl font-black mb-3 uppercase tracking-tighter leading-none italic font-heading">Academic Reports</h2>
                    <p class="text-indigo-100/70 text-[11px] font-bold uppercase tracking-widest leading-relaxed">Generate and download comprehensive student academic reports and rosters.</p>
                </div>
                <div class="bg-white/10 backdrop-blur-xl rounded-[2rem] p-5 border border-white/20 shadow-2xl min-w-[200px]">
                    <p class="text-[9px] font-black text-indigo-200 uppercase tracking-[0.3em] mb-1">Active Year</p>
                    <p class="font-black text-xl tracking-tighter italic">{{ $academicYears->firstWhere('is_active', true)?->name ?? 'N/A' }}</p>
                </div>
            </div>
        </div>

        <!-- Analytical Configuration Matrix -->
        <form id="reportForm" x-data="academicReports()" @submit.prevent="submitReport($event)" action="{{ route('admin.academic-reports.show') }}" method="GET" class="space-y-8">
            <div class="bg-white/40 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-2xl shadow-slate-200/50 overflow-hidden divide-y divide-slate-100">
                <!-- Data Scope Section -->
                <div class="p-8 md:p-10">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shadow-sm border border-indigo-100/50">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-black text-slate-900 uppercase tracking-tight">Report Filters</h3>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5 italic text-opacity-80">Select the year, term, and division for the report</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div class="space-y-2">
                            <label class="px-1 text-[10px] font-black text-slate-400 uppercase tracking-widest">Academic Year</label>
                            <select name="academic_year_id" x-model="selectedYear" @change="loadTerms()" class="w-full bg-white/50 border-slate-200 rounded-2xl focus:ring-indigo-500 focus:border-indigo-500 font-bold text-sm py-3 px-4 transition-all" required>
                                @foreach($academicYears as $year)
                                    <option value="{{ $year->id }}" {{ $year->is_active ? 'selected' : '' }}>{{ $year->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="space-y-2">
                            <label class="px-1 text-[10px] font-black text-slate-400 uppercase tracking-widest">Term / Quarter</label>
                            <select name="term_id" x-model="selectedTerm" class="w-full bg-white/50 border-slate-200 rounded-2xl focus:ring-indigo-500 focus:border-indigo-500 font-bold text-sm py-3 px-4 transition-all" required>
                                <option value="">Select Timeline</option>
                                <template x-for="term in terms" :key="term.id">
                                    <option :value="term.id" x-text="term.name"></option>
                                </template>
                            </select>
                        </div>

                        <div class="space-y-2" x-show="!hideGrade" x-transition>
                            <label class="px-1 text-[10px] font-black text-slate-400 uppercase tracking-widest">Grade Level</label>
                            <select name="grade_level_id" x-model="selectedGrade" @change="loadSections(); loadSubjects()" class="w-full bg-white/50 border-slate-200 rounded-2xl focus:ring-indigo-500 focus:border-indigo-500 font-bold text-sm py-3 px-4 transition-all" :required="!hideGrade">
                                <option value="">Select Level</option>
                                @foreach($gradeLevels as $grade)
                                    <option value="{{ $grade->id }}">{{ $grade->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="space-y-2" x-show="!hideSection" x-transition>
                            <label class="px-1 text-[10px] font-black text-slate-400 uppercase tracking-widest">Select Section</label>
                            <select name="section_id" x-model="selectedSection" class="w-full bg-white/50 border-slate-200 rounded-2xl focus:ring-indigo-500 focus:border-indigo-500 font-bold text-sm py-3 px-4 transition-all" :required="!hideSection && !disableSection" :disabled="disableSection">
                                <option value="">Select Unit</option>
                                <template x-for="section in sections" :key="section.id">
                                    <option :value="section.id" x-text="section.name"></option>
                                </template>
                            </select>
                        </div>
                        
                        <div class="space-y-2" x-show="hideGrade" x-transition x-cloak>
                            <label class="px-1 text-[10px] font-black text-slate-400 uppercase tracking-widest">Division</label>
                            <select name="division_id" x-model="selectedDivision" class="w-full bg-white/50 border-slate-200 rounded-2xl focus:ring-indigo-500 focus:border-indigo-500 font-bold text-sm py-3 px-4 transition-all">
                                <option value="">All Divisions</option>
                                @foreach($divisions as $division)
                                    <option value="{{ $division->id }}">{{ $division->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div x-show="reportType === 'grade_subject_analysis'" x-transition x-cloak class="mt-8 p-6 bg-indigo-50/30 rounded-3xl border border-indigo-100 shadow-sm">
                        <label class="px-1 text-[10px] font-black text-indigo-400 uppercase tracking-widest mb-3 block">Selected Subject</label>
                        <select name="subject_id" x-model="selectedSubject" class="w-full max-w-lg bg-white border-slate-200 rounded-2xl focus:ring-indigo-500 focus:border-indigo-500 font-bold text-sm py-3 px-4 transition-all" :required="reportType === 'grade_subject_analysis'">
                            <option value="">Select Subject for Analysis</option>
                            <template x-for="subject in subjects" :key="subject.id">
                                <option :value="subject.id" x-text="subject.name"></option>
                            </template>
                        </select>
                    </div>
                </div>

                <!-- Modality Selection -->
                <div class="p-8 md:p-10 bg-slate-50/30">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center shadow-sm border border-purple-100/50">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-black text-slate-900 uppercase tracking-tight">Report Type</h3>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5 italic text-opacity-80">Choose the type of report you want to generate</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-5">
                        @php
                            $colorMaps = [
                                'indigo' => [
                                    'peer' => 'peer-checked:border-indigo-500 peer-checked:bg-indigo-50/50 peer-checked:shadow-indigo-100/50',
                                    'icon' => 'bg-indigo-500 shadow-indigo-200',
                                    'text' => 'group-hover:text-indigo-700'
                                ],
                                'emerald' => [
                                    'peer' => 'peer-checked:border-emerald-500 peer-checked:bg-emerald-50/50 peer-checked:shadow-emerald-100/50',
                                    'icon' => 'bg-emerald-500 shadow-emerald-200',
                                    'text' => 'group-hover:text-emerald-700'
                                ],
                                'rose' => [
                                    'peer' => 'peer-checked:border-rose-500 peer-checked:bg-rose-50/50 peer-checked:shadow-rose-100/50',
                                    'icon' => 'bg-rose-500 shadow-rose-200',
                                    'text' => 'group-hover:text-rose-700'
                                ],
                                'purple' => [
                                    'peer' => 'peer-checked:border-purple-500 peer-checked:bg-purple-50/50 peer-checked:shadow-purple-100/50',
                                    'icon' => 'bg-purple-500 shadow-purple-200',
                                    'text' => 'group-hover:text-purple-700'
                                ],
                                'amber' => [
                                    'peer' => 'peer-checked:border-amber-500 peer-checked:bg-amber-50/50 peer-checked:shadow-amber-100/50',
                                    'icon' => 'bg-amber-500 shadow-amber-200',
                                    'text' => 'group-hover:text-amber-700'
                                ],
                            ];
                        @endphp

                        @foreach(collect([
                            ['val' => 'report_card', 'label' => 'Report Card', 'desc' => 'Individual student report cards', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'color' => 'indigo'],
                            ['val' => 'roster', 'label' => 'Term Roster', 'desc' => 'Full class marksheet', 'icon' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'color' => 'emerald'],
                            ['val' => 'result_analysis', 'label' => 'Statistics', 'desc' => 'View grade distribution by section', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10', 'color' => 'rose'],
                            ['val' => 'grade_subject_analysis', 'label' => 'Subject Analysis', 'desc' => 'Subject performance comparison', 'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253', 'color' => 'purple'],
                            ['val' => 'consolidated_matrix', 'label' => 'School Matrix', 'desc' => 'Summary for the entire grade level', 'icon' => 'M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z', 'color' => 'amber']
                        ])->filter(fn($item) => !($item['val'] === 'report_card' && auth()->user()->hasRole('Principal'))) as $item)
                        <label class="group relative cursor-pointer">
                            <input type="radio" name="report_type" value="{{ $item['val'] }}" x-model="reportType" class="sr-only peer" required>
                            <div class="h-full p-5 rounded-[2rem] border-2 border-slate-100 bg-white shadow-sm transition-all duration-300 {{ $colorMaps[$item['color']]['peer'] }} hover:shadow-lg">
                                <div class="w-10 h-10 rounded-xl {{ $colorMaps[$item['color']]['icon'] }} flex items-center justify-center mb-5 shadow-lg transition-transform group-hover:scale-110">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"></path></svg>
                                </div>
                                <h4 class="font-black text-slate-800 tracking-tight leading-none {{ $colorMaps[$item['color']]['text'] }} transition-colors uppercase text-[10px]">{{ $item['label'] }}</h4>
                                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-2 leading-relaxed opacity-60">{{ $item['desc'] }}</p>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>

                <!-- Footer Engagement -->
                <div class="px-8 py-6 bg-white flex flex-col md:flex-row items-center justify-between gap-6">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-amber-50 flex items-center justify-center text-amber-500">
                             <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest italic">
                            Please verify all filters before generating.
                        </p>
                    </div>
                    <button type="submit" class="inline-flex items-center px-8 py-4 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-black uppercase tracking-widest rounded-2xl transition-all shadow-lg shadow-indigo-100 gap-3 group">
                        <svg class="w-5 h-5 group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04M12 2.944V22m0-19.056c1.1 0 2.1.2 3 .6a11.955 11.955 0 018.618 3.04M12 2.944a11.955 11.955 0 00-8.618 3.04"></path></svg>
                        Initiate Report
                    </button>
                </div>
            </div>
        </form>

        <!-- Dynamic Information Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach([
                ['label' => 'Active Students', 'val' => \App\Models\Student::where('is_active', true)->count(), 'unit' => 'Students', 'color' => 'indigo', 'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
                ['label' => 'Active Sections', 'val' => \App\Models\Section::whereHas('academicYear', fn($q) => $q->where('is_active', true))->count(), 'unit' => 'Sections', 'color' => 'emerald', 'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
                ['label' => 'Active Terms', 'val' => \App\Models\Term::whereHas('academicYear', fn($q) => $q->where('is_active', true))->count(), 'unit' => 'Terms', 'color' => 'purple', 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z']
            ] as $stat)
            <div class="bg-white/60 backdrop-blur-md p-6 rounded-3xl border border-white shadow-sm flex items-center gap-5 group hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div class="w-12 h-12 rounded-2xl bg-{{ $stat['color'] }}-50 flex items-center justify-center text-{{ $stat['color'] }}-600 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $stat['icon'] }}"></path></svg>
                </div>
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-1 group-hover:text-{{ $stat['color'] }}-500 transition-colors">{{ $stat['label'] }}</p>
                    <div class="flex items-baseline gap-2">
                        <p class="text-2xl font-black text-slate-900 tracking-tighter">{{ $stat['val'] }}</p>
                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">{{ $stat['unit'] }}</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    @push('scripts')
    <script>
        function academicReports() {
            return {
                selectedYear: '{{ $academicYears->firstWhere('is_active', true)?->id ?? $academicYears->first()?->id ?? '' }}',
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
                    if (this.selectedYear) { 
                        this.loadTerms(); 
                    }
                },

                submitReport(e) {
                    const formData = new FormData(e.target);
                    const params = new URLSearchParams(formData).toString();

                    if (this.reportType === 'grade_subject_analysis') {
                        window.location.href = `{{ route('admin.academic-reports.subject-analysis') }}?${params}`;
                    } else if (this.reportType === 'consolidated_matrix') {
                        window.location.href = `{{ route('admin.academic-reports.grade-matrix') }}?${params}`;
                    } else {
                        // Standard reports (roster, report card, etc.)
                        e.target.submit();
                    }
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
