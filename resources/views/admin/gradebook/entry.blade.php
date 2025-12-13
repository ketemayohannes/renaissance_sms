<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Grade Entry') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Breadcrumb -->
            <x-breadcrumb :items="[
                ['label' => 'Gradebook', 'url' => route('admin.gradebook.index')],
                ['label' => 'Grade Entry', 'url' => '#']
            ]" />

            <!-- Selection Summary -->
            
            @if(!$term->is_grading_open)
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                    <p class="font-bold">Grading Disabled</p>
                    <p>Subject grading is currently closed for this term.</p>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-gray-200 flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Grade Entry Details</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div>
                                <p class="text-sm text-gray-600">Academic Year</p>
                                <p class="font-semibold text-gray-900">{{ $academicYear->name }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Term</p>
                                <p class="font-semibold text-gray-900">{{ $term->name }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Grade & Section</p>
                                <p class="font-semibold text-gray-900">{{ $section->gradeLevel->name }} - {{ $section->name }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Subject</p>
                                <p class="font-semibold text-gray-900">{{ $subject->name }} ({{ $subject->code }})</p>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col space-y-2">
                        <a href="{{ route('admin.gradebook.export-template', ['academic_year_id' => $academicYear->id, 'term_id' => $term->id, 'section_id' => $section->id, 'subject_id' => $subject->id]) }}" 
                           class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Export Template
                        </a>
                                    </svg>
                            Export Template
                        </a>
                        <button type="button" @if($term->is_grading_open) onclick="document.getElementById('importModal').classList.remove('hidden')" @endif
                                class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 @if(!$term->is_grading_open) opacity-50 cursor-not-allowed @endif"
                                @if(!$term->is_grading_open) disabled @endif>
                            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                            </svg>
                            Import Grades
                        </button>
                    </div>
                </div>
            </div>

            <!-- Import Modal -->
            <div id="importModal" class="fixed z-10 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="document.getElementById('importModal').classList.add('hidden')"></div>
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                    <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                        <form action="{{ route('admin.gradebook.import') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="academic_year_id" value="{{ $academicYear->id }}">
                            <input type="hidden" name="term_id" value="{{ $term->id }}">
                            <input type="hidden" name="section_id" value="{{ $section->id }}">
                            <input type="hidden" name="subject_id" value="{{ $subject->id }}">
                            
                            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                <div class="sm:flex sm:items-start">
                                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10">
                                        <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                        </svg>
                                    </div>
                                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Import Grades</h3>
                                        <div class="mt-2">
                                            <p class="text-sm text-gray-500 mb-4">Upload the filled CSV template. Ensure Student IDs match.</p>
                                            <input type="file" name="file" accept=".csv,.txt" class="w-full border border-gray-300 rounded-md p-2">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm">
                                    Import
                                </button>
                                <button type="button" onclick="document.getElementById('importModal').classList.add('hidden')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('warning'))
                <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative mb-4">
                    {{ session('warning') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Grade Entry Form -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if($gradeComponents->isEmpty())
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No Assessment Templates Defined</h3>
                            <p class="mt-1 text-sm text-gray-500">You need to define assessment templates for this subject and grade level before entering marks.</p>
                            <div class="mt-6">
                                <a href="{{ route('admin.assessment-templates.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Create Assessment Template
                                </a>
                            </div>
                        </div>
                    @else
                        <form action="{{ route('admin.gradebook.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="academic_year_id" value="{{ $academicYear->id }}">
                            <input type="hidden" name="section_id" value="{{ $section->id }}">
                            <input type="hidden" name="subject_id" value="{{ $subject->id }}">
                            <input type="hidden" name="term_id" value="{{ $term->id }}">

                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 border border-gray-300">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider sticky left-0 bg-gray-50 z-10 border-r border-gray-300">
                                                Student ID
                                            </th>
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider sticky left-24 bg-gray-50 z-10 border-r border-gray-300">
                                                Full Name
                                            </th>
                                            <th scope="col" class="px-2 py-3 text-center text-xs font-bold text-gray-700 uppercase tracking-wider border-r border-gray-300">
                                                Gender
                                            </th>
                                            @foreach($gradeComponents as $component)
                                                <th scope="col" class="px-4 py-3 text-center text-xs font-bold text-gray-700 uppercase tracking-wider border-r border-gray-300">
                                                    <div>{{ $component->name }}</div>
                                                    <div class="text-xs text-gray-500">({{ $component->weight }}%)</div>
                                                </th>
                                            @endforeach
                                            <th scope="col" class="px-4 py-3 text-center text-xs font-bold text-gray-900 uppercase tracking-wider bg-gray-100 border-r border-gray-300">
                                                Class Assessment<br>(60%)
                                            </th>
                                            <th scope="col" class="px-4 py-3 text-center text-xs font-bold text-gray-900 uppercase tracking-wider bg-gray-100">
                                                Total<br>(100%)
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @forelse($students as $student)
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-4 py-2 whitespace-nowrap sticky left-0 bg-white z-10 border-r border-gray-200 text-sm text-gray-900">
                                                    {{ $student->student_id }}
                                                </td>
                                                <td class="px-4 py-2 whitespace-nowrap sticky left-24 bg-white z-10 border-r border-gray-200 text-sm font-medium text-gray-900">
                                                    {{ $student->full_name }}
                                                </td>
                                                <td class="px-2 py-2 whitespace-nowrap text-center border-r border-gray-200 text-sm text-gray-900">
                                                    {{ $student->gender ?? 'M' }}
                                                </td>
                                                @php
                                                    $totalScore = 0;
                                                    $classAssessmentScore = 0;
                                                @endphp
                                                @foreach($gradeComponents as $component)
                                                    @php
                                                        // Note: using assessment_template_id now
                                                        $mark = $existingMarks->get($student->id)?->firstWhere('assessment_template_id', $component->id);
                                                        $score = $mark ? $mark->score : 0;
                                                        $totalScore += $score;
                                                        
                                                        // Simple logic for class assessment vs final
                                                        if (!str_contains(strtolower($component->name), 'final')) {
                                                            $classAssessmentScore += $score;
                                                        }
                                                    @endphp
                                                    <td class="px-4 py-2 whitespace-nowrap text-center border-r border-gray-200">
                                                        <input type="number" 
                                                               name="marks[{{ $student->id }}][{{ $component->id }}][score]" 
                                                               value="{{ $mark?->score }}"
                                                               min="0" 
                                                               max="{{ $component->max_score }}" 
                                                               step="0.01"
                                                               class="w-20 text-center rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm disabled:bg-gray-100 disabled:text-gray-500"
                                                               onchange="calculateTotals(this)"
                                                               @if(!$term->is_grading_open) disabled @endif>
                                                    </td>
                                                @endforeach
                                                <td class="px-4 py-2 whitespace-nowrap text-center font-bold text-gray-900 bg-gray-50 border-r border-gray-200 class-assessment-total">
                                                    {{ $classAssessmentScore }}
                                                </td>
                                                <td class="px-4 py-2 whitespace-nowrap text-center font-bold text-gray-900 bg-gray-50 total-score">
                                                    {{ $totalScore }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="{{ $gradeComponents->count() + 5 }}" class="px-6 py-4 text-center text-gray-500">
                                                    No students found in this section.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            @if($students->count() > 0)
                                <div class="mt-6 flex justify-between items-center">
                                    <a href="{{ route('admin.gradebook.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded">
                                        Back to Selection
                                    </a>
                                    @if($term->is_grading_open)
                                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                                            Save Grades
                                        </button>
                                    @endif
                                </div>
                            @endif
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>



    @push('scripts')
    <script>
        function calculateTotals(input) {
            // Validate score doesn't exceed max
            const maxScore = parseFloat(input.getAttribute('max'));
            const enteredScore = parseFloat(input.value);
            
            if (enteredScore > maxScore) {
                console.log('Validation failed: Score', enteredScore, 'exceeds max', maxScore);
                console.log('Dispatching confirm-action event');
                
                const event = new CustomEvent('confirm-action', {
                    bubbles: true, // Make sure it bubbles up
                    detail: {
                        type: 'danger',
                        title: 'Invalid Score',
                        message: `Score ${enteredScore} exceeds the maximum allowed score of ${maxScore}. Please enter a valid score.`,
                        showCancel: false,
                        buttonText: 'OK'
                    }
                });
                window.dispatchEvent(event);
                console.log('Event dispatched:', event);
                
                input.value = maxScore; // Reset to max
                // input.focus();
                // input.select();
                // Continue calculation with the corrected value (maxScore)
            }
            
            const row = input.closest('tr');
            const inputs = row.querySelectorAll('input[type="number"]');
            let totalScore = 0;
            let classAssessmentScore = 0;

            inputs.forEach(inp => {
                const val = parseFloat(inp.value) || 0;
                totalScore += val;
                
                // Identify if this input belongs to a 'Final Exam' component
                const cellIndex = inp.closest('td').cellIndex;
                // Get header text safely
                const headerCell = input.closest('table').tHead.rows[0].cells[cellIndex];
                const headerText = headerCell ? headerCell.innerText.toLowerCase() : '';
                
                if (!headerText.includes('final')) {
                    classAssessmentScore += val;
                }
            });

            row.querySelector('.total-score').innerText = totalScore.toFixed(2);
            row.querySelector('.class-assessment-total').innerText = classAssessmentScore.toFixed(2);
        }
    </script>
    @endpush
</x-app-layout>
