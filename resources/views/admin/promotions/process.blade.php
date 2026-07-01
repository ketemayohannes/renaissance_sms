<x-admin-layout>
    <x-slot name="header">Process Student Promotions</x-slot>

    <div class="space-y-8">
        <!-- Header & Navigation -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <x-breadcrumb :items="[
                    ['label' => 'Promotions', 'url' => route('admin.promotions.index')],
                    ['label' => 'Process', 'url' => '#']
                ]" />
                <h1 class="text-4xl font-black text-slate-900 tracking-tight mt-2">Process Promotions</h1>
                <p class="text-sm text-slate-500 mt-1 font-medium">Promote students to the next grade level based on active performance rules.</p>
            </div>
            <div>
                <a href="{{ route('admin.promotions.index') }}" class="px-6 py-3 bg-white border border-slate-200 text-slate-600 font-black text-[10px] uppercase tracking-widest rounded-xl hover:bg-slate-50 transition-all shadow-sm flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Back to Rules
                </a>
            </div>
        </div>

        <!-- Form Wrapper -->
        <div class="glass-panel p-8">
            @if(!$nextAcademicYear)
                <div class="alert-warning flex items-start gap-3 mb-6">
                    <svg class="w-5 h-5 text-amber-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <div>
                        <strong class="text-amber-800 block">Upcoming Academic Year Missing!</strong>
                        <span class="text-sm text-amber-700 font-medium">Please create the next academic year in settings before processing promotions.</span>
                    </div>
                </div>
            @endif

            <h3 class="text-lg font-black text-slate-900 uppercase tracking-tight mb-6 flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-indigo-500 flex items-center justify-center text-white">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                </div>
                Select Section to Process
            </h3>

            <form action="{{ route('admin.promotions.preview') }}" method="POST" class="space-y-6" x-data="{
                divisions: {{ $divisions->toJson() }},
                selectedDivisionId: '',
                selectedGradeLevelId: '',
                selectedSectionId: '',
                get gradeLevels() {
                    if (!this.selectedDivisionId) return [];
                    const div = this.divisions.find(d => d.id == this.selectedDivisionId);
                    if (!div || !div.grade_levels) return [];
                    return div.grade_levels;
                },
                get sections() {
                    if (!this.selectedGradeLevelId) return [];
                    const grade = this.gradeLevels.find(g => g.id == this.selectedGradeLevelId);
                    return grade ? grade.sections : [];
                },
                init() {
                    this.$watch('selectedDivisionId', () => {
                        this.selectedGradeLevelId = '';
                        this.selectedSectionId = '';
                    });
                    this.$watch('selectedGradeLevelId', () => {
                        this.selectedSectionId = '';
                    });
                }
            }">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Division Dropdown -->
                    <div>
                        <label for="division_id" class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Division</label>
                        <select id="division_id" x-model="selectedDivisionId" class="premium-select w-full" required>
                            <option value="">Select Division</option>
                            <template x-for="division in divisions" :key="division.id">
                                <option :value="division.id" x-text="division.name"></option>
                            </template>
                        </select>
                    </div>

                    <!-- Grade Level Dropdown -->
                    <div>
                        <label for="grade_level_id" class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Grade</label>
                        <select id="grade_level_id" x-model="selectedGradeLevelId" class="premium-select w-full" :disabled="!selectedDivisionId" required>
                            <option value="">Select Grade</option>
                            <template x-for="grade in gradeLevels" :key="grade.id">
                                <option :value="grade.id" x-text="grade.name"></option>
                            </template>
                        </select>
                    </div>

                    <!-- Section Dropdown -->
                    <div>
                        <label for="section_id" class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Section</label>
                        <select name="section_id" id="section_id" x-model="selectedSectionId" class="premium-select w-full" :disabled="!selectedGradeLevelId" required>
                            <option value="">Select Section</option>
                            <template x-for="sect in sections" :key="sect.id">
                                <option :value="sect.id" x-text="sect.name"></option>
                            </template>
                        </select>
                    </div>
                </div>

                <div class="flex justify-end pt-4">
                    <button type="submit" class="vibrant-btn-blue px-8 py-4 flex items-center justify-center gap-2" {{ !$nextAcademicYear ? 'disabled' : '' }}>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                        Preview Promotions
                    </button>
                </div>
            </form>

            <!-- Academic Year Details KPI Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-12 border-t border-slate-100 pt-8">
                <!-- Current Academic Year Card -->
                <div class="flex items-center gap-4 bg-slate-50/50 border border-slate-100 p-6 rounded-2xl">
                    <div class="w-12 h-12 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-500 shadow-sm">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                    <div>
                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-wider block">Current Academic Year</span>
                        <span class="text-xl font-bold text-slate-800">{{ $academicYear->name }}</span>
                    </div>
                </div>

                <!-- Next Academic Year Card -->
                <div class="flex items-center gap-4 bg-slate-50/50 border border-slate-100 p-6 rounded-2xl">
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-500 shadow-sm">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"></path></svg>
                    </div>
                    <div>
                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-wider block">Next Academic Year</span>
                        @if($nextAcademicYear)
                            <span class="text-xl font-bold text-slate-800">{{ $nextAcademicYear->name }}</span>
                        @else
                            <span class="text-sm font-black text-rose-500 uppercase tracking-wider">Not Created</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
