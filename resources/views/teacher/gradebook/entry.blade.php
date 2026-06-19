<x-teacher-layout>
    @php
        $user = auth()->user();
        $canEditGradebook = $term->is_grading_open || ($user && ($user->hasRole('Super Admin') || $user->hasRole('Principal')));
    @endphp
    <div class="space-y-8 pb-32" x-data="gradebookState()">
        <!-- Header & Navigation -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <x-breadcrumb :items="[
                    ['label' => 'My Classes', 'url' => route('teacher.classes.index')],
                    ['label' => 'Grade Entry', 'url' => '#']
                ]" />
                <h1 class="text-4xl font-black text-slate-900 tracking-tight mt-2">Score Entry</h1>
                <div class="flex items-center gap-4 mt-2">
                    <p class="text-slate-500 font-semibold">
                        {{ $subject->name }} ({{ $subject->code }}) &bull; {{ $section->gradeLevel->name }} {{ $section->name }}
                    </p>
                </div>
            </div>
            
            <div class="flex items-center gap-3">
                <form action="{{ route('teacher.gradebook.entry', $assignment->id) }}" method="GET" class="flex items-center gap-2">
                    <select name="term_id" onchange="this.form.submit()" class="text-sm border-slate-200 rounded-xl focus:ring-indigo-500 py-2 pl-4 pr-8 font-bold text-slate-700 bg-white shadow-sm border">
                        @foreach($terms as $t)
                            <option value="{{ $t->id }}" {{ $term->id == $t->id ? 'selected' : '' }}>{{ $t->name }} ({{ $academicYear->name }})</option>
                        @endforeach
                    </select>
                </form>
            </div>
        </div>

        @if(!$term->is_grading_open)
            @if($canEditGradebook)
                <div class="bg-blue-50/50 backdrop-blur-md border border-blue-100 p-6 rounded-[2rem] flex items-center gap-4 animate-in fade-in slide-in-from-top-4 duration-500">
                    <div class="w-12 h-12 bg-blue-100 rounded-2xl flex items-center justify-center text-blue-600 shadow-sm">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-blue-900 font-black text-sm uppercase tracking-widest">Administrative Override Active</h3>
                        <p class="text-blue-700 text-xs font-semibold mt-0.5">Subject grading is closed for regular users, but you have edit permissions as a Super Admin or Principal.</p>
                    </div>
                </div>
            @else
                <div class="bg-red-50/50 backdrop-blur-md border border-red-100 p-6 rounded-[2rem] flex items-center gap-4 animate-in fade-in slide-in-from-top-4 duration-500">
                    <div class="w-12 h-12 bg-red-100 rounded-2xl flex items-center justify-center text-red-600 shadow-sm">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-red-900 font-black text-sm uppercase tracking-widest">Grading Disabled</h3>
                        <p class="text-red-700 text-xs font-semibold mt-0.5">Subject grading is currently closed for this term. Records are in read-only mode.</p>
                    </div>
                </div>
            @endif
        @endif


        <!-- Statistics Section -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 animate-in fade-in slide-in-from-bottom-4 duration-700">
            <!-- Class Average (Dual Display) -->
            <div class="bg-white/60 backdrop-blur-xl border border-white p-6 rounded-[2rem] shadow-xl shadow-indigo-100/50 relative overflow-hidden group hover:scale-[1.02] transition-transform">
                <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-50 rounded-full -mr-16 -mt-16 transition-transform group-hover:scale-110"></div>
                <div class="relative">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-8 h-8 rounded-xl bg-indigo-100 flex items-center justify-center text-indigo-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                        <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest">Section Average</h3>
                    </div>
                    
                    <div class="flex items-end gap-1 mt-4">
                        <div class="text-5xl font-black text-indigo-900 tracking-tight">
                            {{ number_format($classAverage, 2) }}
                        </div>
                        <div class="text-lg text-indigo-300 font-bold mb-1.5">%</div>
                        <div class="ml-4 pb-1">
                            <div class="text-[10px] font-black text-slate-400 uppercase tracking-tighter leading-none mb-1">Graded Only</div>
                            <div class="text-lg font-black text-indigo-600 leading-none">{{ number_format($gradedAverage, 2) }}%</div>
                        </div>
                    </div>
                    <p class="text-[10px] text-slate-400 font-bold mt-2 uppercase tracking-tight">Based on {{ $students->count() }} students</p>
                </div>
            </div>

            <!-- Top Performers -->
            <div class="bg-white/60 backdrop-blur-xl border border-white p-6 rounded-[2rem] shadow-xl shadow-emerald-100/50 relative overflow-hidden group hover:scale-[1.02] transition-transform">
                 <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-50 rounded-full -mr-16 -mt-16 transition-transform group-hover:scale-110"></div>
                 <div class="relative">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-8 h-8 rounded-xl bg-emerald-100 flex items-center justify-center text-emerald-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest">Top Performers</h3>
                    </div>
                    <div class="space-y-3">
                        @forelse($top3Students as $stat)
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-5 h-5 rounded-md bg-emerald-100 text-emerald-700 text-[10px] font-bold flex items-center justify-center">{{ $loop->iteration }}</span>
                                    <span class="text-xs font-bold text-slate-700 truncate max-w-[120px]" title="{{ $stat['student']->full_name }}">{{ $stat['student']->full_name }}</span>
                                </div>
                                <span class="text-xs font-black text-emerald-600">{{ number_format($stat['total'], 1) }}</span>
                            </div>
                        @empty
                            <div class="text-xs text-slate-400 font-medium italic">Not enough data</div>
                        @endforelse
                    </div>
                 </div>
            </div>

            <!-- Needs Attention (Bottom 3) -->
            <div class="bg-white/60 backdrop-blur-xl border border-white p-6 rounded-[2rem] shadow-xl shadow-amber-100/50 relative overflow-hidden group hover:scale-[1.02] transition-transform">
                 <div class="absolute top-0 right-0 w-32 h-32 bg-amber-50 rounded-full -mr-16 -mt-16 transition-transform group-hover:scale-110"></div>
                 <div class="relative">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-8 h-8 rounded-xl bg-amber-100 flex items-center justify-center text-amber-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        </div>
                        <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest">Needs Attention</h3>
                    </div>
                    <div class="space-y-3">
                        @forelse($bottom3Students as $stat)
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-5 h-5 rounded-md bg-amber-100 text-amber-700 text-[10px] font-bold flex items-center justify-center">{{ $loop->iteration }}</span>
                                    <span class="text-xs font-bold text-slate-700 truncate max-w-[120px]" title="{{ $stat['student']->full_name }}">{{ $stat['student']->full_name }}</span>
                                </div>
                                <span class="text-xs font-black text-amber-600">{{ number_format($stat['total'], 1) }}</span>
                            </div>
                        @empty
                            <div class="text-xs text-slate-400 font-medium italic">Not enough data</div>
                        @endforelse
                    </div>
                 </div>
            </div>
        </div>

        @php
            $totalStudents = $students->count();
            $totalComponents = $gradeComponents->count();
            $gradedStudents = 0;
            foreach($students as $student) {
                $studentMarks = $existingMarks->get($student->id);
                // Student is fully graded only when ALL components have a mark
                $filledCount = 0;
                foreach($gradeComponents as $component) {
                    $mark = $studentMarks?->firstWhere('assessment_template_id', $component->id);
                    if ($mark && $mark->score !== null && $mark->score !== '') $filledCount++;
                }
                if ($filledCount === $totalComponents && $totalComponents > 0) $gradedStudents++;
            }
            $progressPercent = $totalStudents > 0 ? round(($gradedStudents / $totalStudents) * 100) : 0;
        @endphp

        <!-- Progress and Actions Panel -->
        <div class="bg-white/40 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-xl shadow-slate-200/50 p-8 flex flex-col lg:flex-row lg:items-center justify-between gap-8">
            <div class="flex-1 max-w-2xl">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-2 h-2 rounded-full bg-indigo-500 animate-pulse"></div>
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Grading Progress</span>
                    </div>
                    <span class="text-xs font-black text-slate-900" id="progressLabel">{{ $gradedStudents }} <span class="text-slate-400">/ {{ $totalStudents }}</span></span>
                </div>
                <div class="w-full bg-slate-200/50 rounded-full h-3 p-0.5 overflow-hidden">
                    <div class="h-full rounded-full transition-all duration-500 ease-out bg-gradient-to-r from-indigo-500 to-indigo-600" 
                         id="progressBar"
                         style="width: {{ $progressPercent }}%"></div>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('teacher.gradebook.export', ['assignment' => $assignment->id, 'term_id' => $term->id]) }}" 
                   target="_blank"
                   class="px-6 py-3.5 bg-white text-indigo-600 border border-indigo-100 font-black text-[10px] uppercase tracking-widest rounded-2xl hover:bg-indigo-50 shadow-xl shadow-indigo-100/50 transition-all flex items-center gap-3 group">
                    <svg class="w-4 h-4 text-indigo-500 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    Export Template
                </a>

                <a href="{{ route('teacher.gradebook.marksheet', ['assignment' => $assignment->id, 'term_id' => $term->id]) }}" 
                   target="_blank"
                   class="px-6 py-3.5 bg-white text-slate-600 border border-slate-100 font-black text-[10px] uppercase tracking-widest rounded-2xl hover:bg-slate-50 shadow-xl shadow-slate-100/50 transition-all flex items-center gap-3 group">
                    <svg class="w-4 h-4 text-slate-500 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2m32-2v2m-9-2a4 4 0 00-4-4h-3a4 4 0 00-4 4v2M9 8a4 4 0 11-8 0 4 4 0 018 0zM3 20l4-8H4l-4 8h3zm18-8l-4 8h3l4-8h-3zM9 16l4-8h-3l-4 8h3z"></path></svg>
                    Download Marksheet
                </a>

                <button type="button" @if($canEditGradebook) onclick="document.getElementById('importModal').classList.remove('hidden')" @endif
                        class="px-6 py-3.5 bg-emerald-600 text-white font-black text-[10px] uppercase tracking-widest rounded-2xl hover:bg-emerald-700 shadow-xl shadow-emerald-100/50 transition-all flex items-center gap-3 group @if(!$canEditGradebook) opacity-50 cursor-not-allowed @endif">
                    <svg class="w-4 h-4 text-white group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                    Import Grades
                </button>

                <button type="button" @if($canEditGradebook) onclick="confirmClear()" @endif
                        class="px-6 py-3.5 bg-white text-rose-600 border border-rose-100 font-black text-[10px] uppercase tracking-widest rounded-2xl hover:bg-rose-50 shadow-xl shadow-rose-100/50 transition-all flex items-center gap-3 group @if(!$canEditGradebook) opacity-50 cursor-not-allowed @endif">
                    <svg class="w-4 h-4 text-rose-500 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    Clear Data
                </button>
            </div>
        </div>

        <!-- Import Modal -->
        <div id="importModal" class="fixed z-[60] inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="document.getElementById('importModal').classList.add('hidden')"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-[2rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-white">
                    <form action="{{ route('teacher.gradebook.import', $assignment->id) }}" method="POST" enctype="multipart/form-data" @submit="isSaving = true">
                        @csrf
                        <input type="hidden" name="term_id" value="{{ $term->id }}">
                        
                        <div class="bg-white px-8 pt-8 pb-6">
                            <div class="flex items-center gap-4 mb-6">
                                <div class="w-12 h-12 bg-emerald-100 rounded-2xl flex items-center justify-center text-emerald-600 shadow-sm transition-transform hover:scale-110">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                                </div>
                                <div>
                                    <h3 class="text-xl font-black text-slate-900 tracking-tight" id="modal-title">Batch Import</h3>
                                    <p class="text-slate-500 font-semibold text-xs mt-0.5">Upload the filled CSV template</p>
                                </div>
                            </div>
                            
                            <div class="space-y-4">
                                <div class="bg-slate-50 border-2 border-dashed border-slate-200 rounded-2xl p-8 text-center transition-colors hover:border-indigo-400 group relative">
                                    <input type="file" name="file" accept=".csv,.txt" required 
                                           class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                                           @change="importFileName = $event.target.files[0] ? $event.target.files[0].name : ''">
                                    <div class="space-y-2">
                                        <svg class="w-8 h-8 text-slate-400 mx-auto group-hover:text-indigo-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        <div id="grade-import-placeholder" x-show="!importFileName" class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Drop file here or click to browse</div>
                                        <div id="grade-import-filename" x-show="importFileName" x-text="importFileName" class="text-xs font-bold text-indigo-600 truncate px-4"></div>
                                    </div>
                                </div>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest text-center px-4 leading-relaxed">Ensure Student IDs match exactly with the system records to avoid validation failures.</p>
                            </div>
                        </div>

                        <div class="bg-slate-50 px-8 py-6 flex items-center justify-end gap-3 border-t border-slate-100">
                            <button type="button" onclick="document.getElementById('importModal').classList.add('hidden')" 
                                    class="px-6 py-2.5 text-slate-500 font-black text-[10px] uppercase tracking-widest hover:text-slate-900 transition-colors">
                                Cancel
                            </button>
                            <button type="submit" class="px-8 py-3 bg-emerald-600 text-white font-black text-[10px] uppercase tracking-widest rounded-xl hover:bg-emerald-700 shadow-lg shadow-emerald-200 transition-all">
                                Execute Import
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <form action="{{ route('teacher.gradebook.store', $assignment->id) }}" method="POST" id="gradeForm" @submit="isSaving = true">
            @csrf
            <input type="hidden" name="academic_year_id" value="{{ $academicYear->id }}">
            <input type="hidden" name="section_id" value="{{ $section->id }}">
            <input type="hidden" name="subject_id" value="{{ $subject->id }}">
            <input type="hidden" name="term_id" value="{{ $term->id }}">

            <!-- Grade Entry Table -->
            <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-2xl shadow-slate-200/50 overflow-hidden relative">
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left border-separate border-spacing-0">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200">
                                <th class="px-2 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider text-center w-12 pb-2">No</th>
                                <th class="px-3 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider sticky left-0 z-30 bg-slate-50 shadow-[4px_0_12px_-4px_rgba(0,0,0,0.05)] pb-2 min-w-[250px]">Student Full Name</th>
                                <th class="px-2 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider text-center w-16">Gender</th>
                                
                                @foreach($gradeComponents as $component)
                                    <th class="px-4 py-3 border-b border-slate-200 min-w-[8rem] text-center group cursor-default relative hover:bg-slate-100 transition-colors">
                                        <div class="flex flex-col items-center justify-center w-full h-full mx-auto px-1 py-1">
                                            <span class="text-[10px] font-bold text-slate-600 uppercase tracking-wider whitespace-normal leading-3">{{ $component->name }}</span>
                                            <span class="text-[9px] font-semibold text-indigo-500 tracking-tighter mt-1">Max: {{ $component->max_score }}</span>
                                        </div>
                                    </th>
                                @endforeach

                                @php
                                    $totalCaCapacity = $gradeComponents->filter(fn($c) => $c->assessmentType?->code !== 'FINAL')->sum('max_score');
                                @endphp
                                <th class="px-4 py-3 border-b border-slate-200 text-center font-bold text-indigo-600 text-[11px] bg-indigo-50/30 uppercase tracking-wider min-w-[6rem]">
                                    CA ({{ (int)$totalCaCapacity }}%)
                                </th>
                                <th class="px-4 py-3 border-b border-slate-200 text-center font-bold text-slate-700 text-[11px] uppercase tracking-wider min-w-[6rem]">
                                    Total
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse($students as $index => $student)
                                <tr class="group hover:bg-slate-50 transition-colors student-row" data-student-id="{{ $student->id }}">
                                    <td class="px-2 py-2 text-xs font-medium text-slate-500 text-center">{{ $index + 1 }}</td>
                                    <td class="px-3 py-2 whitespace-nowrap sticky left-0 bg-white z-20 group-hover:bg-slate-50 transition-colors shadow-[4px_0_12px_-4px_rgba(0,0,0,0.05)]">
                                        <div class="flex items-center gap-2">
                                            <div class="w-6 h-6 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-600 font-bold text-[10px] ring-1 ring-white shadow-sm flex-shrink-0">
                                                {{ substr($student->first_name ?? $student->full_name, 0, 1) }}{{ substr($student->last_name ?? '', 0, 1) }}
                                            </div>
                                            <div class="min-w-0">
                                                <div class="font-semibold text-slate-900 text-xs" title="{{ $student->full_name }}">{{ $student->full_name }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-1 py-2 text-xs font-medium text-slate-500 text-center uppercase">
                                        <span class="inline-flex items-center justify-center w-5 h-5 rounded-md {{ ($student->gender ?? 'M') === 'F' ? 'bg-pink-50 text-pink-600' : 'bg-blue-50 text-blue-600' }} font-bold text-[10px]">
                                            {{ substr($student->gender ?? 'M', 0, 1) }}
                                        </span>
                                    </td>
                                    
                                    @php
                                        $totalScore = 0;
                                        $caScore = 0;
                                    @endphp
                                    @foreach($gradeComponents as $component)
                                        @php
                                            $studentMarks = $existingMarks->get($student->id);
                                            $mark = $studentMarks ? $studentMarks->first(fn($m) => (int)$m->assessment_template_id === (int)$component->id) : null;
                                            $score = $mark ? $mark->score : 0;
                                            $val = $mark ? $mark->score : '';
                                            $isFinal = ($component->assessmentType?->code === 'FINAL');
                                            
                                            $totalScore += $score;
                                            if (!$isFinal) {
                                                $caScore += $score;
                                            }
                                        @endphp
                                        <td class="p-0.5 text-center relative group/cell min-w-[3rem]">
                                            <input type="text" inputmode="decimal"
                                                   name="marks[{{ $student->id }}][{{ $component->id }}][score]" 
                                                   value="{{ $val }}" 
                                                   max="{{ $component->max_score }}" 
                                                   class="w-full text-center text-xs font-medium border-slate-200 rounded focus:ring-1 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all hover:bg-slate-50 disabled:bg-slate-50 disabled:text-slate-300 mark-input py-1 px-0"
                                                   data-is-final="{{ $component->assessmentType?->code === 'FINAL' ? '1' : '0' }}"
                                                   oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1'); calculateRow(this.closest('tr'));"
                                                   @if(!$canEditGradebook) disabled @endif>
                                        </td>
                                    @endforeach

                                    <td class="px-1 py-2 text-center bg-indigo-50/20">
                                        <span class="inline-block px-1.5 py-0.5 rounded bg-indigo-100 text-indigo-700 text-xs font-bold student-ca min-w-[2.5rem]">{{ $caScore }}</span>
                                    </td>

                                    <td class="px-1 py-2 text-center">
                                        @php
                                            $displayTotal = $totalScore;
                                            $termTotalMark = null;
                                            if (isset($termTotalTemplate) && $termTotalTemplate && $studentMarks) {
                                                $termTotalMark = $studentMarks->first(fn($m) => (int)$m->assessment_template_id === (int)$termTotalTemplate->id);
                                            }
                                            
                                            if ($displayTotal == 0 && $termTotalMark) {
                                                $displayTotal = $termTotalMark->score;
                                            }
                                        @endphp
                                        <span class="inline-block px-1.5 py-0.5 rounded bg-slate-100 text-slate-700 text-xs font-bold student-total min-w-[2.5rem]" data-master-total="{{ $termTotalMark ? $termTotalMark->score : 0 }}">{{ number_format($displayTotal, 2) }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $gradeComponents->count() + 5 }}" class="px-12 py-20 text-center">
                                        <div class="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center text-slate-300 mx-auto mb-4">
                                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354l1.1 3.392a1 1 0 00.95.69h3.462a1 1 0 01.59 1.807l-2.8 2.034a1 1 0 00-.36 1.118l1.07 3.292a1 1 0 01-1.537 1.117l-2.8-2.034a1 1 0 00-1.176 0l-2.8 2.034a1 1 0 01-1.537-1.117l1.07-3.292a1 1 0 00-.36-1.118l-2.8-2.034a1 1 0 01.59-1.807h3.462a1 1 0 00.95-.69L12 4.354z"></path></svg>
                                        </div>
                                        <h3 class="text-sm font-bold text-slate-900">No students found</h3>
                                        <p class="text-slate-500 text-xs mt-1">Please verify the section enrollment.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Floating Bottom Command Bar -->
            <div class="fixed bottom-6 left-1/2 -translate-x-1/2 z-40 flex items-center gap-1 p-1.5 bg-white/90 backdrop-blur-xl border border-slate-200 shadow-2xl shadow-slate-200/50 rounded-2xl animate-in slide-in-from-bottom-12 duration-500">
                <div class="flex items-center gap-3 pl-4 pr-2 border-r border-slate-100">
                    <div class="flex flex-col">
                        <span class="text-[10px] uppercase tracking-wider font-bold text-slate-400 leading-none" x-text="hasUnsavedChanges ? 'Unsaved' : 'Synced'">Status</span>
                        <span class="text-sm font-bold text-slate-700 leading-tight" id="ungradedCount">{{ $totalStudents - $gradedStudents }} Ungraded</span>
                    </div>
                </div>

                <a href="{{ route('teacher.classes.show', $assignment->id) }}" class="px-5 py-2.5 text-slate-500 font-bold text-xs hover:text-slate-900 transition-colors uppercase tracking-wider">
                    Discard
                </a>
                
                @if($canEditGradebook)
                    <button type="submit" class="px-6 py-2.5 bg-indigo-600 text-white font-bold text-xs uppercase tracking-widest rounded-xl hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-100 flex items-center gap-2 group">
                        Save Changes
                        <svg class="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                    </button>
                @endif
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        // Alpine.js component for gradebook state
        function gradebookState() {
            return {
                hasUnsavedChanges: false,
                isSaving: false,
                importFileName: '',
                
                init() {
                    // Track changes on all inputs
                    this.$nextTick(() => {
                        const form = document.getElementById('gradeForm');
                        if (form) {
                            form.querySelectorAll('input[type="text"]').forEach(input => {
                                input.addEventListener('change', () => {
                                    this.hasUnsavedChanges = true;
                                });
                            });
                        }
                    });
                    
                    window.addEventListener('beforeunload', (e) => {
                        if (this.hasUnsavedChanges && !this.isSaving) {
                            e.preventDefault();
                            e.returnValue = '';
                        }
                    });
                }
            }
        }

        // Grade Import File Display
        document.addEventListener('DOMContentLoaded', function() {
            const gradeFileInput = document.querySelector('input[name="file"][accept=".csv,.txt"]');
            const placeholder = document.getElementById('grade-import-placeholder');
            const fileNameDisplay = document.getElementById('grade-import-filename');

            if (gradeFileInput && placeholder && fileNameDisplay) {
                gradeFileInput.addEventListener('change', (e) => {
                    if (e.target.files.length > 0) {
                        const name = e.target.files[0].name;
                        fileNameDisplay.textContent = name;
                        fileNameDisplay.classList.remove('hidden');
                        placeholder.classList.add('hidden');
                    } else {
                        fileNameDisplay.textContent = '';
                        fileNameDisplay.classList.add('hidden');
                        placeholder.classList.remove('hidden');
                    }
                });
            }
        });

        function calculateRow(row, skipDirty = false) {
            const inputs = row.querySelectorAll('.mark-input');
            let grandTotal = 0;
            let caTotal = 0;
            let hasAnyValue = false;
            
            inputs.forEach(input => {
                const max = parseFloat(input.getAttribute('max'));
                const isFinal = input.getAttribute('data-is-final') === '1';
                
                if (input.value.trim() !== '') {
                    hasAnyValue = true;
                }
                
                let val = parseFloat(input.value) || 0;
                
                // Reset validation styles
                input.classList.remove('border-red-500', 'bg-red-50', 'text-red-600', 'focus:border-red-500', 'focus:ring-red-500');
                
                if (val > max) {
                    // Warning style instead of auto-correct
                    input.classList.add('border-red-500', 'bg-red-50', 'text-red-600', 'focus:border-red-500', 'focus:ring-red-500');
                }
                
                if (val < 0) {
                    val = 0;
                    input.value = 0;
                }
                
                grandTotal += val;
                if (!isFinal) {
                    caTotal += val;
                }
            });
            
            // Update CA and Total
            const caEl = row.querySelector('.student-ca');
            if (caEl) caEl.innerText = caTotal.toFixed(2);

            const totalEl = row.querySelector('.student-total');
            if (totalEl) {
                if (!hasAnyValue && totalEl.hasAttribute('data-master-total')) {
                    const masterTotal = parseFloat(totalEl.getAttribute('data-master-total')) || 0;
                    if (masterTotal > 0) {
                        totalEl.innerText = masterTotal.toFixed(2);
                        return; // Keep master total, don't flag as unsaved unless changed
                    }
                }
                totalEl.innerText = grandTotal.toFixed(2);
            }
            
            // Mark as unsaved
            if (!skipDirty) {
                const rootElement = document.querySelector('.space-y-8');
                if (rootElement && window.Alpine) {
                    const data = Alpine.$data(rootElement);
                    if (data) data.hasUnsavedChanges = true;
                }
                // Live progress update on every change
                updateGradingProgress();
            }
        }

        function clearGradebook() {
            document.querySelectorAll('.mark-input').forEach(input => {
                input.value = '';
            });
            document.querySelectorAll('.student-row').forEach(row => {
                calculateRow(row);
            });
        }

        function confirmClear() {
            window.confirmUI({
                type: 'danger',
                title: 'Clear All Marks',
                message: 'Are you sure you want to clear ALL entered marks in this gradebook? This action cannot be undone.',
                buttonText: 'Yes, Clear All',
                callback: () => {
                    clearGradebook();
                }
            });
        }
        
        const TOTAL_STUDENTS = {{ $totalStudents }};
        const COMPONENTS_PER_STUDENT = {{ $gradeComponents->count() }};

        function updateGradingProgress() {
            let fullyGraded = 0;
            document.querySelectorAll('.student-row').forEach(row => {
                const inputs = row.querySelectorAll('.mark-input');
                const allFilled = inputs.length > 0 && Array.from(inputs).every(inp => inp.value.trim() !== '');
                if (allFilled) fullyGraded++;
            });

            const ungraded = TOTAL_STUDENTS - fullyGraded;
            const pct = TOTAL_STUDENTS > 0 ? Math.round((fullyGraded / TOTAL_STUDENTS) * 100) : 0;

            // Update ungraded counter
            const ungradedEl = document.getElementById('ungradedCount');
            if (ungradedEl) ungradedEl.textContent = ungraded + ' Ungraded';

            // Update progress label
            const labelEl = document.getElementById('progressLabel');
            if (labelEl) labelEl.innerHTML = fullyGraded + ' <span class="text-slate-400">/ ' + TOTAL_STUDENTS + '</span>';

            // Update progress bar
            const barEl = document.getElementById('progressBar');
            if (barEl) {
                barEl.style.width = pct + '%';
                if (pct === 100) {
                    barEl.classList.remove('from-indigo-500', 'to-indigo-600');
                    barEl.classList.add('bg-emerald-500');
                } else {
                    barEl.classList.add('from-indigo-500', 'to-indigo-600');
                    barEl.classList.remove('bg-emerald-500');
                }
            }
        }
        
        // Keyboard navigation
        document.addEventListener('DOMContentLoaded', function() {
            // Initial calculation (skipping dirty flag)
            document.querySelectorAll('.student-row').forEach(row => {
                calculateRow(row, true);
            });

            // Initial progress sync
            updateGradingProgress();

            const table = document.querySelector('table');
            if (!table) return;
            
            table.addEventListener('keydown', function(e) {
                if (e.target.tagName !== 'INPUT') return;
                
                const cell = e.target.closest('td');
                const row = cell.closest('tr');
                const rows = Array.from(table.querySelectorAll('tbody tr.student-row'));
                const rowIndex = rows.indexOf(row);
                const allCellsInRow = Array.from(row.querySelectorAll('td'));
                const cellIndex = allCellsInRow.indexOf(cell);
                
                let nextInput = null;
                
                if (e.key === 'Enter' || e.key === 'ArrowDown') {
                    const nextRow = rows[rowIndex + 1];
                    if (nextRow) {
                        nextInput = nextRow.querySelectorAll('td')[cellIndex]?.querySelector('input');
                        if (nextInput) e.preventDefault();
                    }
                } else if (e.key === 'ArrowUp') {
                    const prevRow = rows[rowIndex - 1];
                    if (prevRow) {
                        nextInput = prevRow.querySelectorAll('td')[cellIndex]?.querySelector('input');
                        if (nextInput) e.preventDefault();
                    }
                } else if (e.key === 'ArrowRight') {
                    if (e.target.value === '' || e.target.selectionEnd === e.target.value.length) {
                        const rowInputs = Array.from(row.querySelectorAll('.mark-input'));
                        const inputIndex = rowInputs.indexOf(e.target);
                        nextInput = rowInputs[inputIndex + 1];
                        if (nextInput) e.preventDefault();
                    }
                } else if (e.key === 'ArrowLeft') {
                    if (e.target.value === '' || e.target.selectionStart === 0) {
                        const rowInputs = Array.from(row.querySelectorAll('.mark-input'));
                        const inputIndex = rowInputs.indexOf(e.target);
                        nextInput = rowInputs[inputIndex - 1];
                        if (nextInput) e.preventDefault();
                    }
                }
                
                if (nextInput) {
                    nextInput.focus();
                    nextInput.select();
                }
            });
        });
    </script>
    @endpush
</x-teacher-layout>
