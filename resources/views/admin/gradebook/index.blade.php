<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Gradebook Management') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Breadcrumb -->
            <nav class="flex mb-4" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600">
                            <svg class="w-3 h-3 mr-2.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                <path d="m19.707 9.293-2-2-7-7a1 1 0 0 0-1.414 0l-7 7-2 2a1 1 0 0 0 1.414 1.414L2 10.414V18a2 2 0 0 0 2 2h3a1 1 0 0 0 1-1v-4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v4a1 1 0 0 0 1 1h3a2 2 0 0 0 2-2v-7.586l.293.293a1 1 0 0 0 1.414-1.414Z"/>
                            </svg>
                            Dashboard
                        </a>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="w-3 h-3 text-gray-400 mx-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
                            </svg>
                            <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Gradebook</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center border-b pb-2 mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Select Criteria to Enter Marks</h3>
                        <a href="{{ route('admin.section-grades.index') }}" class="text-sm text-blue-600 hover:text-blue-900 font-semibold">
                            Switch to Master Sheet Entry &rarr;
                        </a>
                    </div>
                    
                    <form action="{{ route('admin.gradebook.entry') }}" method="GET">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <!-- Academic Year -->
                            <div>
                                <label for="academic_year_id" class="block text-sm font-medium text-gray-700">Academic Year</label>
                                <select name="academic_year_id" id="academic_year_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    <option value="">Select Year</option>
                                    @foreach($academicYears as $year)
                                        <option value="{{ $year->id }}" {{ $year->is_active ? 'selected' : '' }}>{{ $year->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Term -->
                            <div>
                                <label for="term_id" class="block text-sm font-medium text-gray-700">Term (Semester/Quarter)</label>
                                <select name="term_id" id="term_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    <option value="">Select Term</option>
                                    <!-- Populated via AJAX -->
                                </select>
                            </div>

                            <!-- Grade Level -->
                            <div>
                                <label for="grade_level_id" class="block text-sm font-medium text-gray-700">Grade Level</label>
                                <select name="grade_level_id" id="grade_level_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    <option value="">Select Grade Level</option>
                                    @foreach($gradeLevels as $grade)
                                        <option value="{{ $grade->id }}">{{ $grade->name }} ({{ $grade->division->name }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Section -->
                            <div>
                                <label for="section_id" class="block text-sm font-medium text-gray-700">Section</label>
                                <select name="section_id" id="section_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    <option value="">Select Section</option>
                                    <!-- Populated via AJAX -->
                                </select>
                            </div>

                            <!-- Subject -->
                            <div>
                                <label for="subject_id" class="block text-sm font-medium text-gray-700">Subject</label>
                                <select name="subject_id" id="subject_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    <option value="">Select Subject</option>
                                    <!-- Populated via AJAX -->
                                </select>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition duration-150 ease-in-out">
                                Load Gradebook
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const yearSelect = document.getElementById('academic_year_id');
            const gradeSelect = document.getElementById('grade_level_id');
            const termSelect = document.getElementById('term_id');
            const sectionSelect = document.getElementById('section_id');
            const subjectSelect = document.getElementById('subject_id');

            // Initial load of terms if year is selected
            if(yearSelect.value) {
                loadTerms(yearSelect.value);
            }

            yearSelect.addEventListener('change', function() {
                loadTerms(this.value);
                resetDependentDropdowns();
            });

            gradeSelect.addEventListener('change', function() {
                if(this.value && yearSelect.value) {
                    loadSections(yearSelect.value, this.value);
                    loadSubjects(yearSelect.value, this.value);
                } else {
                    sectionSelect.innerHTML = '<option value="">Select Section</option>';
                    subjectSelect.innerHTML = '<option value="">Select Subject</option>';
                }
            });

            function loadTerms(yearId) {
                if(!yearId) return;
                fetch(`{{ route('admin.gradebook.get-terms') }}?academic_year_id=${yearId}`)
                    .then(response => response.json())
                    .then(data => {
                        termSelect.innerHTML = '<option value="">Select Term</option>';
                        data.forEach(term => {
                            termSelect.innerHTML += `<option value="${term.id}">${term.name}</option>`;
                        });
                    });
            }

            function loadSections(yearId, gradeId) {
                console.log('Loading sections for year:', yearId, 'grade:', gradeId);
                fetch(`{{ route('admin.gradebook.get-sections') }}?academic_year_id=${yearId}&grade_level_id=${gradeId}`)
                    .then(response => {
                        if (!response.ok) throw new Error('Network response was not ok');
                        return response.json();
                    })
                    .then(data => {
                        console.log('Sections loaded:', data);
                        sectionSelect.innerHTML = '<option value="">Select Section</option>';
                        if (data.length === 0) {
                            sectionSelect.innerHTML += '<option value="" disabled>No sections found</option>';
                        }
                        data.forEach(section => {
                            sectionSelect.innerHTML += `<option value="${section.id}">${section.name}</option>`;
                        });
                    })
                    .catch(error => {
                        console.error('Error loading sections:', error);
                        sectionSelect.innerHTML = '<option value="">Error loading sections</option>';
                    });
            }

            function loadSubjects(yearId, gradeId) {
                console.log('Loading subjects for year:', yearId, 'grade:', gradeId);
                fetch(`{{ route('admin.gradebook.get-subjects') }}?academic_year_id=${yearId}&grade_level_id=${gradeId}`)
                    .then(response => {
                        if (!response.ok) throw new Error('Network response was not ok');
                        return response.json();
                    })
                    .then(data => {
                        console.log('Subjects loaded:', data);
                        subjectSelect.innerHTML = '<option value="">Select Subject</option>';
                        if (data.length === 0) {
                            subjectSelect.innerHTML += '<option value="" disabled>No subjects found</option>';
                        }
                        data.forEach(subject => {
                            subjectSelect.innerHTML += `<option value="${subject.id}">${subject.name} (${subject.code})</option>`;
                        });
                    })
                    .catch(error => {
                        console.error('Error loading subjects:', error);
                        subjectSelect.innerHTML = '<option value="">Error loading subjects</option>';
                    });
            }

            function resetDependentDropdowns() {
                gradeSelect.value = '';
                sectionSelect.innerHTML = '<option value="">Select Section</option>';
                subjectSelect.innerHTML = '<option value="">Select Subject</option>';
            }
        });
    </script>
    @endpush
</x-app-layout>
