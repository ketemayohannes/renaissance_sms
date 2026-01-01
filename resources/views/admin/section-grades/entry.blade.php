<x-admin-layout>
    <x-slot name="header">Master Sheet Entry</x-slot>

    <div class="space-y-8 pb-32">
        <!-- Breadcrumb & Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <x-breadcrumb :items="[
                    ['label' => 'Section Grades', 'url' => route('admin.section-grades.index')],
                    ['label' => $section->name, 'url' => '#']
                ]" />
                <h1 class="text-4xl font-black text-slate-900 tracking-tight mt-2">Master Sheet</h1>
            </div>
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2 p-1 bg-slate-100 rounded-2xl border border-slate-200 shadow-sm">
                    <button type="button" onclick="calculateAll()" class="px-5 py-2.5 bg-white text-slate-900 font-black text-[10px] uppercase tracking-widest rounded-xl hover:bg-slate-50 transition-all shadow-sm border border-slate-200">
                        Recalculate
                    </button>
                    @if($term->type === 'semester' && ($term->is_grading_open || $term->is_master_grading_open))
                        <button type="button" x-data @click="$dispatch('open-calc-modal')" class="px-5 py-2.5 bg-indigo-600 text-white font-black text-[10px] uppercase tracking-widest rounded-xl hover:bg-indigo-700 transition-all shadow-indigo-200 shadow-lg">
                            Auto-Calculate
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <!-- Success/Error Alerts -->
        @if (session('success'))
            <div class="bg-emerald-50 border border-emerald-100 text-emerald-800 px-6 py-4 rounded-[1.5rem] flex items-center gap-4 animate-in fade-in slide-in-from-top-4 duration-300">
                <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600 shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <span class="font-bold text-sm">{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="bg-rose-50 border border-rose-100 text-rose-800 px-6 py-4 rounded-[1.5rem] flex items-center gap-4 animate-in fade-in slide-in-from-top-4 duration-300">
                <div class="w-8 h-8 rounded-full bg-rose-100 flex items-center justify-center text-rose-600 shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </div>
                <span class="font-bold text-sm">{{ session('error') }}</span>
            </div>
        @endif

        <!-- System Status Bar -->
        @if(!$term->is_master_grading_open)
            <div class="bg-slate-900 text-white p-6 rounded-[2.5rem] flex flex-col md:flex-row items-center justify-between gap-6 shadow-2xl overflow-hidden relative group">
                <div class="absolute inset-0 bg-gradient-to-r from-rose-500/20 to-transparent opacity-50"></div>
                <div class="flex items-center gap-6 relative z-10">
                    <div class="w-14 h-14 rounded-3xl bg-rose-500 flex items-center justify-center text-white shadow-lg shadow-rose-900/50">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-black tracking-tight leading-tight">Master Grading Lock Active</h3>
                        <p class="text-rose-300/80 text-xs font-bold uppercase tracking-widest mt-1">Direct modifications are currently restricted by management</p>
                    </div>
                </div>
                <div class="relative z-10">
                    <span class="px-4 py-2 bg-white/10 rounded-xl border border-white/10 text-[10px] font-black uppercase tracking-[0.2em]">Read Only Mode</span>
                </div>
            </div>
        @endif

        <!-- Context Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white/80 backdrop-blur-xl rounded-[2rem] p-6 border border-white shadow-xl shadow-slate-200/50">
                <span class="block text-[10px] font-black text-slate-400 uppercase tracking-widest">Academic Year</span>
                <span class="block text-lg font-black text-slate-900 mt-1">{{ $academicYear->name }}</span>
            </div>
            <div class="bg-white/80 backdrop-blur-xl rounded-[2rem] p-6 border border-white shadow-xl shadow-slate-200/50">
                <span class="block text-[10px] font-black text-slate-400 uppercase tracking-widest">Active Term</span>
                <span class="block text-lg font-black text-indigo-600 mt-1">{{ $term->name }}</span>
            </div>
            <div class="bg-white/80 backdrop-blur-xl rounded-[2rem] p-6 border border-white shadow-xl shadow-slate-200/50">
                <span class="block text-[10px] font-black text-slate-400 uppercase tracking-widest">Grade & Section</span>
                <span class="block text-lg font-black text-slate-900 mt-1">{{ $section->gradeLevel->name }} - {{ $section->name }}</span>
            </div>
            <div class="bg-white/80 backdrop-blur-xl rounded-[2rem] p-6 border border-white shadow-xl shadow-slate-200/50">
                <span class="block text-[10px] font-black text-slate-400 uppercase tracking-widest">Student Count</span>
                <span class="block text-lg font-black text-slate-900 mt-1">{{ $students->count() }} enrolled</span>
            </div>
        </div>

        <!-- Toolbar Panel -->
        <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-xl shadow-slate-200/50 p-6 flex flex-col lg:flex-row items-center justify-between gap-6">
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('admin.section-grades.export', ['academic_year_id' => $academicYear->id, 'term_id' => $term->id, 'section_id' => $section->id]) }}" 
                   class="px-5 py-3 bg-slate-900 text-white font-black text-[10px] uppercase tracking-widest rounded-xl hover:bg-slate-800 transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    Export Template
                </a>
                <a href="{{ route('admin.section-grades.report-card-entry', $section->id) }}?academic_year_id={{$academicYear->id}}&term_id={{$term->id}}" 
                   class="px-5 py-3 bg-violet-50 text-violet-700 font-black text-[10px] uppercase tracking-widest rounded-xl border border-violet-100 hover:bg-violet-100 transition-all">
                    Report Card Data
                </a>
                <a href="{{ route('admin.academic-reports.show') }}?academic_year_id={{$academicYear->id}}&term_id={{$term->id}}&section_id={{$section->id}}&report_type=result_analysis" 
                   class="px-5 py-3 bg-indigo-50 text-indigo-700 font-black text-[10px] uppercase tracking-widest rounded-xl border border-indigo-100 hover:bg-indigo-100 transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    Analysis
                </a>
            </div>

            <form action="{{ route('admin.section-grades.import') }}" method="POST" enctype="multipart/form-data" class="flex flex-wrap items-center gap-4 bg-slate-50 p-2 rounded-2xl border border-slate-200">
                @csrf
                <input type="hidden" name="academic_year_id" value="{{ $academicYear->id }}">
                <input type="hidden" name="term_id" value="{{ $term->id }}">
                <input type="hidden" name="section_id" value="{{ $section->id }}">
                <input type="file" name="file" accept=".csv" required 
                       class="block w-full sm:w-48 text-[10px] font-black text-slate-500 uppercase tracking-tight file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-[10px] file:font-black file:bg-white file:text-slate-900 file:shadow-sm file:cursor-pointer transition-all hover:file:bg-slate-50 disabled:opacity-50"
                       @if(!$term->is_master_grading_open) disabled @endif>
                <button type="submit" 
                        class="px-6 py-2.5 bg-indigo-600 text-white font-black text-[10px] uppercase tracking-widest rounded-xl hover:bg-indigo-700 shadow-md shadow-indigo-100 transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                        @if(!$term->is_master_grading_open) disabled @endif>
                    Batch Import
                </button>
            </form>
        </div>

        <!-- Master Sheet Table Container -->
        <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-2xl shadow-slate-200/50 overflow-hidden relative">
            <form action="{{ route('admin.section-grades.store') }}" method="POST" id="gradeForm">
                @csrf
                <input type="hidden" name="academic_year_id" value="{{ $academicYear->id }}">
                <input type="hidden" name="term_id" value="{{ $term->id }}">
                <input type="hidden" name="section_id" value="{{ $section->id }}">

                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left border-separate border-spacing-0">
                        <thead class="sticky top-0 z-30">
                            <tr>
                                <th class="px-4 py-6 bg-slate-50/90 backdrop-blur-md border-b border-r border-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">No</th>
                                <th class="px-8 py-6 bg-slate-50/90 backdrop-blur-md border-b border-r border-slate-100 text-[10px] font-black text-slate-900 uppercase tracking-widest sticky left-0 z-40 shadow-[4px_0_8px_-4px_rgba(0,0,0,0.05)] whitespace-normal">Full Student Name</th>
                                <th class="px-4 py-6 bg-slate-50/90 backdrop-blur-md border-b border-r border-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center min-w-[60px] whitespace-normal">Gender</th>
                                
                                @foreach($subjects as $subject)
                                    <th class="px-3 py-6 bg-white border-b border-r border-slate-100 text-center min-w-[100px] group cursor-default" title="{{ $subject->name }}">
                                        <div class="text-[10px] font-black text-slate-600 uppercase tracking-widest group-hover:text-indigo-600 transition-colors leading-tight">
                                            {{ $subject->name }}
                                        </div>
                                    </th>
                                @endforeach

                                <th class="px-6 py-6 bg-indigo-900 border-b border-slate-800 text-center font-black text-white text-[11px] uppercase tracking-widest shadow-lg min-w-[100px] whitespace-normal">Total</th>
                                <th class="px-6 py-6 bg-indigo-950 border-b border-slate-900 text-center font-black text-indigo-300 text-[11px] uppercase tracking-widest shadow-lg min-w-[100px] whitespace-normal">Average</th>
                                <th class="px-6 py-6 bg-emerald-600 border-b border-emerald-700 text-center font-black text-white text-[11px] uppercase tracking-widest shadow-lg rounded-tr-[2rem] min-w-[100px] whitespace-normal">Rank</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($students as $index => $student)
                                <tr class="group hover:bg-indigo-50/30 transition-all student-row" data-student-id="{{ $student->id }}">
                                    <td class="px-4 py-4 border-r border-slate-100 text-[10px] font-black text-slate-400 text-center bg-slate-50/30">{{ $index + 1 }}</td>
                                    <td class="px-8 py-4 border-r border-slate-100 font-bold text-slate-900 sticky left-0 bg-white/95 backdrop-blur-sm z-20 group-hover:bg-indigo-50 transition-colors shadow-[4px_0_8px_-4px_rgba(0,0,0,0.05)] whitespace-nowrap">{{ $student->full_name }}</td>
                                    <td class="px-4 py-4 border-r border-slate-100 text-[10px] font-black text-slate-500 text-center uppercase">{{ $student->gender ?? 'N/A' }}</td>
                                    
                                    @foreach($subjects as $subject)
                                        @php
                                            $score = $marksMap[$student->id][$subject->id] ?? '';
                                            $disabled = !$term->is_master_grading_open;
                                            $placeholder = '-';
                                            $title = '';

                                            if ($subject->is_elective) {
                                                $enrolled = isset($studentElectives[$student->id]) && in_array($subject->id, $studentElectives[$student->id]);
                                                if (!$enrolled) {
                                                    $disabled = true;
                                                    $placeholder = 'N/A';
                                                    $title = 'Not enrolled';
                                                } elseif ($term->type === 'quarter') {
                                                    $disabled = true;
                                                    $title = 'Semester only';
                                                }
                                            } else {
                                                if ($term->type === 'semester') {
                                                    $disabled = true;
                                                    $title = 'Auto-calculated';
                                                }
                                            }
                                        @endphp
                                        <td class="p-0 border-r border-slate-100 text-center relative group/cell" title="{{ $title }}">
                                            <input type="number" 
                                                   name="marks[{{ $student->id }}][{{ $subject->id }}]" 
                                                   value="{{ $score }}" 
                                                   min="0" max="100" step="0.01"
                                                   class="w-full h-12 text-center text-sm font-black border-0 focus:ring-0 focus:bg-indigo-50/50 bg-transparent transition-all hover:bg-slate-50/50 disabled:bg-slate-100/50 disabled:text-slate-400 mark-input"
                                                   placeholder="{{ $placeholder }}"
                                                   onchange="calculateRow(this.closest('tr'))"
                                                   @if($disabled) disabled @endif>
                                            @if(!$disabled)
                                                <div class="absolute inset-0 border-b-2 border-transparent group-focus-within/cell:border-indigo-600 transition-all pointer-events-none"></div>
                                            @endif
                                        </td>
                                    @endforeach

                                    <td class="px-4 py-4 border-r border-indigo-200 text-center font-black bg-indigo-50/50 text-indigo-900 student-total">0</td>
                                    <td class="px-4 py-4 border-r border-indigo-200 text-center font-black bg-indigo-100/50 text-indigo-900 student-average">0.00</td>
                                    <td class="px-4 py-4 bg-emerald-50/50 text-center font-black text-emerald-700 student-rank font-mono text-lg">-</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Floating Bottom Command Bar -->
                <div class="fixed bottom-8 left-1/2 -translate-x-1/2 z-50 transform flex items-center gap-3 p-3 bg-slate-900/90 backdrop-blur-2xl rounded-[2.5rem] border border-white/20 shadow-2xl animate-in slide-in-from-bottom-12 duration-500">
                    <div class="flex items-center gap-2 px-4 border-r border-white/10">
                        <div class="w-10 h-10 rounded-full bg-indigo-500/20 flex items-center justify-center text-indigo-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        </div>
                        <div class="hidden sm:block">
                            <span class="block text-[8px] font-black text-slate-400 uppercase tracking-widest">Active Entries</span>
                            <span class="block text-xs font-black text-white">{{ $students->count() }} Records</span>
                        </div>
                    </div>

                    <a href="{{ route('admin.section-grades.index') }}" class="px-6 py-3 text-slate-300 font-black text-[10px] uppercase tracking-widest hover:text-white transition-colors">
                        Discard
                    </a>
                    
                    @if($term->is_master_grading_open)
                        <button type="submit" class="px-8 py-3 bg-indigo-600 text-white font-black text-[10px] uppercase tracking-widest rounded-full hover:bg-indigo-700 transition-all shadow-xl shadow-indigo-900/40 flex items-center gap-2 group">
                            Commit All Changes
                            <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                        </button>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Redesigned Calculation Modal -->
    <div x-data="{ open: false }" 
         x-show="open" 
         @open-calc-modal.window="open = true" 
         @keydown.escape.window="open = false"
         style="display: none;" 
         class="fixed inset-0 z-[100] overflow-y-auto" 
         aria-labelledby="modal-title" 
         role="dialog" 
         aria-modal="true">
        
        <div class="flex items-center justify-center min-h-screen p-6 text-center">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-md transition-opacity" 
                 @click="open = false"
                 x-transition:enter="ease-out duration-300" 
                 x-transition:enter-start="opacity-0" 
                 x-transition:enter-end="opacity-100" 
                 x-transition:leave="ease-in duration-200" 
                 x-transition:leave-start="opacity-100" 
                 x-transition:leave-end="opacity-0"></div>

            <div class="inline-block align-bottom bg-white/95 backdrop-blur-xl rounded-[3rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-white"
                 x-transition:enter="ease-out duration-300" 
                 x-transition:enter-start="opacity-0 translate-y-8 sm:scale-95" 
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave="ease-in duration-200" 
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave-end="opacity-0 translate-y-8 sm:scale-95">
                
                <div class="p-10">
                    <div class="text-center">
                        <div class="w-20 h-20 bg-indigo-50 rounded-[2rem] flex items-center justify-center text-indigo-600 mx-auto mb-6 shadow-sm border border-indigo-100">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        </div>
                        <h3 class="text-3xl font-black text-slate-900 tracking-tight leading-tight">Master Calculation</h3>
                        <p class="text-slate-500 font-semibold mt-2 px-6 leading-relaxed">This will overwrite existing Semester grades based on Quarter averages. This action is destructive and cannot be undone.</p>
                        
                        <div class="mt-8 p-6 bg-slate-50 rounded-[2rem] border border-slate-100 text-left">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-3">Logic applied</span>
                            <div class="flex items-center gap-4">
                                <div class="px-4 py-2 bg-white rounded-xl border border-slate-200 text-xs font-black text-slate-700">Q1 Avg</div>
                                <span class="text-slate-300 font-black">+</span>
                                <div class="px-4 py-2 bg-white rounded-xl border border-slate-200 text-xs font-black text-slate-700">Q2 Avg</div>
                                <span class="text-slate-300 font-black">/ 2</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-10 flex flex-col gap-3">
                        <button type="button" onclick="document.getElementById('calculate-form').submit()" class="w-full py-5 bg-indigo-600 text-white font-black text-sm uppercase tracking-[0.2em] rounded-2xl hover:bg-slate-900 transition-all shadow-xl shadow-indigo-100">
                            Execute Calculation
                        </button>
                        <button type="button" @click="open = false" class="w-full py-5 text-slate-400 font-black text-[10px] uppercase tracking-widest hover:text-slate-900 transition-colors">
                            Dismiss
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden Calculation Form -->
    <form id="calculate-form" action="{{ route('admin.section-grades.calculate') }}" method="POST" class="hidden">
        @csrf
        <input type="hidden" name="academic_year_id" value="{{ $academicYear->id }}">
        <input type="hidden" name="term_id" value="{{ $term->id }}">
        <input type="hidden" name="grade_level_id" value="{{ $section->gradeLevel->id }}">
        <input type="hidden" name="section_id" value="{{ $section->id }}">
    </form>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            calculateAll();
        });

        function calculateRow(row) {
            let total = 0;
            let count = 0;
            
            row.querySelectorAll('.mark-input').forEach(input => {
                const val = parseFloat(input.value);
                if (!isNaN(val)) {
                    if (val > 100) {
                        input.value = 100;
                        total += 100;
                    } else if (val < 0) {
                        input.value = 0;
                    } else {
                        total += val;
                    }
                    count++;
                }
            });
            
            const subjectCount = {{ $subjects->count() }};
            const average = subjectCount > 0 ? (total / subjectCount) : 0;

            row.querySelector('.student-total').innerText = parseFloat(total.toFixed(2));
            row.querySelector('.student-average').innerText = average.toFixed(2);
        }

        function calculateAll() {
            const rows = document.querySelectorAll('.student-row');
            let studentData = [];

            rows.forEach(row => {
                calculateRow(row);
                const avg = parseFloat(row.querySelector('.student-average').innerText);
                studentData.push({ row: row, average: avg });
            });

            studentData.sort((a, b) => b.average - a.average);

            let currentRank = 1;
            for (let i = 0; i < studentData.length; i++) {
                if (i > 0 && studentData[i].average < studentData[i-1].average) {
                    currentRank = i + 1;
                }
                studentData[i].row.querySelector('.student-rank').innerText = currentRank;
            }
        }
    </script>
    @endpush
</x-admin-layout>
