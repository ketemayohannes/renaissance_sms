<x-admin-layout>
    <x-slot name="header">Section Grade Entry (Master Sheet)</x-slot>

    <div class="space-y-6">
        <x-breadcrumb :items="[
            ['label' => 'Section Grades', 'url' => route('admin.section-grades.index')],
            ['label' => 'Entry', 'url' => '#']
        ]" />

            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <strong class="font-bold">Success!</strong>
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <strong class="font-bold">Error!</strong>
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            @if (session('warning'))
                <div class="mb-4 bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative" role="alert">
                    <strong class="font-bold">Warning!</strong>
                    <span class="block sm:inline">{{ session('warning') }}</span>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <!-- Header Info -->
                <div class="p-6 bg-gray-50 border-b border-gray-200">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm">
                        <div>
                            <span class="text-gray-500 block">Academic Year</span>
                            <span class="font-bold">{{ $academicYear->name }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500 block">Term</span>
                            <span class="font-bold">{{ $term->name }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500 block">Grade & Section</span>
                            <span class="font-bold">{{ $section->gradeLevel->name }} - {{ $section->name }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500 block">Students</span>
                            <span class="font-bold">{{ $students->count() }}</span>
                        </div>
                    </div>
                </div>

                @if(!$term->is_master_grading_open)
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 mx-6" role="alert">
                        <p class="font-bold">Grading Disabled</p>
                        <p>Master Sheet grading entry is currently closed for this term.</p>
                    </div>
                @endif

                <div class="p-6 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                    <h3 class="text-lg font-medium text-gray-900">Grade Entry</h3>
                    <div class="flex gap-4 items-center">
                         @if($term->type === 'semester' && ($term->is_grading_open || $term->is_master_grading_open))
                            <form id="calculate-form" action="{{ route('admin.section-grades.calculate') }}" method="POST">
                                @csrf
                                <input type="hidden" name="academic_year_id" value="{{ $academicYear->id }}">
                                <input type="hidden" name="term_id" value="{{ $term->id }}">
                                <input type="hidden" name="grade_level_id" value="{{ $section->gradeLevel->id }}">
                                <input type="hidden" name="section_id" value="{{ $section->id }}">
                                <button type="button" x-data @click="$dispatch('open-calc-modal')" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                    Auto-Calculate
                                </button>
                            </form>
                        @endif
                        <a href="{{ route('admin.section-grades.export', ['academic_year_id' => $academicYear->id, 'term_id' => $term->id, 'section_id' => $section->id]) }}" 
                           class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            Export Template
                        </a>
                        
                        <a href="{{ route('admin.section-grades.report-card-entry', $section->id) }}?academic_year_id={{$academicYear->id}}&term_id={{$term->id}}" 
                           class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 transition ease-in-out duration-150">
                           Report Card Data
                        </a>

                        <a href="{{ route('admin.academic-reports.show') }}?academic_year_id={{$academicYear->id}}&term_id={{$term->id}}&section_id={{$section->id}}&report_type=result_analysis" 
                           class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition ease-in-out duration-150">
                           <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                           Result Analysis
                        </a>

                        <form action="{{ route('admin.section-grades.import') }}" method="POST" enctype="multipart/form-data" class="flex items-center gap-2">
                            @csrf
                            <input type="hidden" name="academic_year_id" value="{{ $academicYear->id }}">
                            <input type="hidden" name="term_id" value="{{ $term->id }}">
                            <input type="hidden" name="section_id" value="{{ $section->id }}">
                            <div class="relative">
                                <input type="file" name="file" accept=".csv" required class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            </div>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150" @if(!$term->is_master_grading_open) disabled class="opacity-50 cursor-not-allowed" @endif>
                                Import
                            </button>
                        </form>
                        @if(!$term->is_master_grading_open)
                            <script>
                                document.querySelector('form[action="{{ route('admin.section-grades.import') }}"] input[type="file"]').disabled = true;
                                document.querySelector('form[action="{{ route('admin.section-grades.import') }}"] button').disabled = true;
                                document.querySelector('form[action="{{ route('admin.section-grades.import') }}"] button').classList.add('opacity-50', 'cursor-not-allowed');
                            </script>
                        @endif
                    </div>
                </div>

                <div class="p-6">
                    <form action="{{ route('admin.section-grades.store') }}" method="POST" id="gradeForm">
                        @csrf
                        <input type="hidden" name="academic_year_id" value="{{ $academicYear->id }}">
                        <input type="hidden" name="term_id" value="{{ $term->id }}">
                        <input type="hidden" name="section_id" value="{{ $section->id }}">

                            <table class="min-w-full divide-y divide-gray-200 border border-gray-300 text-sm">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <!-- Fixed Columns Headers -->
                                        <th class="px-2 py-2 border-r border-gray-300 bg-gray-100 text-center w-10">No</th>
                                        <th class="px-3 py-2 border-r border-gray-300 bg-gray-100 text-left min-w-[200px] sticky left-0 z-20 shadow-sm">Student Full Name</th>
                                        <th class="px-2 py-2 border-r border-gray-300 bg-gray-100 text-center [writing-mode:vertical-rl] rotate-180 h-32">Gender</th>
                                        
                                        <!-- Subject Headers -->
                                        @foreach($subjects as $subject)
                                            <th class="px-1 py-2 border-r border-gray-300 bg-white text-center [writing-mode:vertical-rl] rotate-180 h-40 min-w-[40px] hover:bg-gray-50 transition-colors cursor-default" title="{{ $subject->name }}">
                                                {{ $subject->name }}
                                            </th>
                                        @endforeach

                                        <!-- Summary Headers -->
                                        <th class="px-2 py-2 border-r border-gray-300 bg-blue-50 text-center [writing-mode:vertical-rl] rotate-180 h-32 font-bold text-blue-800">Total</th>
                                        <th class="px-2 py-2 border-r border-gray-300 bg-blue-50 text-center [writing-mode:vertical-rl] rotate-180 h-32 font-bold text-blue-800">Average</th>
                                        <th class="px-2 py-2 border-r border-gray-300 bg-green-50 text-center [writing-mode:vertical-rl] rotate-180 h-32 font-bold text-green-800">Rank</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($students as $index => $student)
                                        <tr class="hover:bg-gray-50 student-row group" data-student-id="{{ $student->id }}">
                                            <td class="px-2 py-1 border-r border-gray-200 text-center bg-gray-50">{{ $index + 1 }}</td>
                                            <td class="px-3 py-1 border-r border-gray-200 font-medium sticky left-0 bg-white z-10 group-hover:bg-gray-50 transition-colors shadow-sm whitespace-nowrap">{{ $student->full_name }}</td>
                                            <td class="px-2 py-1 border-r border-gray-200 text-center text-gray-500">{{ $student->gender ?? '-' }}</td>
                                            
                                            @foreach($subjects as $subject)
                                                @php
                                                    $score = $marksMap[$student->id][$subject->id] ?? '';
                                                    
                                                    // Logic to disable input
                                                    $disabled = false;
                                                    $placeholder = '-';
                                                    $title = '';
                                                    
                                                    // 1. Term Lock
                                                    if (!$term->is_master_grading_open) {
                                                        $disabled = true;
                                                    }

                                                    // 2. Elective Enrollment Check
                                                    if ($subject->is_elective) {
                                                        $enrolled = isset($studentElectives[$student->id]) && in_array($subject->id, $studentElectives[$student->id]);
                                                        if (!$enrolled) {
                                                            $disabled = true;
                                                            $placeholder = 'N/A';
                                                            $title = 'Student not enrolled in this elective';
                                                        }
                                                        // 3. Elective Term Type Check (Only Semester)
                                                        elseif ($term->type === 'quarter') {
                                                            $disabled = true;
                                                            $title = 'Electives are graded in Semesters only';
                                                        }
                                                    } 
                                                    // 4. Regular Subject Term Type Check (Only Quarter for manual entry, Semester is calculated)
                                                    else {
                                                        if ($term->type === 'semester') {
                                                            $disabled = true;
                                                            $title = 'Regular subjects are calculated automatically in Semesters';
                                                        }
                                                    }
                                                @endphp
                                                <td class="p-0 border-r border-gray-200 text-center relative h-10" title="{{ $title }}">
                                                    <input type="number" 
                                                           name="marks[{{ $student->id }}][{{ $subject->id }}]" 
                                                           value="{{ $score }}" 
                                                           min="0" max="100" step="0.01"
                                                           class="w-full h-full p-1 text-center text-sm border-0 focus:ring-2 focus:ring-inset focus:ring-blue-500 bg-transparent mark-input transition-colors hover:bg-gray-50 disabled:bg-gray-100 disabled:text-gray-500"
                                                           placeholder="{{ $placeholder }}"
                                                           onchange="calculateRow(this.closest('tr'))"
                                                           @if($disabled) disabled @endif>
                                                </td>
                                            @endforeach

                                            <td class="px-2 py-1 border-r border-gray-200 text-center font-bold bg-blue-50 text-blue-900 student-total">0</td>
                                            <td class="px-2 py-1 border-r border-gray-200 text-center font-bold bg-blue-50 text-blue-900 student-average">0.00</td>
                                            <td class="px-2 py-1 border-r border-gray-200 text-center font-bold bg-green-50 text-green-900 student-rank">-</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-6 flex justify-end gap-3 fixed bottom-6 right-6 z-50">
                            <a href="{{ route('admin.section-grades.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-3 px-6 rounded shadow-lg">
                                Warning: Unsaved Changes
                            </a>
                            <button type="button" onclick="calculateAll()" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded shadow-lg">
                                Recalculate
                            </button>
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded shadow-lg">
                                Save All Grades
                            </button>
                            @if(!$term->is_master_grading_open)
                                <script>
                                    document.querySelector('button[type="submit"].bg-green-600').style.display = 'none';
                                    document.querySelector('a.bg-gray-500').style.display = 'none';
                                </script>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Auto-Calculate Confirmation Modal -->
    <div x-data="{ open: false }" 
         x-show="open" 
         @open-calc-modal.window="open = true" 
         @keydown.escape.window="open = false"
         style="display: none;" 
         class="fixed inset-0 z-50 overflow-y-auto" 
         aria-labelledby="modal-title" 
         role="dialog" 
         aria-modal="true">
        
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
                 @click="open = false"
                 x-transition:enter="ease-out duration-300" 
                 x-transition:enter-start="opacity-0" 
                 x-transition:enter-end="opacity-100" 
                 x-transition:leave="ease-in duration-200" 
                 x-transition:leave-start="opacity-100" 
                 x-transition:leave-end="opacity-0"></div>

            <!-- Modal panel -->
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
                 x-transition:enter="ease-out duration-300" 
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave="ease-in duration-200" 
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                            <!-- Icon -->
                            <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Confirm Auto-Calculation
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    Are you sure? This will <strong>overwrite existing Semester grades</strong> for all Regular subjects in this section based on Quarter averages.
                                    <br><br>
                                    Formula: <code>(Q1 + Q2) / 2</code>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" onclick="document.getElementById('calculate-form').submit()" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Yes, Calculate
                    </button>
                    <button type="button" @click="open = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

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
                    // Validation
                    if (val > 100) {
                        alert('Score cannot exceed 100');
                        input.value = 100;
                        total += 100;
                    } else if (val < 0) {
                        input.value = 0;
                    } else {
                        total += val;
                    }
                    count++; // Count subjects with marks entered? Or count all subjects?
                             // Usually Average = Total / Number of Subjects.
                }
            });
            
            // Assuming Average is Total / Total Number of Subjects (columns)
            // Or Total / Number of Entered marks? Usually Total / Subject Count.
            const subjectCount = {{ $subjects->count() }};
            const average = subjectCount > 0 ? (total / subjectCount) : 0;

            row.querySelector('.student-total').innerText = total.toFixed(0); // Assuming integer total? Or float?
            row.querySelector('.student-average').innerText = average.toFixed(2);
            
            // Re-calculate ranks if needed (expensive on every change, maybe only on save or specific button?)
            // For now, let's update rank only when "Recalculate" is clicked or page loads.
        }

        function calculateAll() {
            const rows = document.querySelectorAll('.student-row');
            let studentData = [];

            // Calculate Totals and Averages
            rows.forEach(row => {
                calculateRow(row);
                const avg = parseFloat(row.querySelector('.student-average').innerText);
                studentData.push({ row: row, average: avg });
            });

            // Calculate Rank
            // Sort by average descending
            studentData.sort((a, b) => b.average - a.average);

            let currentRank = 1;
            for (let i = 0; i < studentData.length; i++) {
                // Handling ties? Standard rank: 1, 2, 2, 4...
                if (i > 0 && studentData[i].average < studentData[i-1].average) {
                    currentRank = i + 1;
                }
                // If marks equal, rank stays same as previous (i+1 would be standard competition rank)
                // But for dense ranking (1,2,2,3), we'd increment only on difference.
                // Standard academic rank is usually competition rank (1, 2, 2, 4).
                
                studentData[i].row.querySelector('.student-rank').innerText = currentRank;
            }
        }
    </script>
    @endpush
    </div>
</x-admin-layout>
