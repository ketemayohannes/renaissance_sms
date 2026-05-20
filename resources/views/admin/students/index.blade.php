<x-admin-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="font-heading font-bold text-2xl text-slate-900">Student Management</h2>
                <p class="text-slate-500 text-sm mt-1">Manage enrollments, profiles, and academic records.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.students.export') }}" 
                   class="inline-flex items-center px-4 py-2 bg-white border border-slate-200 text-slate-700 text-sm font-semibold rounded-xl hover:bg-slate-50 transition-all gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                    </svg>
                    Export
                </a>
                <a href="{{ route('admin.students.import') }}" 
                   class="inline-flex items-center px-4 py-2 bg-white border border-slate-200 text-slate-700 text-sm font-semibold rounded-xl hover:bg-slate-50 transition-all gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                    </svg>
                    Import
                </a>
                <a href="{{ route('admin.students.create') }}" 
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl transition-all duration-200 shadow-lg shadow-indigo-100 gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Register Student
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Breadcrumb -->
        <x-breadcrumb :items="[
            ['label' => 'Students', 'url' => '#']
        ]" />

        <!-- Stats Overview -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <x-ui.glass-card class="p-4 flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Students</p>
                    <p class="text-xl font-bold text-slate-900">{{ $students->total() }}</p>
                </div>
            </x-ui.glass-card>
            
            <x-ui.glass-card class="p-4 flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Active</p>
                    <p class="text-xl font-bold text-slate-900">{{ \App\Models\Student::active()->count() }}</p>
                </div>
            </x-ui.glass-card>

            <x-ui.glass-card class="p-4 flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-amber-50 flex items-center justify-center text-amber-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Unassigned</p>
                    <p class="text-xl font-bold text-slate-900">{{ \App\Models\Student::unassigned()->count() }}</p>
                </div>
            </x-ui.glass-card>

            <x-ui.glass-card class="p-4 flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-rose-50 flex items-center justify-center text-rose-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Trashed</p>
                    <p class="text-xl font-bold text-slate-900">{{ \App\Models\Student::onlyTrashed()->count() }}</p>
                </div>
            </x-ui.glass-card>
        </div>

        @if(session('import_errors'))
            <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 flex gap-3">
                <div class="flex-shrink-0 w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center text-amber-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-amber-900">Import Warnings/Errors</h3>
                    <div class="mt-1 max-h-40 overflow-y-auto text-sm text-amber-700">
                        <ul class="list-disc list-inside">
                            @foreach(session('import_errors') as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <x-ui.premium-card>
            <div class="p-6">
                <!-- Filters Section -->
                <div x-data="{
                    showAdvanced: {{ request()->anyFilled(['age_min', 'age_max', 'enrollment_year']) ? 'true' : 'false' }},
                    selectedDivision: '{{ request('division_id') }}',
                    selectedGrade: '{{ request('grade_id') }}',
                    selectedSection: '{{ request('section_name') }}',
                    allGrades: {{ $gradeLevels->map(fn($g) => ['id' => $g->id, 'name' => $g->name, 'division_id' => $g->division_id])->values()->toJson() }},
                    allSections: {{ $allSections->map(fn($s) => ['id' => $s->id, 'name' => $s->name, 'grade_level_id' => $s->grade_level_id])->values()->toJson() }},
                    get filteredGrades() {
                        if (!this.selectedDivision) return this.allGrades;
                        return this.allGrades.filter(g => String(g.division_id) === String(this.selectedDivision));
                    },
                    get filteredSections() {
                        if (!this.selectedGrade) {
                            if (!this.selectedDivision) return this.allSections;
                            const gradeIds = this.filteredGrades.map(g => g.id);
                            return this.allSections.filter(s => gradeIds.includes(s.grade_level_id));
                        }
                        return this.allSections.filter(s => String(s.grade_level_id) === String(this.selectedGrade));
                    },
                    onDivisionChange() {
                        this.selectedGrade = '';
                        this.selectedSection = '';
                    },
                    onGradeChange() {
                        this.selectedSection = '';
                    }
                }" class="mb-8 p-6 bg-slate-50/50 rounded-3xl border border-slate-100">
                    <form action="{{ route('admin.students.index') }}" method="GET">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-7 gap-4 mb-4">
                            <!-- Search -->
                            <div class="sm:col-span-2 lg:col-span-2">
                                <label for="search" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Search Students</label>
                                <div class="relative">
                                    <input type="text" name="search" id="search" value="{{ request('search') }}"
                                           placeholder="Name, ID, No..."
                                           class="w-full pl-10 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all">
                                    <svg class="w-5 h-5 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </div>
                            </div>

                            <!-- Gender -->
                            <div>
                                <label for="gender" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Gender</label>
                                <select name="gender" id="gender" class="w-full py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all">
                                    <option value="">All</option>
                                    <option value="M" {{ request('gender') == 'M' ? 'selected' : '' }}>Male</option>
                                    <option value="F" {{ request('gender') == 'F' ? 'selected' : '' }}>Female</option>
                                </select>
                            </div>

                            <!-- Division -->
                            <div>
                                <label for="division_id" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Division</label>
                                <select name="division_id" id="division_id"
                                        x-model="selectedDivision"
                                        @change="onDivisionChange()"
                                        class="w-full py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all">
                                    <option value="">All Divisions</option>
                                    @foreach($divisions as $division)
                                        <option value="{{ $division->id }}">{{ $division->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Grade (filtered by division) -->
                            <div>
                                <label for="grade_id" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Grade</label>
                                <select name="grade_id" id="grade_id"
                                        x-model="selectedGrade"
                                        @change="onGradeChange()"
                                        class="w-full py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all">
                                    <option value="">All Grades</option>
                                    <template x-for="grade in filteredGrades" :key="grade.id">
                                        <option :value="grade.id" x-text="grade.name"></option>
                                    </template>
                                </select>
                            </div>

                            <!-- Section (filtered by grade) -->
                            <div>
                                <label for="section_name" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Section</label>
                                <select name="section_name" id="section_name"
                                        x-model="selectedSection"
                                        class="w-full py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all">
                                    <option value="">All Sections</option>
                                    <option value="unassigned" {{ request('section_name') == 'unassigned' ? 'selected' : '' }}>⚠ Unassigned</option>
                                    <template x-for="section in filteredSections" :key="section.id">
                                        <option :value="section.name" x-text="section.name"></option>
                                    </template>
                                </select>
                            </div>

                            <!-- Status -->
                            <div>
                                <label for="status" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Status</label>
                                <select name="status" id="status" class="w-full py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all">
                                    <option value="">All</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ request('status') == 'inactive' || request('status') == 'blocked' ? 'selected' : '' }}>Inactive</option>
                                    <option value="trashed" {{ request('status') == 'trashed' ? 'selected' : '' }}>Trash</option>
                                </select>
                            </div>
                        </div>

                        <!-- Advanced Fields Toggle -->
                        <div class="flex items-center justify-between">
                            <button type="button" @click="showAdvanced = !showAdvanced" class="text-sm text-indigo-600 hover:text-indigo-800 font-semibold flex items-center gap-1 transition-all">
                                <span x-text="showAdvanced ? 'Hide Advanced Filters' : 'Show Advanced Filters'"></span>
                                <svg class="w-4 h-4 transform transition-transform" :class="{'rotate-180': showAdvanced}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>
                            <div class="flex gap-2">
                                @if(request()->anyFilled(['search', 'gender', 'grade_id', 'section_name', 'status', 'division_id', 'age_min', 'age_max', 'enrollment_year']))
                                    <a href="{{ route('admin.students.index') }}" class="px-4 py-2 text-slate-500 hover:text-slate-700 text-sm font-semibold transition-all">Clear All</a>
                                @endif
                                <button type="submit" class="inline-flex items-center px-6 py-2 bg-slate-900 hover:bg-slate-800 text-white text-sm font-semibold rounded-xl transition-all shadow-lg shadow-slate-200">
                                    Apply Filters
                                </button>
                            </div>
                        </div>

                        <!-- Advanced Fields -->
                        <div x-show="showAdvanced" x-collapse x-cloak class="mt-4 pt-4 border-t border-slate-100">
                             <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Min Age</label>
                                    <input type="number" name="age_min" value="{{ request('age_min') }}" min="1" max="30" class="w-full py-2 bg-white border border-slate-200 rounded-xl">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Max Age</label>
                                    <input type="number" name="age_max" value="{{ request('age_max') }}" min="1" max="30" class="w-full py-2 bg-white border border-slate-200 rounded-xl">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Enrollment Academic Year</label>
                                    <select name="enrollment_year" class="w-full py-2 bg-white border border-slate-200 rounded-xl">
                                        <option value="">Any Year</option>
                                        @foreach(\App\Models\AcademicYear::orderBy('start_date', 'desc')->get() as $year)
                                            <option value="{{ $year->id }}" {{ request('enrollment_year') == $year->id ? 'selected' : '' }}>{{ $year->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                             </div>
                        </div>
                    </form>
                </div>

                <div x-data="{ selected: [], allSelected: false }">
                    <!-- Bulk Actions -->
                    <div x-show="selected.length > 0" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                         class="bg-indigo-50/50 backdrop-blur-sm border border-indigo-100 p-4 mb-4 rounded-2xl flex flex-wrap justify-between items-center gap-4" style="display: none;">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center text-white text-xs font-bold">
                                <span x-text="selected.length"></span>
                            </div>
                            <span class="text-sm font-semibold text-indigo-900">Students Selected</span>
                        </div>
                        <div class="flex gap-2">
                            @if(!auth()->user()?->hasRole('Vice Principal') && !auth()->user()?->hasRole('Supervisor'))
                            <form action="{{ route('admin.students.bulk-destroy') }}" method="POST" class="confirm-form" data-confirm-message="Are you sure you want to delete the selected students?" data-confirm-title="Bulk Delete Students" data-confirm-button="Delete Selected">
                                @csrf
                                <template x-for="id in selected">
                                    <input type="hidden" name="ids[]" :value="id">
                                </template>
                                <button type="submit" class="px-4 py-2 bg-rose-100 hover:bg-rose-200 text-rose-700 text-xs font-bold rounded-xl transition-all">
                                    Delete Selected
                                </button>
                            </form>
                            @endif
                            <form action="{{ route('admin.students.bulk-id-cards-selected') }}" method="POST" target="_blank">
                                @csrf
                                <template x-for="id in selected">
                                    <input type="hidden" name="ids[]" :value="id">
                                </template>
                                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition-all shadow-md shadow-indigo-100">
                                    Print ID Cards
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50/50">
                                    <th class="p-4 first:rounded-l-2xl">
                                        <input type="checkbox" id="selectAll" @click="allSelected = !allSelected; selected = allSelected ? {{ $students->pluck('id') }} : []" class="w-5 h-5 rounded-lg border-slate-300 text-indigo-600 focus:ring-indigo-500/20 transition-all">
                                    </th>
                                    <th class="p-4 text-xs font-bold text-slate-500 uppercase tracking-wider">No</th>
                                    <th class="p-4 text-xs font-bold text-slate-500 uppercase tracking-wider">
                                        <a href="{{ route('admin.students.index', array_merge(request()->except('page'), ['sort' => 'name', 'direction' => request('sort') === 'name' && request('direction') === 'asc' ? 'desc' : 'asc'])) }}" class="group flex items-center gap-1 hover:text-indigo-600 transition-colors">
                                            Student Name
                                            <span class="text-slate-300 group-hover:text-indigo-400 transition-all">{{ (request('sort') === 'name') ? (request('direction') === 'asc' ? '↑' : '↓') : '↕' }}</span>
                                        </a>
                                    </th>
                                    <th class="p-4 text-xs font-bold text-slate-500 uppercase tracking-wider">
                                         <a href="{{ route('admin.students.index', array_merge(request()->except('page'), ['sort' => 'student_id', 'direction' => request('sort') === 'student_id' && request('direction') === 'asc' ? 'desc' : 'asc'])) }}" class="group flex items-center gap-1 hover:text-indigo-600 transition-colors">
                                            Student ID
                                             <span class="text-slate-300 group-hover:text-indigo-400 transition-all">{{ (request('sort') === 'student_id') ? (request('direction') === 'asc' ? '↑' : '↓') : '↕' }}</span>
                                         </a>
                                    </th>
                                    <th class="p-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Gender</th>
                                    <th class="p-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Grade/Section</th>
                                    <th class="p-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Status</th>
                                    <th class="p-4 last:rounded-r-2xl text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($students as $student)
                                    <tr class="group hover:bg-slate-50/50 transition-all duration-200">
                                        <td class="p-4">
                                            <input type="checkbox" value="{{ $student->id }}" x-model="selected" class="w-5 h-5 rounded-lg border-slate-300 text-indigo-600 focus:ring-indigo-500/20 transition-all">
                                        </td>
                                        <td class="p-4 text-sm font-medium text-slate-400">
                                            {{ ($students->currentPage() - 1) * $students->perPage() + $loop->iteration }}
                                        </td>
                                        <td class="p-4">
                                            <div class="flex items-center gap-3">
                                                <div class="relative">
                                                    @if($student->photo)
                                                        <img class="h-11 w-11 rounded-2xl object-cover ring-2 ring-white shadow-sm" src="{{ Storage::url($student->photo) }}" alt="">
                                                    @else
                                                        <div class="h-11 w-11 rounded-2xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-sm ring-2 ring-white shadow-sm">
                                                            {{ substr($student->first_name, 0, 1) }}{{ substr($student->father_name, 0, 1) }}
                                                        </div>
                                                    @endif
                                                    <div class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full border-2 border-white {{ $student->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></div>
                                                </div>
                                                <div>
                                                    <div class="text-sm font-bold text-slate-900 group-hover:text-indigo-600 transition-colors">{{ $student->full_name }}</div>
                                                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">{{ $student->admission_number }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="p-4">
                                            <span class="text-sm font-semibold text-slate-600">{{ $student->student_id }}</span>
                                        </td>
                                        <td class="p-4">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold {{ in_array(strtoupper($student->gender), ['M', 'MALE']) ? 'bg-blue-50 text-blue-700' : 'bg-pink-50 text-pink-700' }} uppercase tracking-wide">
                                                {{ $student->gender }}
                                            </span>
                                        </td>
                                        <td class="p-4">
                                            @if($student->enrollments->whereNull('end_date')->first())
                                                @php $enrollment = $student->enrollments->whereNull('end_date')->first(); @endphp
                                                <div class="text-sm font-bold text-slate-700">{{ $enrollment->section->gradeLevel->name }}</div>
                                                <div class="text-xs font-medium text-slate-400">{{ $enrollment->section->name }}</div>
                                            @else
                                                <span class="text-rose-500 text-xs font-bold uppercase tracking-wide px-2 py-1 bg-rose-50 rounded-lg">Not Enrolled</span>
                                            @endif
                                        </td>
                                        <td class="p-4">
                                            @if($student->trashed())
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-black bg-rose-100 text-rose-700 uppercase tracking-[0.1em]">Deleted</span>
                                            @elseif($student->is_active)
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-black bg-emerald-100 text-emerald-700 uppercase tracking-[0.1em]">Active</span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-black bg-slate-100 text-slate-600 uppercase tracking-[0.1em]">Blocked</span>
                                            @endif
                                        </td>
                                        <td class="p-4 text-right">
                                            <div class="flex justify-end gap-2">
                                                @if($student->trashed())
                                                    <form action="{{ route('admin.students.restore', $student->id) }}" method="POST" class="inline-block">
                                                        @csrf
                                                        <button type="submit" class="p-2 hover:bg-emerald-50 text-emerald-600 rounded-xl transition-all" title="Restore">
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                                        </button>
                                                    </form>
                                                @else
                                                    <a href="{{ route('admin.students.show', $student) }}" class="p-2 hover:bg-indigo-50 text-indigo-600 rounded-xl transition-all" title="View Profile">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                                    </a>
                                                     <button @click="$dispatch('open-quick-edit', { 
                                                         id: '{{ $student->id }}', 
                                                         first_name: '{{ $student->getRawOriginal('first_name') }}', 
                                                         father_name: '{{ $student->getRawOriginal('father_name') }}', 
                                                         grandfather_name: '{{ $student->getRawOriginal('grandfather_name') }}', 
                                                         gender: '{{ strtoupper($student->gender) }}' 
                                                     })" class="p-2 hover:bg-indigo-50 text-indigo-600 rounded-xl transition-all" title="Quick Edit">
                                                         <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                                         </svg>
                                                     </button>
                                                    @if(!auth()->user()?->hasRole('Vice Principal') && !auth()->user()?->hasRole('Supervisor'))
                                                    <a href="{{ route('admin.students.edit', $student) }}" class="p-2 hover:bg-amber-50 text-amber-600 rounded-xl transition-all" title="Edit Student">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                                    </a>
                                                    <form action="{{ route('admin.students.destroy', $student) }}" method="POST" class="inline-block delete-form" data-confirm-message="Are you sure? This will delete the user account as well.">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="p-2 hover:bg-rose-50 text-rose-600 rounded-xl transition-all" title="Delete">
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                        </button>
                                                    </form>
                                                    @endif
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="p-12 text-center text-slate-400 font-medium">No students found matching your criteria.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-8 pt-8 border-t border-slate-100 flex flex-col md:flex-row justify-between items-center gap-4">
                    <div class="app-pagination">
                        {{ $students->links() }}
                    </div>
                    <div class="flex items-center gap-4">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Rows per page</span>
                        <form method="GET" action="{{ route('admin.students.index') }}">
                            @foreach(request()->except(['per_page', 'page']) as $key => $value)
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endforeach
                            <select name="per_page" onchange="this.form.submit()" class="bg-white border border-slate-200 text-slate-700 text-sm font-bold rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all px-4 py-2 pr-10">
                                <option value="15" {{ request('per_page', 50) == 15 ? 'selected' : '' }}>15</option>
                                <option value="50" {{ request('per_page', 50) == 50 ? 'selected' : '' }}>50</option>
                                <option value="100" {{ request('per_page', 50) == 100 ? 'selected' : '' }}>100</option>
                            </select>
                        </form>
                    </div>
                </div>
            </div>
        </x-ui.premium-card>
    </div>

    <!-- Quick Edit Modal -->
    <div x-data="{ 
        isOpen: false, 
        student: { id: '', first_name: '', father_name: '', grandfather_name: '', gender: '' },
        openModal(data) {
            this.student = data;
            this.isOpen = true;
        }
    }" 
    @open-quick-edit.window="openModal($event.detail)"
    x-show="isOpen" 
    class="fixed inset-0 z-[100] overflow-y-auto" 
    x-cloak>
        <div class="flex items-center justify-center min-h-screen p-4 text-center sm:p-0">
            <div x-show="isOpen" 
                 x-transition:enter="ease-out duration-300" 
                 x-transition:enter-start="opacity-0" 
                 x-transition:enter-end="opacity-100" 
                 x-transition:leave="ease-in duration-200" 
                 x-transition:leave-start="opacity-100" 
                 x-transition:leave-end="opacity-0" 
                 class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-sm" 
                 @click="isOpen = false"></div>

            <div x-show="isOpen" 
                 x-transition:enter="ease-out duration-300" 
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave="ease-in duration-200" 
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 class="relative inline-block w-full max-w-lg p-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-2xl rounded-3xl sm:my-8">
                
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h3 class="text-xl font-bold text-slate-900">Quick Edit Student</h3>
                        <p class="text-sm text-slate-500 mt-1">Update basic information instantly.</p>
                    </div>
                    <button @click="isOpen = false" class="p-2 hover:bg-slate-100 text-slate-400 hover:text-slate-600 rounded-full transition-all">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <form :action="'{{ route('admin.students.index') }}/' + student.id + '/quick-update'" method="POST">
                    @csrf
                    @method('PATCH')
                    
                    <div class="space-y-5">
                        <div class="grid grid-cols-1 gap-5">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">First Name</label>
                                <input type="text" name="first_name" x-model="student.first_name" required
                                       class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-medium">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Father Name</label>
                                <input type="text" name="father_name" x-model="student.father_name" required
                                       class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-medium">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Grandfather Name</label>
                                <input type="text" name="grandfather_name" x-model="student.grandfather_name" required
                                       class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-medium">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Gender</label>
                                <div class="grid grid-cols-2 gap-3">
                                    <label class="relative flex items-center justify-center p-3 border-2 rounded-2xl cursor-pointer transition-all"
                                           :class="student.gender === 'M' ? 'border-indigo-600 bg-indigo-50 text-indigo-700' : 'border-slate-100 hover:border-slate-200 text-slate-600'">
                                        <input type="radio" name="gender" value="M" x-model="student.gender" class="sr-only">
                                        <span class="font-bold">Male</span>
                                    </label>
                                    <label class="relative flex items-center justify-center p-3 border-2 rounded-2xl cursor-pointer transition-all"
                                           :class="student.gender === 'F' ? 'border-indigo-600 bg-indigo-50 text-indigo-700' : 'border-slate-100 hover:border-slate-200 text-slate-600'">
                                        <input type="radio" name="gender" value="F" x-model="student.gender" class="sr-only">
                                        <span class="font-bold">Female</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 flex gap-3">
                        <button type="button" @click="isOpen = false" 
                                class="flex-1 px-6 py-3.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-2xl transition-all">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="flex-1 px-6 py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-2xl shadow-xl shadow-indigo-200 transition-all transform hover:-translate-y-0.5">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>
