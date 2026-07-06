<x-admin-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="font-heading font-bold text-2xl text-slate-900">🎓 Graduates</h2>
                <p class="text-slate-500 text-sm mt-1">Students who have completed their studies and graduated.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.students.index') }}"
                   class="inline-flex items-center px-4 py-2 bg-white border border-slate-200 text-slate-700 text-sm font-semibold rounded-xl hover:bg-slate-50 transition-all gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    All Students
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Breadcrumb -->
        <x-breadcrumb :items="[
            ['label' => 'Students', 'url' => route('admin.students.index')],
            ['label' => 'Graduates', 'url' => '#']
        ]" />

        <!-- Stats Overview -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Total Graduates -->
            <x-ui.glass-card class="p-5 flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center text-white shadow-lg shadow-violet-100">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Graduates</p>
                    <p class="text-2xl font-bold text-slate-900">{{ $totalGraduates }}</p>
                </div>
            </x-ui.glass-card>

            <!-- Male Graduates -->
            <x-ui.glass-card class="p-5 flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white shadow-lg shadow-blue-100">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Male Graduates</p>
                    <p class="text-2xl font-bold text-slate-900">{{ $maleGraduates }}</p>
                    @if($totalGraduates > 0)
                        <p class="text-xs text-slate-400">{{ round(($maleGraduates / $totalGraduates) * 100) }}% of total</p>
                    @endif
                </div>
            </x-ui.glass-card>

            <!-- Female Graduates -->
            <x-ui.glass-card class="p-5 flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-pink-500 to-rose-500 flex items-center justify-center text-white shadow-lg shadow-pink-100">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Female Graduates</p>
                    <p class="text-2xl font-bold text-slate-900">{{ $femaleGraduates }}</p>
                    @if($totalGraduates > 0)
                        <p class="text-xs text-slate-400">{{ round(($femaleGraduates / $totalGraduates) * 100) }}% of total</p>
                    @endif
                </div>
            </x-ui.glass-card>
        </div>

        @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-4 flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-emerald-100 flex items-center justify-center text-emerald-600 flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <span class="text-sm font-semibold text-emerald-800">{{ session('success') }}</span>
            </div>
        @endif

        <x-ui.premium-card>
            <div class="p-6">

                <!-- Filters -->
                <form action="{{ route('admin.graduates.index') }}" method="GET">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">

                        <!-- Search -->
                        <div class="sm:col-span-2 lg:col-span-1">
                            <label for="grad_search" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Search</label>
                            <div class="relative">
                                <input type="text" name="search" id="grad_search" value="{{ request('search') }}"
                                       placeholder="Name, ID..."
                                       class="w-full pl-10 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-violet-500/20 focus:border-violet-500 transition-all">
                                <svg class="w-5 h-5 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                        </div>

                        <!-- Gender -->
                        <div>
                            <label for="grad_gender" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Gender</label>
                            <select name="gender" id="grad_gender" class="w-full py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-violet-500/20 focus:border-violet-500 transition-all">
                                <option value="">All</option>
                                <option value="M" {{ request('gender') == 'M' ? 'selected' : '' }}>Male</option>
                                <option value="F" {{ request('gender') == 'F' ? 'selected' : '' }}>Female</option>
                            </select>
                        </div>

                        <!-- Graduation Year -->
                        <div>
                            <label for="graduation_year_id" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Graduation Year</label>
                            <select name="graduation_year_id" id="graduation_year_id" class="w-full py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-violet-500/20 focus:border-violet-500 transition-all">
                                <option value="">All Years</option>
                                @foreach($academicYears as $year)
                                    <option value="{{ $year->id }}" {{ request('graduation_year_id') == $year->id ? 'selected' : '' }}>{{ $year->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Division -->
                        <div>
                            <label for="grad_division_id" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Division</label>
                            <select name="division_id" id="grad_division_id" class="w-full py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-violet-500/20 focus:border-violet-500 transition-all">
                                <option value="">All Divisions</option>
                                @foreach($divisions as $division)
                                    <option value="{{ $division->id }}" {{ request('division_id') == $division->id ? 'selected' : '' }}>{{ $division->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        @if(request()->anyFilled(['search', 'gender', 'graduation_year_id', 'division_id']))
                            <a href="{{ route('admin.graduates.index') }}" class="text-sm text-slate-500 hover:text-slate-700 font-semibold transition-all">Clear Filters</a>
                        @else
                            <span></span>
                        @endif
                        <button type="submit" class="inline-flex items-center px-6 py-2 bg-violet-600 hover:bg-violet-700 text-white text-sm font-semibold rounded-xl transition-all shadow-lg shadow-violet-100">
                            Apply Filters
                        </button>
                    </div>
                </form>

                <!-- Table -->
                <div class="mt-6 overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50">
                                <th class="p-4 text-xs font-bold text-slate-500 uppercase tracking-wider first:rounded-l-2xl">No</th>
                                <th class="p-4 text-xs font-bold text-slate-500 uppercase tracking-wider">
                                    <a href="{{ route('admin.graduates.index', array_merge(request()->except('page'), ['sort' => 'name', 'direction' => request('sort') === 'name' && request('direction') === 'asc' ? 'desc' : 'asc'])) }}" class="group flex items-center gap-1 hover:text-violet-600 transition-colors">
                                        Student Name
                                        <span class="text-slate-300 group-hover:text-violet-400">{{ (request('sort') === 'name') ? (request('direction') === 'asc' ? '↑' : '↓') : '↕' }}</span>
                                    </a>
                                </th>
                                <th class="p-4 text-xs font-bold text-slate-500 uppercase tracking-wider">
                                    <a href="{{ route('admin.graduates.index', array_merge(request()->except('page'), ['sort' => 'student_id', 'direction' => request('sort') === 'student_id' && request('direction') === 'asc' ? 'desc' : 'asc'])) }}" class="group flex items-center gap-1 hover:text-violet-600 transition-colors">
                                        Student ID
                                        <span class="text-slate-300 group-hover:text-violet-400">{{ (request('sort') === 'student_id') ? (request('direction') === 'asc' ? '↑' : '↓') : '↕' }}</span>
                                    </a>
                                </th>
                                <th class="p-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Gender</th>
                                <th class="p-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Graduated From</th>
                                <th class="p-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Academic Year</th>
                                <th class="p-4 last:rounded-r-2xl text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($graduates as $student)
                                <tr class="group hover:bg-slate-50/50 transition-all duration-200">
                                    <td class="p-4 text-sm font-medium text-slate-400">
                                        {{ ($graduates->currentPage() - 1) * $graduates->perPage() + $loop->iteration }}
                                    </td>
                                    <td class="p-4">
                                        <div class="flex items-center gap-3">
                                            <div class="relative">
                                                @if($student->photo)
                                                    <img class="h-11 w-11 rounded-2xl object-cover ring-2 ring-white shadow-sm" src="{{ Storage::url($student->photo) }}" alt="">
                                                @else
                                                    <div class="h-11 w-11 rounded-2xl bg-violet-50 border border-violet-100 flex items-center justify-center text-violet-600 font-bold text-sm ring-2 ring-white shadow-sm">
                                                        {{ substr($student->first_name, 0, 1) }}{{ substr($student->father_name, 0, 1) }}
                                                    </div>
                                                @endif
                                                <div class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full border-2 border-white bg-violet-500 flex items-center justify-center">
                                                    <svg class="w-2 h-2 text-white" fill="currentColor" viewBox="0 0 20 20"><path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zm5.99 7.176A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z"/></svg>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="text-sm font-bold text-slate-900 group-hover:text-violet-600 transition-colors">{{ $student->full_name }}</div>
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
                                        @if($student->latestPromotion)
                                            <div class="text-sm font-bold text-slate-700">{{ $student->latestPromotion->fromGradeLevel?->name ?? '—' }}</div>
                                        @else
                                            <span class="text-slate-400 text-xs">—</span>
                                        @endif
                                    </td>
                                    <td class="p-4">
                                        @if($student->latestPromotion)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-violet-50 text-violet-700 border border-violet-100">
                                                {{ $student->latestPromotion->fromAcademicYear?->name ?? '—' }}
                                            </span>
                                        @else
                                            <span class="text-slate-400 text-xs">—</span>
                                        @endif
                                    </td>
                                    <td class="p-4 text-right">
                                        <a href="{{ route('admin.students.show', $student) }}"
                                           class="p-2 hover:bg-violet-50 text-violet-600 rounded-xl transition-all inline-flex items-center" title="View Profile">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="p-16 text-center">
                                        <div class="flex flex-col items-center gap-4">
                                            <div class="w-20 h-20 rounded-3xl bg-violet-50 flex items-center justify-center text-4xl">🎓</div>
                                            <div>
                                                <p class="text-slate-700 font-bold text-lg">No Graduates Found</p>
                                                <p class="text-slate-400 text-sm mt-1">No students have graduated yet, or no results match your filters.</p>
                                            </div>
                                            @if(request()->anyFilled(['search', 'gender', 'graduation_year_id', 'division_id']))
                                                <a href="{{ route('admin.graduates.index') }}" class="px-4 py-2 bg-violet-100 hover:bg-violet-200 text-violet-700 text-sm font-bold rounded-xl transition-all">Clear Filters</a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($graduates->hasPages())
                    <div class="mt-6 flex items-center justify-between">
                        <p class="text-sm text-slate-500">
                            Showing {{ $graduates->firstItem() }}–{{ $graduates->lastItem() }} of <span class="font-bold">{{ $graduates->total() }}</span> graduates
                        </p>
                        {{ $graduates->links() }}
                    </div>
                @else
                    <div class="mt-4 text-center text-sm text-slate-400">
                        {{ $graduates->total() }} graduate{{ $graduates->total() !== 1 ? 's' : '' }} total
                    </div>
                @endif

            </div>
        </x-ui.premium-card>
    </div>
</x-admin-layout>
