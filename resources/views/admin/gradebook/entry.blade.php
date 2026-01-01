<x-admin-layout>
    <x-slot name="header">Grade Entry</x-slot>

    <div class="space-y-8" x-data="gradebookState()">
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
            
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.gradebook.export-template', ['academic_year_id' => $academicYear->id, 'term_id' => $term->id, 'section_id' => $section->id, 'subject_id' => $subject->id]) }}" 
                   class="px-6 py-3 bg-white text-slate-700 font-black text-[10px] uppercase tracking-widest rounded-2xl border border-slate-200 hover:bg-slate-50 shadow-sm transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    Export Template
                </a>
            </div>
        </div>

        @if(!$term->is_grading_open)
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

        @php
            $totalStudents = $students->count();
            $gradedStudents = 0;
            foreach($students as $student) {
                $studentMarks = $existingMarks->get($student->id);
                if($studentMarks && $studentMarks->count() > 0) $gradedStudents++;
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
                    <span class="text-xs font-black text-slate-900">{{ $gradedStudents }} <span class="text-slate-400">/ {{ $totalStudents }}</span></span>
                </div>
                <div class="w-full bg-slate-200/50 rounded-full h-3 p-0.5 overflow-hidden">
                    <div class="h-full rounded-full transition-all duration-1000 ease-out {{ $progressPercent == 100 ? 'bg-emerald-500' : 'bg-gradient-to-r from-indigo-500 to-indigo-600' }}" 
                         style="width: {{ $progressPercent }}%"></div>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button type="button" @if($term->is_grading_open) onclick="document.getElementById('importModal').classList.remove('hidden')" @endif
                        class="px-8 py-3.5 bg-slate-900 text-white font-black text-[10px] uppercase tracking-widest rounded-2xl hover:bg-slate-800 shadow-xl shadow-slate-200 transition-all flex items-center gap-3 group @if(!$term->is_grading_open) opacity-50 cursor-not-allowed @endif">
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
                                    <input type="file" name="file" accept=".csv,.txt" required class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                                    <div class="space-y-2">
                                        <svg class="w-8 h-8 text-slate-400 mx-auto group-hover:text-indigo-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Drop file here or click to browse</div>
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

        <!-- Grade Entry Table -->
        <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-2xl shadow-slate-200/50 overflow-hidden relative">
            <form action="{{ route('admin.gradebook.store') }}" method="POST" id="gradeForm">
                @csrf
                <input type="hidden" name="academic_year_id" value="{{ $academicYear->id }}">
                <input type="hidden" name="section_id" value="{{ $section->id }}">
                <input type="hidden" name="subject_id" value="{{ $subject->id }}">
                <input type="hidden" name="term_id" value="{{ $term->id }}">

                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left border-separate border-spacing-0">
                        <thead class="sticky top-0 z-30">
                            <tr>
                                <th class="px-4 py-6 bg-slate-50/90 backdrop-blur-md border-b border-r border-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center min-w-[50px]">No</th>
                                <th class="px-6 py-6 bg-slate-50/90 backdrop-blur-md border-b border-r border-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-widest sticky left-0 z-40">Student INFO</th>
                                <th class="px-4 py-6 bg-slate-50/90 backdrop-blur-md border-b border-r border-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center min-w-[60px]">Gender</th>
                                
                                @foreach($gradeComponents as $component)
                                    <th class="px-4 py-6 bg-white border-b border-r border-slate-100 text-center min-w-[120px] whitespace-normal">
                                        <div class="text-[10px] font-black text-slate-600 uppercase tracking-widest leading-tight">
                                            {{ $component->name }}
                                        </div>
                                        <div class="text-[8px] font-black text-indigo-500 uppercase mt-1 tracking-tighter">
                                            Max: {{ $component->max_score }} &bull; {{ $component->weight }}%
                                        </div>
                                    </th>
                                @endforeach

                                <th class="px-6 py-6 bg-indigo-900 border-b border-slate-800 text-center font-black text-white text-[11px] uppercase tracking-widest shadow-lg min-w-[100px] whitespace-normal">Total</th>
                                <th class="px-6 py-6 bg-indigo-950 border-b border-slate-900 text-center font-black text-indigo-300 text-[11px] uppercase tracking-widest shadow-lg min-w-[100px] whitespace-normal">Average</th>
                                <th class="px-6 py-6 bg-emerald-600 border-b border-emerald-700 text-center font-black text-white text-[11px] uppercase tracking-widest shadow-lg rounded-tr-[2rem] min-w-[100px] whitespace-normal">Rank</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($students as $index => $student)
                                <tr class="group hover:bg-indigo-50/30 transition-all student-row" data-student-id="{{ $student->id }}">
                                    <td class="px-4 py-4 border-r border-slate-100 text-[10px] font-black text-slate-400 text-center bg-slate-50/30">{{ $index + 1 }}</td>
                                    <td class="px-6 py-4 border-r border-slate-100 sticky left-0 bg-white/95 backdrop-blur-sm z-20 group-hover:bg-indigo-50 transition-colors shadow-[4px_0_8px_-4px_rgba(0,0,0,0.05)]">
                                        <div class="flex flex-col">
                                            <span class="text-sm font-bold text-slate-900">{{ $student->full_name }}</span>
                                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-0.5">{{ $student->student_id }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 border-r border-slate-100 text-[10px] font-black text-slate-500 text-center uppercase">{{ $student->gender ?? 'M' }}</td>
                                    
                                    @php
                                        $totalScore = 0;
                                    @endphp
                                    @foreach($gradeComponents as $component)
                                        @php
                                            $mark = $existingMarks->get($student->id)?->firstWhere('assessment_template_id', $component->id);
                                            $score = $mark ? $mark->score : 0;
                                            $totalScore += $score;
                                        @endphp
                                        <td class="p-0 border-r border-slate-100 text-center relative group/cell">
                                            <input type="number" 
                                                   name="marks[{{ $student->id }}][{{ $component->id }}][score]" 
                                                   value="{{ $mark?->score }}" 
                                                   min="0" 
                                                   max="{{ $component->max_score }}" 
                                                   step="0.01"
                                                   class="w-full h-12 text-center text-sm font-black border-0 focus:ring-0 focus:bg-indigo-50/50 bg-transparent transition-all hover:bg-slate-50/50 disabled:bg-slate-100/50 disabled:text-slate-400 mark-input"
                                                   onchange="calculateRow(this.closest('tr'))"
                                                   @if(!$term->is_grading_open) disabled @endif>
                                            @if($term->is_grading_open)
                                                <div class="absolute inset-0 border-b-2 border-transparent group-focus-within/cell:border-indigo-600 transition-all pointer-events-none"></div>
                                            @endif
                                        </td>
                                    @endforeach

                                    <td class="px-4 py-4 border-r border-indigo-200 text-center font-black bg-indigo-50/50 text-indigo-900 student-total">{{ $totalScore }}</td>
                                    <td class="px-4 py-4 border-r border-indigo-200 text-center font-black bg-indigo-100/50 text-indigo-900 student-average">{{ number_format($totalScore, 2) }}</td>
                                    <td class="px-4 py-4 bg-emerald-50/50 text-center font-black text-emerald-700 student-rank font-mono text-lg">-</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $gradeComponents->count() + 5 }}" class="px-12 py-20 text-center">
                                        <div class="w-20 h-20 bg-slate-50 rounded-[2rem] flex items-center justify-center text-slate-300 mx-auto mb-6">
                                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354l1.1 3.392a1 1 0 00.95.69h3.462a1 1 0 01.59 1.807l-2.8 2.034a1 1 0 00-.36 1.118l1.07 3.292a1 1 0 01-1.537 1.117l-2.8-2.034a1 1 0 00-1.176 0l-2.8 2.034a1 1 0 01-1.537-1.117l1.07-3.292a1 1 0 00-.36-1.118l-2.8-2.034a1 1 0 01.59-1.807h3.462a1 1 0 00.95-.69L12 4.354z"></path></svg>
                                        </div>
                                        <h3 class="text-xl font-black text-slate-900 tracking-tight">No students found</h3>
                                        <p class="text-slate-500 font-semibold mt-1 text-sm">Please verify the section enrollment for this academic period.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Floating Bottom Command Bar -->
                <div class="fixed bottom-8 left-1/2 -translate-x-1/2 z-50 transform flex items-center gap-3 p-3 bg-slate-900/90 backdrop-blur-2xl rounded-[2.5rem] border border-white/20 shadow-2xl animate-in slide-in-from-bottom-12 duration-500">
                    <div class="flex items-center gap-2 px-4 border-r border-white/10">
                        <div class="w-10 h-10 rounded-full bg-indigo-500/20 flex items-center justify-center text-indigo-400 text-sm font-black">
                            <span x-text="hasUnsavedChanges ? '!' : '✓'"></span>
                        </div>
                        <div class="hidden sm:block">
                            <span class="block text-[8px] font-black text-slate-400 uppercase tracking-widest" x-text="hasUnsavedChanges ? 'Changes Detected' : 'All Syncronized'"></span>
                            <span class="block text-xs font-black text-white" x-text="hasUnsavedChanges ? 'Unsaved Record Data' : 'Cloud Synchronized'"></span>
                        </div>
                    </div>

                    <a href="{{ route('admin.gradebook.index') }}" class="px-6 py-3 text-slate-300 font-black text-[10px] uppercase tracking-widest hover:text-white transition-colors">
                        Discard
                    </a>
                    
                    @if($term->is_grading_open)
                        <button type="submit" class="px-8 py-3 bg-indigo-600 text-white font-black text-[10px] uppercase tracking-widest rounded-full hover:bg-indigo-700 transition-all shadow-xl shadow-indigo-900/40 flex items-center gap-2 group">
                            Commit Score Data
                            <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                        </button>
                    @endif
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        // Alpine.js component for gradebook state
        function gradebookState() {
            return {
                hasUnsavedChanges: false,
                isSaving: false,
                
                init() {
                    // Track changes on all inputs
                    this.$nextTick(() => {
                        const form = document.getElementById('gradeForm');
                        if (form) {
                            form.querySelectorAll('input[type="number"]').forEach(input => {
                                input.addEventListener('change', () => {
                                    this.hasUnsavedChanges = true;
                                });
                            });

                            form.addEventListener('submit', () => {
                                this.isSaving = true;
                            });
                        }
                    });
                    
                    // Warn before leaving with unsaved changes
                    window.addEventListener('beforeunload', (e) => {
                        if (this.hasUnsavedChanges && !this.isSaving) {
                            e.preventDefault();
                            e.returnValue = '';
                        }
                    });
                }
            }
        }
        
        function calculateRow(row) {
            let total = 0;
            const inputs = row.querySelectorAll('.mark-input');
            
            inputs.forEach(input => {
                const max = parseFloat(input.getAttribute('max'));
                let val = parseFloat(input.value) || 0;
                
                if (val > max) {
                    val = max;
                    input.value = max;
                }
                if (val < 0) {
                    val = 0;
                    input.value = 0;
                }
                
                total += val;
            });
            
            row.querySelector('.student-total').innerText = total.toFixed(2);
            row.querySelector('.student-average').innerText = total.toFixed(2); // Out of 100
            
            // Mark as unsaved
            const rootElement = document.querySelector('.space-y-8');
            if (rootElement && window.Alpine) {
                const data = Alpine.$data(rootElement);
                if (data) data.hasUnsavedChanges = true;
            }

            calculateAllRanks();
        }

        function calculateAllRanks() {
            const rows = Array.from(document.querySelectorAll('.student-row'));
            const studentData = rows.map(row => ({
                row: row,
                total: parseFloat(row.querySelector('.student-total').innerText) || 0
            }));

            studentData.sort((a, b) => b.total - a.total);

            let currentRank = 1;
            for (let i = 0; i < studentData.length; i++) {
                if (i > 0 && studentData[i].total < studentData[i-1].total) {
                    currentRank = i + 1;
                }
                studentData[i].row.querySelector('.student-rank').innerText = currentRank;
            }
        }
        
        // Keyboard navigation
        document.addEventListener('DOMContentLoaded', function() {
            // Initial calculation
            document.querySelectorAll('.student-row').forEach(row => {
                let total = 0;
                row.querySelectorAll('.mark-input').forEach(input => {
                    total += parseFloat(input.value) || 0;
                });
                row.querySelector('.student-total').innerText = total.toFixed(2);
                row.querySelector('.student-average').innerText = total.toFixed(2);
            });
            calculateAllRanks();

            const table = document.querySelector('table');
            if (!table) return;
            
            table.addEventListener('keydown', function(e) {
                if (e.target.tagName !== 'INPUT') return;
                
                const cell = e.target.closest('td');
                const row = cell.closest('tr');
                const rows = Array.from(table.querySelectorAll('tbody tr.student-row'));
                const rowIndex = rows.indexOf(row);
                const cells = Array.from(row.querySelectorAll('td'));
                const cellIndex = cells.indexOf(cell);
                
                let nextInput = null;
                
                if (e.key === 'Enter' || e.key === 'ArrowDown') {
                    e.preventDefault();
                    const nextRow = rows[rowIndex + 1];
                    if (nextRow) {
                        nextInput = nextRow.querySelectorAll('td')[cellIndex]?.querySelector('input');
                    }
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    const prevRow = rows[rowIndex - 1];
                    if (prevRow) {
                        nextInput = prevRow.querySelectorAll('td')[cellIndex]?.querySelector('input');
                    }
                } else if (e.key === 'ArrowRight' && e.target.selectionEnd === e.target.value.length) {
                    const rowInputs = Array.from(row.querySelectorAll('.mark-input'));
                    const inputIndex = rowInputs.indexOf(e.target);
                    nextInput = rowInputs[inputIndex + 1];
                    if (nextInput) e.preventDefault();
                } else if (e.key === 'ArrowLeft' && e.target.selectionStart === 0) {
                    const rowInputs = Array.from(row.querySelectorAll('.mark-input'));
                    const inputIndex = rowInputs.indexOf(e.target);
                    nextInput = rowInputs[inputIndex - 1];
                    if (nextInput) e.preventDefault();
                }
                
                if (nextInput) {
                    nextInput.focus();
                    nextInput.select();
                }
            });
        });
    </script>
    @endpush
</x-admin-layout>
