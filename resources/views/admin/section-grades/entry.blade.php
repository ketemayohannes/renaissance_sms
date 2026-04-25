<x-admin-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="font-heading font-bold text-2xl text-slate-900">Master Sheet Entry</h2>
                <p class="text-slate-500 text-sm mt-1">Manage and verify student grades.</p>
            </div>
            <div class="flex items-center gap-3">
                <button type="button" onclick="calculateAll()" class="inline-flex items-center px-4 py-2 bg-white border border-slate-200 text-slate-700 text-sm font-semibold rounded-xl hover:bg-slate-50 transition-all gap-2 shadow-sm">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    Recalculate
                </button>
                @if($term->type === 'semester' && ($term->is_grading_open || $term->is_master_grading_open))
                    <button type="button" x-data @click="$dispatch('open-calc-modal')" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl transition-all duration-200 shadow-lg shadow-indigo-100 gap-2">
                        <svg class="w-4 h-4 text-indigo-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                        Auto-Calculate
                    </button>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="space-y-6 pb-32">
        <!-- Breadcrumb -->
        <x-breadcrumb :items="[
            ['label' => 'Section Grades', 'url' => route('admin.section-grades.index')],
            ['label' => $section->name, 'url' => '#']
        ]" />



        <!-- System Status Bar -->
        @if(!$term->is_master_grading_open)
            <div class="bg-slate-900 text-white p-4 rounded-2xl flex flex-col md:flex-row items-center justify-between gap-4 shadow-xl relative overflow-hidden group">
                <div class="absolute inset-0 bg-gradient-to-r from-rose-500/20 to-transparent opacity-50"></div>
                <div class="flex items-center gap-4 relative z-10">
                    <div class="w-10 h-10 rounded-xl bg-rose-500 flex items-center justify-center text-white shadow-lg shadow-rose-900/50">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold tracking-tight">Master Grading Locked</h3>
                        <p class="text-rose-300/80 text-xs mt-0.5">Modifications restricted by management</p>
                    </div>
                </div>
                <div class="relative z-10">
                    <span class="px-3 py-1 bg-white/10 rounded-lg border border-white/10 text-[10px] font-bold uppercase tracking-wider">Read Only</span>
                </div>
            </div>
        @endif

        <!-- Stats Overview -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white/60 backdrop-blur-md p-4 rounded-3xl border border-white shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Academic Year</p>
                    <p class="text-lg font-bold text-slate-900">{{ $academicYear->name }}</p>
                </div>
            </div>

            <div class="bg-white/60 backdrop-blur-md p-4 rounded-3xl border border-white shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-violet-50 flex items-center justify-center text-violet-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Active Term</p>
                    <p class="text-lg font-bold text-slate-900">{{ $term->name }}</p>
                </div>
            </div>

            <div class="bg-white/60 backdrop-blur-md p-4 rounded-3xl border border-white shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Class</p>
                    <p class="text-lg font-bold text-slate-900">{{ $section->gradeLevel->name }} - {{ $section->name }}</p>
                </div>
            </div>

            <div class="bg-white/60 backdrop-blur-md p-4 rounded-3xl border border-white shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-amber-50 flex items-center justify-center text-amber-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Enrolled</p>
                    <p class="text-lg font-bold text-slate-900">{{ $students->count() }} Students</p>
                </div>
            </div>
        </div>

        <!-- Toolbar Panel -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col lg:flex-row items-center justify-between gap-4">
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('admin.section-grades.export', ['academic_year_id' => $academicYear->id, 'term_id' => $term->id, 'section_id' => $section->id]) }}" 
                   class="inline-flex items-center px-4 py-2 bg-white border border-slate-200 text-slate-700 text-sm font-semibold rounded-xl hover:bg-slate-50 transition-all gap-2">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    Export Template
                </a>
                <a href="{{ route('admin.section-grades.report-card-entry', $section->id) }}?academic_year_id={{$academicYear->id}}&term_id={{$term->id}}" 
                   class="inline-flex items-center px-4 py-2 bg-white border border-slate-200 text-slate-700 text-sm font-semibold rounded-xl hover:bg-slate-50 transition-all gap-2">
                   <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Report Card Data
                </a>
                <a href="{{ route('admin.academic-reports.show') }}?academic_year_id={{$academicYear->id}}&term_id={{$term->id}}&section_id={{$section->id}}&report_type=result_analysis" 
                   class="inline-flex items-center px-4 py-2 bg-indigo-50 border border-indigo-100 text-indigo-700 text-sm font-semibold rounded-xl hover:bg-indigo-100 transition-all gap-2">
                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    Analysis
                </a>
            </div>

            <form action="{{ route('admin.section-grades.import') }}" method="POST" enctype="multipart/form-data" class="flex items-center gap-3">
                @csrf
                <input type="hidden" name="academic_year_id" value="{{ $academicYear->id }}">
                <input type="hidden" name="term_id" value="{{ $term->id }}">
                <input type="hidden" name="section_id" value="{{ $section->id }}">
                
                <div class="relative">
                    <input type="file" name="file" accept=".csv" required 
                           class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-slate-50 file:text-slate-700 hover:file:bg-slate-100 transition-all cursor-pointer disabled:opacity-50"
                           @if(!$term->is_master_grading_open) disabled @endif>
                </div>
                
                <button type="submit" 
                        class="px-4 py-2 bg-slate-900 text-white text-sm font-semibold rounded-xl hover:bg-slate-800 shadow-md shadow-slate-200 transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                        @if(!$term->is_master_grading_open) disabled @endif>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                    Import CSV
                </button>
            </form>
        </div>

        <!-- Master Sheet Table Container -->
        <div class="bg-white border border-slate-200 shadow-sm rounded-2xl overflow-hidden relative">
            <form action="{{ route('admin.section-grades.store') }}" method="POST" id="gradeForm">
                @csrf
                <input type="hidden" name="academic_year_id" value="{{ $academicYear->id }}">
                <input type="hidden" name="term_id" value="{{ $term->id }}">
                <input type="hidden" name="section_id" value="{{ $section->id }}">

                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left border-separate border-spacing-0">
                        <thead>
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200">
                                <th class="px-2 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider text-center w-12 align-bottom pb-2">No</th>
                                <th class="px-3 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider sticky left-0 z-30 bg-slate-50 shadow-[4px_0_12px_-4px_rgba(0,0,0,0.05)] align-bottom pb-2 min-w-[250px]">Student Full Name</th>
                                <th class="px-1 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider text-center align-bottom h-[120px] w-12">
                                    <div class="[writing-mode:vertical-rl] rotate-180 flex items-center justify-start w-full h-full mx-auto whitespace-normal leading-3 px-1">
                                        Gender
                                    </div>
                                </th>
                                
                                @foreach($subjects as $subject)
                                    <th class="px-0.5 py-3 border-b border-slate-200 min-w-[3rem] text-center align-bottom h-[120px] group cursor-default relative hover:bg-slate-100 transition-colors" title="{{ $subject->name }}">
                                        <div class="[writing-mode:vertical-rl] rotate-180 flex items-center justify-start w-full h-full mx-auto text-[10px] font-bold text-slate-600 uppercase tracking-wider whitespace-normal leading-3 text-left px-1">
                                            {{ $subject->name }}
                                        </div>
                                    </th>
                                @endforeach

                                <th class="px-1 py-3 border-b border-slate-200 text-center font-bold text-slate-700 text-[11px] uppercase tracking-wider min-w-[4rem] align-bottom h-[120px]">
                                    <div class="[writing-mode:vertical-rl] rotate-180 flex items-center justify-start w-full h-full mx-auto whitespace-normal leading-3 px-1">
                                        Total
                                    </div>
                                </th>
                                <th class="px-1 py-3 border-b border-slate-200 text-center font-bold text-indigo-700 text-[11px] uppercase tracking-wider min-w-[4rem] align-bottom h-[120px]">
                                    <div class="[writing-mode:vertical-rl] rotate-180 flex items-center justify-start w-full h-full mx-auto whitespace-normal leading-3 px-1">
                                        Average
                                    </div>
                                </th>
                                <th class="px-1 py-3 border-b border-slate-200 text-center font-bold text-emerald-700 text-[11px] uppercase tracking-wider min-w-[4rem] align-bottom h-[120px]">
                                    <div class="[writing-mode:vertical-rl] rotate-180 flex items-center justify-start w-full h-full mx-auto whitespace-normal leading-3 px-1">
                                        Rank
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach($students as $index => $student)
                                @php
                                    $applicableSubjectCount = 0;
                                    foreach($subjects as $s) {
                                        if (!$s->is_elective || (isset($studentElectives[$student->id]) && in_array($s->id, $studentElectives[$student->id]))) {
                                            $applicableSubjectCount++;
                                        }
                                    }
                                @endphp
                                <tr class="group hover:bg-slate-50 transition-colors student-row" data-student-id="{{ $student->id }}" data-subject-count="{{ $applicableSubjectCount }}">
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
                                        <span class="inline-flex items-center justify-center w-5 h-5 rounded-md {{ strtolower($student->gender) === 'f' ? 'bg-pink-50 text-pink-600' : 'bg-blue-50 text-blue-600' }} font-bold text-[10px]">
                                            {{ substr($student->gender ?? '?', 0, 1) }}
                                        </span>
                                    </td>
                                    
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
                                                    $placeholder = '.';
                                                    $title = 'Not enrolled';
                                                }
                                            } else {
                                                if ($term->type === 'semester') {
                                                    $disabled = true;
                                                    $title = 'Auto-calculated';
                                                }
                                            }
                                        <td class="p-0.5 text-center relative group/cell min-w-[3rem]" title="{{ $title }}">
                                            <input type="text" inputmode="decimal"
                                                   name="marks[{{ $student->id }}][{{ $subject->id }}]" 
                                                   value="{{ $score }}" 
                                                   class="w-full text-center text-xs font-medium border-slate-200 rounded focus:ring-1 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all hover:bg-slate-50 disabled:bg-slate-50 disabled:text-slate-300 mark-input py-1 px-0"
                                                   placeholder="{{ $placeholder }}"
                                                   oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"
                                                   onchange="calculateRow(this.closest('tr'))"
                                                   @if($disabled) disabled @endif>
                                        </td>
                                    @endforeach

                                    <td class="px-1 py-2 text-center">
                                        <span class="inline-block px-1.5 py-0.5 rounded bg-slate-100 text-slate-700 text-xs font-bold student-total min-w-[2.5rem]">0</span>
                                    </td>
                                    <td class="px-1 py-2 text-center">
                                        <span class="inline-block px-1.5 py-0.5 rounded bg-indigo-50 text-indigo-700 text-xs font-bold student-average min-w-[2.5rem]">0.00</span>
                                    </td>
                                    <td class="px-1 py-2 text-center">
                                        <span class="inline-block w-6 h-6 flex items-center justify-center rounded-full bg-emerald-50 text-emerald-600 text-xs font-bold student-rank mx-auto">-</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Floating Bottom Command Bar -->
                <div class="fixed bottom-6 left-1/2 -translate-x-1/2 z-40 flex items-center gap-1 p-1.5 bg-white/90 backdrop-blur-xl border border-slate-200 shadow-2xl shadow-slate-200/50 rounded-2xl animate-in slide-in-from-bottom-12 duration-500">
                    <div class="flex items-center gap-3 pl-4 pr-2 border-r border-slate-100">
                        <div class="flex flex-col">
                            <span class="text-[10px] uppercase tracking-wider font-bold text-slate-400 leading-none">Changes</span>
                            <span class="text-sm font-bold text-slate-700 leading-tight">{{ $students->count() }} Records</span>
                        </div>
                    </div>

                    <a href="{{ route('admin.section-grades.index') }}" class="px-5 py-2.5 text-slate-500 font-bold text-xs hover:text-slate-900 transition-colors uppercase tracking-wider">
                        Discard
                    </a>
                    
                    @if($term->is_master_grading_open)
                        <button type="submit" class="px-6 py-2.5 bg-indigo-600 text-white font-bold text-xs uppercase tracking-widest rounded-xl hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-100 flex items-center gap-2 group">
                            Save Changes
                            <svg class="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
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

            // Keyboard Navigation
            const table = document.querySelector('table');
            if (table) {
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
            }
        });

        function calculateRow(row) {
            let total = 0;
            let count = 0;
            
            row.querySelectorAll('.mark-input').forEach(input => {
                const val = parseFloat(input.value);
                
                // Reset validation styles
                input.classList.remove('border-red-500', 'bg-red-50', 'text-red-600', 'focus:border-red-500', 'focus:ring-red-200');
                input.classList.add('border-slate-200', 'focus:border-indigo-500', 'focus:ring-indigo-500/20');

                if (!isNaN(val)) {
                    if (val > 100) {
                        // Warning style instead of auto-correct
                        input.classList.remove('border-slate-200', 'focus:border-indigo-500', 'focus:ring-indigo-500/20');
                        input.classList.add('border-red-500', 'bg-red-50', 'text-red-600', 'focus:border-red-500', 'focus:ring-red-200');
                        total += val;
                    } else if (val < 0) {
                        input.value = 0;
                    } else {
                        total += val;
                    }
                    count++;
                }
            });
            
            const subjectCount = parseInt(row.dataset.subjectCount) || 0;
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
