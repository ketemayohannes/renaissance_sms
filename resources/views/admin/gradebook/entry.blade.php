<x-admin-layout>
    <x-slot name="header">Grade Entry</x-slot>

    @php
        $canEditGradebook = auth()->user()->can('edit subject gradebook') && ($term->is_grading_open || auth()->user()->hasRole(['Super Admin', 'Principal']));
    @endphp

    <div class="space-y-8 pb-32" x-data="gradebookState()">
        <!-- Header & Navigation -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <x-breadcrumb :items="[
                    ['label' => 'Gradebook', 'url' => route('admin.gradebook.index')],
                    ['label' => 'Score Entry', 'url' => '#']
                ]" />
                <h1 class="text-4xl font-black text-slate-900 tracking-tight mt-2">Score Entry</h1>
                <p class="text-slate-500 font-semibold mt-1">
                    {{ $subject->name }} ({{ $subject->code }}) &bull; {{ $section->gradeLevel->name }} {{ $section->name }}
                </p>
            </div>
            
            <div class="flex flex-wrap items-center gap-3">
                @if(auth()->user()->hasAnyRole(['Super Admin', 'Principal', 'Vice Principal', 'Supervisor']))
                    <a href="{{ route('admin.gradebook.marksheet', ['academic_year_id' => $academicYear->id, 'term_id' => $term->id, 'section_id' => $section->id, 'subject_id' => $subject->id]) }}" 
                       target="_blank"
                       class="px-6 py-3 bg-white text-slate-600 font-black text-[10px] uppercase tracking-widest rounded-2xl border border-slate-200 hover:bg-slate-50 shadow-sm transition-all flex items-center gap-2 group">
                        <svg class="w-4 h-4 text-slate-500 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2m32-2v2m-9-2a4 4 0 00-4-4h-3a4 4 0 00-4 4v2M9 8a4 4 0 11-8 0 4 4 0 018 0zM3 20l4-8H4l-4 8h3zm18-8l-4 8h3l4-8h-3zM9 16l4-8h-3l-4 8h3z"></path></svg>
                        Download Marksheet
                    </a>

                    <a href="{{ route('admin.gradebook.marksheet', ['academic_year_id' => $academicYear->id, 'term_id' => $term->id, 'section_id' => $section->id, 'subject_id' => $subject->id, 'summary' => 1]) }}" 
                       target="_blank"
                       class="px-6 py-3 bg-white text-indigo-600 font-black text-[10px] uppercase tracking-widest rounded-2xl border border-indigo-200 hover:bg-indigo-50 shadow-sm transition-all flex items-center gap-2 group">
                        <svg class="w-4 h-4 text-indigo-500 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2m32-2v2m-9-2a4 4 0 00-4-4h-3a4 4 0 00-4 4v2M9 8a4 4 0 11-8 0 4 4 0 018 0zM3 20l4-8H4l-4 8h3zm18-8l-4 8h3l4-8h-3zM9 16l4-8h-3l-4 8h3z"></path></svg>
                        Download Summary Marksheet
                    </a>
                @endif

                <a href="{{ route('admin.gradebook.export-template', ['academic_year_id' => $academicYear->id, 'term_id' => $term->id, 'section_id' => $section->id, 'subject_id' => $subject->id]) }}" 
                   target="_blank"
                   class="px-6 py-3 bg-white text-slate-700 font-black text-[10px] uppercase tracking-widest rounded-2xl border border-slate-200 hover:bg-slate-50 shadow-sm transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    Export Template
                </a>
            </div>
        </div>

        @if(!$term->is_grading_open)
            @if(auth()->user()->hasRole(['Super Admin', 'Principal']))
                <div class="bg-blue-50/50 backdrop-blur-md border border-blue-100 p-6 rounded-[2rem] flex items-center gap-4 animate-in fade-in slide-in-from-top-4 duration-500">
                    <div class="w-12 h-12 bg-blue-100 rounded-2xl flex items-center justify-center text-blue-600 shadow-sm">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-blue-900 font-black text-sm uppercase tracking-widest">Administrative Override Active</h3>
                        <p class="text-blue-700 text-xs font-semibold mt-0.5">Subject grading is closed for regular users, but you have administrative bypass permissions to modify grades.</p>
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
            $totalCells = $totalStudents * $totalComponents;
            $filledCells = 0;
            foreach($students as $student) {
                $studentMarks = $existingMarks->get($student->id);
                // Student is fully graded only when ALL components have a mark
                $filledCount = 0;
                foreach($gradeComponents as $component) {
                    $mark = $studentMarks?->firstWhere('assessment_template_id', $component->id);
                    if ($mark && $mark->score !== null && $mark->score !== '') {
                        $filledCount++;
                        $filledCells++;
                    }
                }
                if ($filledCount === $totalComponents && $totalComponents > 0) $gradedStudents++;
            }
            $progressPercent = $totalCells > 0 ? round(($filledCells / $totalCells) * 100) : 0;
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
                <button type="button" @if($canEditGradebook) onclick="confirmClear()" @endif
                        class="px-6 py-3.5 bg-white text-rose-600 border border-rose-100 font-black text-[10px] uppercase tracking-widest rounded-2xl hover:bg-rose-50 shadow-xl shadow-rose-100/50 transition-all flex items-center gap-3 group @if(!$canEditGradebook) opacity-50 cursor-not-allowed @endif">
                    <svg class="w-4 h-4 text-rose-500 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    Clear Data
                </button>

                <button type="button" @if($canEditGradebook) onclick="document.getElementById('importModal').classList.remove('hidden')" @endif
                        class="px-8 py-3.5 bg-slate-900 text-white font-black text-[10px] uppercase tracking-widest rounded-2xl hover:bg-slate-800 shadow-xl shadow-slate-200 transition-all flex items-center gap-3 group @if(!$canEditGradebook) opacity-50 cursor-not-allowed @endif">
                    <svg class="w-4 h-4 text-white group-hover:-translate-y-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                    Batch Import
                </button>
            </div>
        </div>

        <!-- Import Modal -->
        <div id="importModal" class="fixed z-[60] inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="document.getElementById('importModal').classList.add('hidden')"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-[2rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-white">
                    <form action="{{ route('admin.gradebook.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="academic_year_id" value="{{ $academicYear->id }}">
                        <input type="hidden" name="term_id" value="{{ $term->id }}">
                        <input type="hidden" name="section_id" value="{{ $section->id }}">
                        <input type="hidden" name="subject_id" value="{{ $subject->id }}">
                        
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
                                        <div x-show="!importFileName" class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Drop file here or click to browse</div>
                                        <div x-show="importFileName" x-text="importFileName" class="text-xs font-bold text-indigo-600 truncate px-4"></div>
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

        <form action="{{ route('admin.gradebook.store') }}" method="POST" id="gradeForm" @submit="isSaving = true">
            @csrf
            <input type="hidden" name="academic_year_id" value="{{ $academicYear->id }}">
            <input type="hidden" name="section_id" value="{{ $section->id }}">
            <input type="hidden" name="subject_id" value="{{ $subject->id }}">
            <input type="hidden" name="term_id" value="{{ $term->id }}">

            @include('partials.gradebook.score-grid', [
                'discardUrl' => route('admin.gradebook.index'),
                'lockedTextClass' => 'disabled:text-slate-700',
            ])
        </form>
    </div>

    @push('scripts')
    @include('partials.gradebook.score-grid-script')
    @endpush
</x-admin-layout>
