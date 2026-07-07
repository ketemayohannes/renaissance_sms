<x-student-layout header="My Gradebook">

    <div class="space-y-8">
        
        <!-- Grades Header Banner -->
        <div class="relative overflow-hidden rounded-[3rem] bg-slate-900 p-8 md:p-12 shadow-2xl glass-panel border-0">
            <div class="absolute inset-0 bg-gradient-to-br from-indigo-600/30 via-transparent to-purple-500/30 opacity-70"></div>
            <div class="absolute -top-24 -right-24 w-96 h-96 bg-indigo-500/20 rounded-full blur-3xl mix-blend-screen pointer-events-none"></div>
            
            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <span class="px-3 py-1 bg-indigo-500/30 text-indigo-200 border border-indigo-400/30 rounded-xl text-xs font-black uppercase tracking-widest">Academic Report</span>
                    <h1 class="text-3xl md:text-5xl font-black text-white font-heading tracking-tight mt-3 mb-2">My Gradebook</h1>
                    <p class="text-indigo-100/80 text-base font-medium">
                        {{ $student->full_name }} • <span class="text-indigo-300 font-extrabold">{{ $enrollment->section->gradeLevel->name }} - Section {{ $enrollment->section->name }}</span>
                    </p>
                </div>
                
                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                    <!-- Filter Dropdown -->
                    <form action="{{ route('student.grades.index') }}" method="GET" class="flex items-center gap-2">
                        <select name="period" onchange="this.form.submit()" class="rounded-2xl border-white/20 bg-white/10 text-white font-bold text-sm focus:ring-indigo-500 focus:border-indigo-500 shadow-lg backdrop-blur-md transition-all px-4 py-2.5">
                            <option value="all" class="text-slate-900" {{ $selectedPeriod == 'all' ? 'selected' : '' }}>All Records</option>
                            <option disabled class="text-slate-400">── Quarters ──</option>
                            @foreach($quarters as $quarter)
                                <option value="term_{{ $quarter->id }}" class="text-slate-900" {{ $selectedPeriod == 'term_'.$quarter->id ? 'selected' : '' }}>{{ $quarter->name }}</option>
                            @endforeach
                            <option disabled class="text-slate-400">── Semesters ──</option>
                            @foreach($semesters as $semester)
                                <option value="semester_{{ $semester->id }}" class="text-slate-900" {{ $selectedPeriod == 'semester_'.$semester->id ? 'selected' : '' }}>{{ $semester->name }}</option>
                            @endforeach
                            <option disabled class="text-slate-400">── Yearly ──</option>
                            <option value="yearly" class="text-slate-900" {{ $selectedPeriod == 'yearly' ? 'selected' : '' }}>Yearly Report</option>
                        </select>
                    </form>

                    <!-- Academic Year Badge -->
                    <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-2xl p-3 flex items-center gap-3 shadow-lg">
                        <div class="w-10 h-10 rounded-xl bg-indigo-500/30 flex items-center justify-center text-indigo-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <div>
                            <span class="block text-[8px] font-black text-indigo-200 uppercase tracking-widest leading-none mb-1">Academic Year</span>
                            <span class="text-xs font-black text-white uppercase tracking-wider">
                                {{ $enrollment->academicYear->name }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Period Action Row -->
        <div class="mb-6 flex justify-between items-center px-4">
            <h2 class="text-xl font-black text-slate-800 dark:text-slate-100 font-heading uppercase tracking-widest">{{ $periodName }}</h2>
            @if($selectedPeriod !== 'all')
                <a href="{{ route('student.grades.download', ['period' => $selectedPeriod]) }}" class="inline-flex items-center px-5 py-2.5 bg-gradient-to-r from-indigo-500 to-indigo-600 border border-transparent rounded-2xl font-bold text-xs text-white uppercase tracking-widest hover:from-indigo-600 hover:to-indigo-700 active:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition duration-150 shadow-md shadow-indigo-200 dark:shadow-none">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                    </svg> Download PDF
                </a>
            @endif
        </div>

        <!-- Report / Assessment Tab Switcher -->
        <div x-data="{ view: 'report' }">
            <div class="flex items-center gap-2 px-2 mb-6">
                <button type="button" @click="view = 'report'"
                        :class="view === 'report' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-100 dark:shadow-none' : 'bg-white dark:bg-slate-900 text-slate-500 dark:text-slate-400 border border-slate-100 dark:border-slate-800'"
                        class="px-5 py-2.5 rounded-2xl font-black text-xs uppercase tracking-widest transition-all">
                    Report
                </button>
                <button type="button" @click="view = 'assessment'"
                        :class="view === 'assessment' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-100 dark:shadow-none' : 'bg-white dark:bg-slate-900 text-slate-500 dark:text-slate-400 border border-slate-100 dark:border-slate-800'"
                        class="px-5 py-2.5 rounded-2xl font-black text-xs uppercase tracking-widest transition-all">
                    Assessment
                </button>
            </div>

            <!-- REPORT TAB: one row per subject, total score + standing — same numbers as the report card and admin profile. -->
            <div x-show="view === 'report'" x-cloak class="w-full space-y-6 mt-4">
                @forelse($grades as $termName => $termGrades)
                    @php $termRecord = isset($termRecords) ? ($termRecords[$termName] ?? null) : null; @endphp
                    <div class="glass-panel border-white bg-white/60 dark:bg-slate-900/60 p-8 rounded-[2.5rem] shadow-sm flex flex-col gap-6" x-data="{ expanded: {{ $loop->first ? 'true' : 'false' }} }">
                        @include('student.grades.partials.term-card-header', ['termName' => $termName, 'termRecord' => $termRecord, 'activeTerm' => $activeTerm ?? null])
                        <div x-show="expanded" x-collapse x-cloak>
                            @include('components.grade-history.report-table', ['marks' => $termGrades])
                        </div>
                    </div>
                @empty
                    <div class="glass-panel border-white bg-white/60 dark:bg-slate-900/60 p-12 text-center text-slate-500 dark:text-slate-400">
                        <p class="text-base font-bold">No grade records found for this academic year.</p>
                    </div>
                @endforelse
            </div>

            <!-- ASSESSMENT TAB: quarters only — the raw per-component entries (Quiz/Mid/Final). -->
            <div x-show="view === 'assessment'" x-cloak class="w-full space-y-6 mt-4">
                @php $quarterGrades = $grades->filter(fn($v, $k) => str_starts_with($k, 'Quarter')); @endphp
                @forelse($quarterGrades as $termName => $termGrades)
                    @php $termRecord = isset($termRecords) ? ($termRecords[$termName] ?? null) : null; @endphp
                    <div class="glass-panel border-white bg-white/60 dark:bg-slate-900/60 p-8 rounded-[2.5rem] shadow-sm flex flex-col gap-6" x-data="{ expanded: {{ $loop->first ? 'true' : 'false' }} }">
                        @include('student.grades.partials.term-card-header', ['termName' => $termName, 'termRecord' => $termRecord, 'activeTerm' => $activeTerm ?? null])
                        <div x-show="expanded" x-collapse x-cloak>
                            @include('components.grade-history.assessment-table', ['marks' => $termGrades])
                        </div>
                    </div>
                @empty
                    <div class="glass-panel border-white bg-white/60 dark:bg-slate-900/60 p-12 text-center text-slate-500 dark:text-slate-400">
                        <p class="text-base font-bold">No quarter assessment data for the selected period.</p>
                    </div>
                @endforelse
            </div>
        </div>

    </div>
</x-student-layout>
