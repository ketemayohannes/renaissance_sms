<x-parent-layout header="{{ $student->full_name }}'s Academic Report">
    <div class="space-y-8" x-data="{ mobileDrawerOpen: false, selectedPeriodTemp: '{{ $selectedPeriod }}' }">
        <!-- Modern Premium Banner -->
        <div class="relative bg-gradient-to-r from-violet-600 via-indigo-600 to-indigo-700 rounded-3xl p-6 lg:p-8 text-white overflow-hidden shadow-lg shadow-indigo-100 dark:shadow-none">
            <!-- Translucent decorative glow shapes -->
            <div class="absolute -right-10 -top-10 w-48 h-48 bg-white/10 rounded-full blur-2xl"></div>
            <div class="absolute -right-20 -bottom-20 w-64 h-64 bg-indigo-500/20 rounded-full blur-3xl"></div>
            
            <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-8">
                <!-- Left: Student Info Summary -->
                <div class="flex items-center gap-5">
                    <div class="relative flex-shrink-0">
                        <div class="w-16 h-16 rounded-2xl bg-white/10 backdrop-blur-md flex items-center justify-center font-bold text-xl border border-white/15 shadow-inner uppercase tracking-wider relative overflow-hidden">
                            @if($student->photo)
                                <img src="/storage/{{ $student->photo }}" alt="{{ $student->full_name }}" class="w-full h-full object-cover rounded-2xl">
                            @else
                                {{ substr($student->first_name, 0, 1) }}{{ substr($student->father_name, 0, 1) }}
                            @endif
                        </div>
                        <span class="absolute -bottom-1 -right-1 flex h-4 w-4">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-4 w-4 bg-emerald-500 border border-slate-900"></span>
                        </span>
                    </div>
                    <div class="space-y-1">
                        <span class="text-indigo-300 text-[10px] font-black tracking-widest uppercase block leading-none">Official Report Card</span>
                        <h2 class="text-2xl font-black font-heading tracking-tight text-white leading-none mt-1">
                            {{ $student->full_name }}
                        </h2>
                        <div class="flex flex-wrap items-center gap-2 mt-2">
                            <span class="px-2.5 py-0.5 bg-white/10 backdrop-blur-md border border-white/10 text-indigo-200 rounded-lg text-[10px] font-extrabold uppercase">
                                @if(str_starts_with(strtolower($student->gradeLevel->name ?? ''), 'grade'))
                                    {{ $student->gradeLevel->name }}
                                @else
                                    Grade {{ $student->gradeLevel->name ?? 'N/A' }}
                                @endif
                            </span>
                            <span class="px-2.5 py-0.5 bg-white/10 backdrop-blur-md border border-white/10 text-indigo-200 rounded-lg text-[10px] font-extrabold uppercase">
                                Section {{ $student->section->name ?? 'N/A' }}
                            </span>
                            <span class="px-2.5 py-0.5 bg-white/10 backdrop-blur-md border border-white/10 text-indigo-200 rounded-lg text-[10px] font-extrabold uppercase">
                                Roll #{{ $student->currentEnrollment->roll_number ?? 'N/A' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Right: High-end Filter Form -->
                <div class="bg-white/5 backdrop-blur-xl border border-white/10 rounded-2xl p-4 w-full max-w-xs lg:w-72 min-w-0 self-start lg:self-auto flex-shrink-0">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[10px] font-black uppercase tracking-wider text-indigo-200">Select Academic Period</span>
                        <span class="text-[9px] font-mono text-indigo-400 bg-indigo-500/10 px-2 py-0.5 rounded">Filter</span>
                    </div>
                    <!-- Desktop View Selector -->
                    <form action="{{ route('parent.student.grades.index', $student) }}" method="GET" class="hidden sm:block w-full">
                        <select name="period" onchange="this.form.submit()" class="w-full px-4 py-2.5 border border-white/10 bg-slate-900/80 text-white rounded-xl font-bold text-xs focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all cursor-pointer shadow-inner">
                            <option value="all" {{ $selectedPeriod == 'all' ? 'selected' : '' }}>All Records</option>
                            <option disabled class="text-slate-500 bg-slate-950">── Quarters ──</option>
                            @foreach($quarters as $quarter)
                                <option value="term_{{ $quarter->id }}" {{ $selectedPeriod == 'term_'.$quarter->id ? 'selected' : '' }}>{{ $quarter->name }}</option>
                            @endforeach
                            <option disabled class="text-slate-500 bg-slate-950">── Semesters ──</option>
                            @foreach($semesters as $semester)
                                <option value="semester_{{ $semester->id }}" {{ $selectedPeriod == 'semester_'.$semester->id ? 'selected' : '' }}>{{ $semester->name }}</option>
                            @endforeach
                            <option disabled class="text-slate-500 bg-slate-950">── Yearly ──</option>
                            <option value="yearly" {{ $selectedPeriod == 'yearly' ? 'selected' : '' }}>Yearly Report</option>
                        </select>
                    </form>
                    <!-- Mobile View Selector Button -->
                    <div class="sm:hidden w-full">
                        <button type="button" @click="mobileDrawerOpen = true" class="w-full flex items-center justify-between px-4 py-2.5 border border-white/10 bg-slate-900/80 text-white rounded-xl font-bold text-xs shadow-inner">
                            <span class="truncate pr-2">
                                {{ $selectedPeriod === 'all' ? 'All Records' : ($selectedPeriod === 'yearly' ? 'Yearly Report' : ($quarters->find((int) str_replace('term_', '', $selectedPeriod))?->name ?? ($semesters->find((int) str_replace('semester_', '', $selectedPeriod))?->name ?? 'Select Period'))) }}
                            </span>
                            <svg class="w-4 h-4 text-indigo-300 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Period Info Title Bar -->
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800/80 pb-4 px-2">
            <div class="flex items-center gap-3">
                <div class="w-2.5 h-6 rounded bg-indigo-600"></div>
                <h2 class="text-lg font-black text-slate-800 dark:text-slate-100 font-heading uppercase tracking-wider">{{ $periodName }} Overview</h2>
            </div>
            <span class="text-xs text-slate-450 dark:text-slate-500 font-bold bg-slate-100 dark:bg-slate-800 px-3 py-1 rounded-xl">
                Active Year: {{ \App\Helpers\CachedData::activeAcademicYear()?->name ?? 'N/A' }}
            </span>
        </div>

        <!-- Gradebook Details -->
        <div class="space-y-6" x-data="{ view: 'report' }">
            @if(empty($groupedMarks) || count($groupedMarks) === 0)
                <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl p-16 text-center shadow-sm relative overflow-hidden group">
                    <div class="absolute -right-10 -top-10 w-40 h-40 bg-indigo-500/5 rounded-full blur-2xl"></div>
                    <div class="w-16 h-16 rounded-2xl bg-slate-50 dark:bg-slate-850 flex items-center justify-center text-slate-400 dark:text-slate-600 mx-auto mb-5 border border-slate-100 dark:border-slate-800">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-extrabold text-slate-800 dark:text-slate-200 font-heading">No academic records found</h3>
                    <p class="text-slate-500 dark:text-slate-400 mt-2 max-w-sm mx-auto text-sm">Assessment scores and official report card data will appear here once they are published by the academic staff.</p>
                </div>
            @else
                <!-- Report / Assessment Tab Switcher -->
                <div class="flex items-center gap-2 px-2">
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
                <div x-show="view === 'report'" x-cloak class="space-y-8">
                    @foreach($groupedMarks as $termName => $marks)
                        @php $termRecord = isset($termRecords) ? ($termRecords[$termName] ?? null) : null; @endphp
                        <div x-data="{ open: false }" class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl shadow-sm hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 overflow-hidden">
                            @include('parent.student.partials.term-card-header', ['termName' => $termName, 'termRecord' => $termRecord])
                            <div x-show="open" x-collapse x-cloak>
                                <div class="px-6 pb-6">
                                    @include('components.grade-history.report-table', ['marks' => $marks])
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- ASSESSMENT TAB: quarters only — the raw per-component entries (Quiz/Mid/Final). -->
                <div x-show="view === 'assessment'" x-cloak class="space-y-8">
                    @php $quarterMarks = $groupedMarks->filter(fn($v, $k) => str_starts_with($k, 'Quarter')); @endphp
                    @forelse($quarterMarks as $termName => $marks)
                        @php $termRecord = isset($termRecords) ? ($termRecords[$termName] ?? null) : null; @endphp
                        <div x-data="{ open: false }" class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl shadow-sm hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 overflow-hidden">
                            @include('parent.student.partials.term-card-header', ['termName' => $termName, 'termRecord' => $termRecord])
                            <div x-show="open" x-collapse x-cloak>
                                <div class="px-6 pb-6">
                                    @include('components.grade-history.assessment-table', ['marks' => $marks])
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl p-16 text-center shadow-sm">
                            <p class="text-slate-500 dark:text-slate-400 text-sm">No quarter assessment data for the selected period.</p>
                        </div>
                    @endforelse
                </div>
            @endif
        </div>

    <!-- Mobile Drawer Backdrop & Drawer -->
    <div x-cloak x-show="mobileDrawerOpen" class="fixed inset-0 z-[100] sm:hidden" x-transition>
        <!-- Backdrop -->
        <div @click="mobileDrawerOpen = false" class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm transition-opacity"></div>
        
        <!-- Bottom Drawer Container -->
        <div class="fixed inset-x-0 bottom-0 max-h-[85vh] bg-white dark:bg-slate-900 rounded-t-[32px] border-t border-slate-150 dark:border-slate-800 shadow-2xl p-6 overflow-y-auto flex flex-col transition-all transform duration-300"
             x-show="mobileDrawerOpen"
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="translate-y-full"
             x-transition:enter-end="translate-y-0"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="translate-y-0"
             x-transition:leave-end="translate-y-full">
            
            <!-- Swipe Indicator bar -->
            <div class="w-12 h-1.5 bg-slate-200 dark:bg-slate-700 rounded-full mx-auto mb-5 flex-shrink-0"></div>
            
            <div class="flex items-center justify-between mb-6 flex-shrink-0">
                <h3 class="font-extrabold text-lg text-slate-850 dark:text-slate-100 font-heading">Select Academic Period</h3>
                <button @click="mobileDrawerOpen = false" class="p-2 rounded-full bg-slate-50 dark:bg-slate-800 text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <!-- Option Lists -->
            <form action="{{ route('parent.student.grades.index', $student) }}" method="GET" class="space-y-4">
                <input type="hidden" name="period" x-model="selectedPeriodTemp">
                
                <div class="space-y-2.5">
                    <!-- All Records Option -->
                    <button type="button" @click="selectedPeriodTemp = 'all'; $nextTick(() => $el.form.submit())"
                            :class="selectedPeriodTemp === 'all' ? 'border-indigo-500 bg-indigo-50/50 dark:bg-indigo-950/20 text-indigo-650 dark:text-indigo-450' : 'border-slate-100 dark:border-slate-800 text-slate-750 dark:text-slate-350'"
                            class="w-full text-left px-4 py-3.5 border rounded-2xl font-bold text-sm flex items-center justify-between transition-colors">
                        <span>All Records</span>
                        <span x-show="selectedPeriodTemp === 'all'" class="text-indigo-600">✓</span>
                    </button>

                    <!-- Quarters header -->
                    <div class="text-[10px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500 pt-2 px-1">Quarters</div>
                    @foreach($quarters as $quarter)
                        <button type="button" @click="selectedPeriodTemp = 'term_{{ $quarter->id }}'; $nextTick(() => $el.form.submit())"
                                :class="selectedPeriodTemp === 'term_{{ $quarter->id }}' ? 'border-indigo-500 bg-indigo-50/50 dark:bg-indigo-950/20 text-indigo-650 dark:text-indigo-455' : 'border-slate-100 dark:border-slate-800 text-slate-750 dark:text-slate-350'"
                                class="w-full text-left px-4 py-3.5 border rounded-2xl font-bold text-sm flex items-center justify-between transition-colors">
                            <span>{{ $quarter->name }}</span>
                            <span x-show="selectedPeriodTemp === 'term_{{ $quarter->id }}'" class="text-indigo-600">✓</span>
                        </button>
                    @endforeach

                    <!-- Semesters header -->
                    <div class="text-[10px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500 pt-2 px-1">Semesters</div>
                    @foreach($semesters as $semester)
                        <button type="button" @click="selectedPeriodTemp = 'semester_{{ $semester->id }}'; $nextTick(() => $el.form.submit())"
                                :class="selectedPeriodTemp === 'semester_{{ $semester->id }}' ? 'border-indigo-500 bg-indigo-50/50 dark:bg-indigo-950/20 text-indigo-650 dark:text-indigo-455' : 'border-slate-100 dark:border-slate-800 text-slate-750 dark:text-slate-350'"
                                class="w-full text-left px-4 py-3.5 border rounded-2xl font-bold text-sm flex items-center justify-between transition-colors">
                            <span>{{ $semester->name }}</span>
                            <span x-show="selectedPeriodTemp === 'semester_{{ $semester->id }}'" class="text-indigo-600">✓</span>
                        </button>
                    @endforeach

                    <!-- Yearly header -->
                    <div class="text-[10px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500 pt-2 px-1">Yearly</div>
                    <button type="button" @click="selectedPeriodTemp = 'yearly'; $nextTick(() => $el.form.submit())"
                            :class="selectedPeriodTemp === 'yearly' ? 'border-indigo-500 bg-indigo-50/50 dark:bg-indigo-950/20 text-indigo-650 dark:text-indigo-455' : 'border-slate-100 dark:border-slate-800 text-slate-750 dark:text-slate-350'"
                            class="w-full text-left px-4 py-3.5 border rounded-2xl font-bold text-sm flex items-center justify-between transition-colors">
                        <span>Yearly Report</span>
                        <span x-show="selectedPeriodTemp === 'yearly'" class="text-indigo-600">✓</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
</x-parent-layout>