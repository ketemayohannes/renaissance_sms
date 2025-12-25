<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <span class="text-xl font-bold text-slate-800">Academic Insights</span>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.report-cards.settings') }}" class="btn-secondary text-sm px-4 py-2 border-slate-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Report Card Settings
                </a>
                <a href="{{ route('admin.academic-reports.settings') }}" class="btn-secondary text-sm px-4 py-2 border-slate-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    Roster Settings
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6" x-data="academicReports()">
        <x-breadcrumb :items="[
            ['label' => 'Academic Reports', 'url' => '#']
        ]" />

        <!-- Hero Section with Gradient Background -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-600 via-indigo-700 to-purple-800 p-8 md:p-10 text-white">
            <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxnIGZpbGw9IiNmZmYiIGZpbGwtb3BhY2l0eT0iMC4wNSI+PHBhdGggZD0iTTM2IDM0djItSDI0di0yaDEyek0zNiAzMHYySDI0di0yaDEyem0wLTR2Mkgy0di0yaDEyeiIvPjwvZz48L2c+PC9zdmc+')] opacity-30"></div>
            <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                <div>
                    <h2 class="text-2xl md:text-3xl font-bold mb-2">Generate Academic Reports</h2>
                    <p class="text-indigo-200 text-sm">Create comprehensive student report cards, rosters, and performance analytics</p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="bg-white/10 backdrop-blur-sm rounded-xl px-4 py-2 border border-white/20">
                        <p class="text-xs text-indigo-200 uppercase tracking-wider">Active Year</p>
                        <p class="font-bold text-lg">{{ $academicYears->firstWhere('is_active', true)?->name ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Form Card -->
        <form id="reportForm" action="{{ route('admin.academic-reports.show') }}" method="GET">
            <div class="card p-0 overflow-hidden">
                <!-- Section 1: Scope Selection -->
                <div class="p-6 md:p-8 border-b border-gray-100">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900">Define Analysis Scope</h3>
                            <p class="text-sm text-gray-500">Select the academic period and target section</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
                        <!-- Academic Year -->
                        <div>
                            <label for="academic_year_id" class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">Academic Year</label>
                            <select name="academic_year_id" id="academic_year_id" x-model="selectedYear" @change="loadTerms()" class="form-input w-full" required>
                                <option value="">Select Year</option>
                                @foreach($academicYears as $year)
                                    <option value="{{ $year->id }}" {{ $year->is_active ? 'selected' : '' }}>{{ $year->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Term -->
                        <div>
                            <label for="term_id" class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">Target Term</label>
                            <select name="term_id" id="term_id" x-model="selectedTerm" class="form-input w-full" required>
                                <option value="">Select Term</option>
                                <template x-for="term in terms" :key="term.id">
                                    <option :value="term.id" x-text="term.name"></option>
                                </template>
                            </select>
                        </div>

                        <!-- Grade Level -->
                        <div x-show="!hideGrade" x-transition>
                            <label for="grade_level_id" class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">Grade Level</label>
                            <select name="grade_level_id" id="grade_level_id" x-model="selectedGrade" @change="loadSections(); loadSubjects()" class="form-input w-full" :required="!hideGrade">
                                <option value="">Select Grade Level</option>
                                @foreach($gradeLevels as $grade)
                                    <option value="{{ $grade->id }}">{{ $grade->name }} ({{ $grade->division->name }})</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Division (for Grade Matrix) -->
                        <div x-show="hideGrade" x-transition x-cloak>
                            <label for="division_id" class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">Division Filter</label>
                            <select name="division_id" id="division_id" x-model="selectedDivision" class="form-input w-full">
                                <option value="">All Divisions</option>
                                @foreach($divisions as $division)
                                    <option value="{{ $division->id }}">{{ $division->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Section -->
                        <div x-show="!hideSection" x-transition>
                            <label for="section_id" class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">Specific Section</label>
                            <select name="section_id" id="section_id" x-model="selectedSection" class="form-input w-full" :required="!hideSection && !disableSection" :disabled="disableSection" :class="{'opacity-50 cursor-not-allowed': disableSection}">
                                <option value="">Select Section</option>
                                <template x-for="section in sections" :key="section.id">
                                    <option :value="section.id" x-text="section.name"></option>
                                </template>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Report Type Selection -->
                <div class="p-6 md:p-8 bg-gray-50/50">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900">Choose Report Type</h3>
                            <p class="text-sm text-gray-500">Select the type of report you want to generate</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                        <!-- Report Card -->
                        <label class="group relative cursor-pointer">
                            <input type="radio" name="report_type" value="report_card" x-model="reportType" class="sr-only peer" required>
                            <div class="h-full p-5 rounded-xl border-2 border-gray-200 bg-white transition-all duration-200 peer-checked:border-blue-500 peer-checked:bg-blue-50 hover:border-gray-300 hover:shadow-md">
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center mb-4 shadow-lg shadow-blue-500/25">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                </div>
                                <h4 class="font-semibold text-gray-900 mb-1">Report Card</h4>
                                <p class="text-xs text-gray-500">Bulk student results with grades</p>
                                <div class="absolute top-3 right-3 w-5 h-5 rounded-full border-2 border-gray-300 peer-checked:border-blue-500 peer-checked:bg-blue-500 flex items-center justify-center transition-all">
                                    <svg class="w-3 h-3 text-white opacity-0 peer-checked:opacity-100" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                </div>
                            </div>
                        </label>

                        <!-- Roster -->
                        <label class="group relative cursor-pointer">
                            <input type="radio" name="report_type" value="roster" x-model="reportType" class="sr-only peer" required>
                            <div class="h-full p-5 rounded-xl border-2 border-gray-200 bg-white transition-all duration-200 peer-checked:border-emerald-500 peer-checked:bg-emerald-50 hover:border-gray-300 hover:shadow-md">
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-600 flex items-center justify-center mb-4 shadow-lg shadow-emerald-500/25">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                </div>
                                <h4 class="font-semibold text-gray-900 mb-1">Roster</h4>
                                <p class="text-xs text-gray-500">Complete marks summary sheet</p>
                                <div class="absolute top-3 right-3 w-5 h-5 rounded-full border-2 border-gray-300 peer-checked:border-emerald-500 peer-checked:bg-emerald-500 flex items-center justify-center transition-all">
                                    <svg class="w-3 h-3 text-white opacity-0 peer-checked:opacity-100" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                </div>
                            </div>
                        </label>

                        <!-- Analysis -->
                        <label class="group relative cursor-pointer">
                            <input type="radio" name="report_type" value="result_analysis" x-model="reportType" class="sr-only peer" required>
                            <div class="h-full p-5 rounded-xl border-2 border-gray-200 bg-white transition-all duration-200 peer-checked:border-indigo-500 peer-checked:bg-indigo-50 hover:border-gray-300 hover:shadow-md">
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-500 to-indigo-600 flex items-center justify-center mb-4 shadow-lg shadow-indigo-500/25">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                                </div>
                                <h4 class="font-semibold text-gray-900 mb-1">Analysis</h4>
                                <p class="text-xs text-gray-500">Section performance insights</p>
                                <div class="absolute top-3 right-3 w-5 h-5 rounded-full border-2 border-gray-300 peer-checked:border-indigo-500 peer-checked:bg-indigo-500 flex items-center justify-center transition-all">
                                    <svg class="w-3 h-3 text-white opacity-0 peer-checked:opacity-100" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                </div>
                            </div>
                        </label>

                        <!-- Subject Wise -->
                        <label class="group relative cursor-pointer">
                            <input type="radio" name="report_type" value="grade_subject_analysis" x-model="reportType" class="sr-only peer" required>
                            <div class="h-full p-5 rounded-xl border-2 border-gray-200 bg-white transition-all duration-200 peer-checked:border-purple-500 peer-checked:bg-purple-50 hover:border-gray-300 hover:shadow-md">
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-purple-500 to-purple-600 flex items-center justify-center mb-4 shadow-lg shadow-purple-500/25">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                                </div>
                                <h4 class="font-semibold text-gray-900 mb-1">Subject Wise</h4>
                                <p class="text-xs text-gray-500">Cross-section comparison</p>
                                <div class="absolute top-3 right-3 w-5 h-5 rounded-full border-2 border-gray-300 peer-checked:border-purple-500 peer-checked:bg-purple-500 flex items-center justify-center transition-all">
                                    <svg class="w-3 h-3 text-white opacity-0 peer-checked:opacity-100" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                </div>
                            </div>
                        </label>

                        <!-- Grade Matrix -->
                        <label class="group relative cursor-pointer">
                            <input type="radio" name="report_type" value="consolidated_matrix" x-model="reportType" class="sr-only peer" required>
                            <div class="h-full p-5 rounded-xl border-2 border-gray-200 bg-white transition-all duration-200 peer-checked:border-amber-500 peer-checked:bg-amber-50 hover:border-gray-300 hover:shadow-md">
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-amber-500 to-orange-500 flex items-center justify-center mb-4 shadow-lg shadow-amber-500/25">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path></svg>
                                </div>
                                <h4 class="font-semibold text-gray-900 mb-1">Grade Matrix</h4>
                                <p class="text-xs text-gray-500">School-wide overview</p>
                                <div class="absolute top-3 right-3 w-5 h-5 rounded-full border-2 border-gray-300 peer-checked:border-amber-500 peer-checked:bg-amber-500 flex items-center justify-center transition-all">
                                    <svg class="w-3 h-3 text-white opacity-0 peer-checked:opacity-100" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                </div>
                            </div>
                        </label>
                    </div>

                    <!-- Subject Selection (Conditional) -->
                    <div x-show="reportType === 'grade_subject_analysis'" x-transition x-cloak class="mt-6 p-4 rounded-xl bg-purple-50 border border-purple-100">
                        <label for="subject_id" class="block text-xs font-medium text-purple-700 uppercase tracking-wider mb-2">Target Subject</label>
                        <select name="subject_id" id="subject_id" x-model="selectedSubject" class="form-input w-full max-w-md border-purple-200 focus:border-purple-500 focus:ring-purple-500" :required="reportType === 'grade_subject_analysis'">
                            <option value="">Select Subject</option>
                            <template x-for="subject in subjects" :key="subject.id">
                                <option :value="subject.id" x-text="subject.name"></option>
                            </template>
                        </select>
                    </div>
                </div>

                <!-- Section 3: Action Footer -->
                <div class="p-6 md:p-8 bg-gray-50 border-t border-gray-100">
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div class="flex items-center gap-3 text-gray-500 text-sm">
                            <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span>Ensure all grades are submitted before generating reports</span>
                        </div>
                        <button type="submit" class="btn-primary px-8 py-3 text-base flex items-center gap-2 shadow-lg shadow-indigo-500/25 hover:shadow-xl hover:shadow-indigo-500/30 transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                            Generate Report
                        </button>
                    </div>
                </div>
            </div>
        </form>

        <!-- Quick Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="card p-5 flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ \App\Models\Student::where('is_active', true)->count() }}</p>
                    <p class="text-sm text-gray-500">Active Students</p>
                </div>
            </div>
            <div class="card p-5 flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ \App\Models\Section::whereHas('academicYear', fn($q) => $q->where('is_active', true))->count() }}</p>
                    <p class="text-sm text-gray-500">Active Sections</p>
                </div>
            </div>
            <div class="card p-5 flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-purple-100 text-purple-600 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ \App\Models\Term::whereHas('academicYear', fn($q) => $q->where('is_active', true))->count() }}</p>
                    <p class="text-sm text-gray-500">Terms This Year</p>
                </div>
            </div>
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

                get hideGrade() {
                    return this.reportType === 'consolidated_matrix';
                },
                get hideSection() {
                    return this.reportType === 'consolidated_matrix';
                },
                get disableSection() {
                    return this.reportType === 'grade_subject_analysis';
                },

                init() {
                    if (this.selectedYear) {
                        this.loadTerms();
                    }
                    
                    // Handle form routing
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
                    } catch (error) {
                        console.error('Error loading terms:', error);
                    }
                },

                async loadSections() {
                    if (!this.selectedYear || !this.selectedGrade) return;
                    try {
                        const response = await fetch(`{{ route('admin.gradebook.get-sections') }}?academic_year_id=${this.selectedYear}&grade_level_id=${this.selectedGrade}`);
                        this.sections = await response.json();
                    } catch (error) {
                        console.error('Error loading sections:', error);
                    }
                },

                async loadSubjects() {
                    if (!this.selectedYear || !this.selectedGrade) return;
                    try {
                        const response = await fetch(`{{ route('admin.gradebook.get-subjects') }}?academic_year_id=${this.selectedYear}&grade_level_id=${this.selectedGrade}`);
                        this.subjects = await response.json();
                    } catch (error) {
                        console.error('Error loading subjects:', error);
                    }
                }
            }
        }
    </script>
    @endpush
</x-admin-layout>
