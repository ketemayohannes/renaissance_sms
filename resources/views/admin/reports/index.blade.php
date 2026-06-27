<x-admin-layout>
    <x-slot name="header">General Reports</x-slot>

    <div class="space-y-8 pb-12" x-data="reportsDashboard()">
        <!-- Modern Header & Actions -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 no-print">
            <div>
                <x-breadcrumb :items="[
                    ['label' => 'Reports', 'url' => '#']
                ]" />
                <h1 class="text-4xl font-black text-slate-900 tracking-tight mt-2 flex items-center gap-3">
                    <span class="w-1.5 h-8 bg-indigo-600 rounded-full shadow-[0_0_15px_rgba(79,70,229,0.4)]"></span>
                    General Reports
                </h1>
                <p class="text-slate-500 font-semibold mt-1 uppercase text-[10px] tracking-[0.3em] italic">Download Custom Summary Reports</p>
            </div>
        </div>

        <form action="" method="GET" @submit.prevent="downloadReport($event)">
            <!-- Top Controls card -->
            <div class="bg-white rounded-[2.5rem] border border-slate-200 p-8 shadow-sm relative overflow-hidden transition-all duration-300">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
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

                    <div class="space-y-2">
                        <label class="px-1 text-[10px] font-black text-slate-400 uppercase tracking-widest">Division</label>
                        <select name="division_id" x-model="selectedDivision" class="w-full bg-white/50 border-slate-200 rounded-2xl focus:ring-indigo-500 focus:border-indigo-500 font-bold text-sm py-3 px-4 transition-all" required>
                            <option value="">Select Division</option>
                            @foreach($divisions as $div)
                                <option value="{{ $div->id }}">{{ $div->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Reports Card list -->
            <div class="mt-8">
                <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-6">Available Reports</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Report Card: Top 3 Per Section -->
                    <div class="bg-white rounded-[2.5rem] border border-slate-200 p-6 flex flex-col justify-between hover:shadow-lg transition-all duration-300">
                        <div>
                            <div class="w-12 h-12 bg-amber-500/10 border border-amber-500/20 text-amber-600 rounded-2xl flex items-center justify-center mb-5 shadow-inner text-xl">
                                🏆
                            </div>
                            <h3 class="text-base font-black text-slate-900 uppercase tracking-tight">Top 3 Per Section</h3>
                            <p class="text-slate-400 text-xs font-bold mt-2 leading-relaxed opacity-85">Generates a PDF document compiling the top 3 ranked students from each section under the selected division, along with a final grade-level summary honor roll.</p>
                        </div>
                        <button type="submit" class="mt-8 w-full py-4 bg-slate-900 text-white font-black text-[10px] uppercase tracking-widest rounded-2xl hover:bg-indigo-600 transition-all flex items-center justify-center gap-3 active:scale-95 group">
                            <svg class="w-4 h-4 text-white group-hover:animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                            </svg>
                            Download Summary PDF
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        function reportsDashboard() {
            const defaultYear = '{{ $academicYears->firstWhere('is_active', true)?->id ?? $academicYears->first()?->id ?? '' }}';
            
            @php
                $activeYear = $academicYears->firstWhere('is_active', true) ?? $academicYears->first();
                $initialTerms = $activeYear ? \App\Models\Term::where('academic_year_id', $activeYear->id)->get()->map(function($t) {
                    return ['id' => (string)$t->id, 'name' => $t->name];
                })->toArray() : [];
                $initialTerms[] = ['id' => 'yearly', 'name' => 'Yearly'];
            @endphp

            return {
                selectedYear: defaultYear,
                selectedTerm: '',
                selectedDivision: '',
                terms: @json($initialTerms),

                async init() {
                    // Pre-populated on initial load
                },

                async loadTerms() {
                    if (!this.selectedYear) return;
                    try {
                        const url = `{{ route('admin.gradebook.get-terms') }}?academic_year_id=${this.selectedYear}`;
                        const res = await fetch(url);
                        const data = await res.json();
                        // Inject yearly option at the end
                        this.terms = [...data, { id: 'yearly', name: 'Yearly' }];
                        this.selectedTerm = '';
                    } catch (e) {
                        console.error("Failed to load terms:", e);
                    }
                },

                downloadReport(e) {
                    if (!this.selectedTerm || !this.selectedDivision) {
                        alert('Please select both a timeline and a division before downloading.');
                        return;
                    }
                    const params = new URLSearchParams({
                        academic_year_id: this.selectedYear,
                        term_id: this.selectedTerm,
                        division_id: this.selectedDivision
                    }).toString();
                    window.location.href = `{{ route('admin.reports.top3-per-section') }}?${params}`;
                }
            };
        }
    </script>
    @endpush
</x-admin-layout>
