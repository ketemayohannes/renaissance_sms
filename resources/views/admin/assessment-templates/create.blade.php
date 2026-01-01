<x-admin-layout>
    <x-slot name="header">Create Grade Template</x-slot>

    <div class="space-y-6">
        <x-breadcrumb :items="[
            ['label' => 'Assessment Templates', 'url' => route('admin.assessment-templates.index')],
            ['label' => 'Create', 'url' => '#']
        ]" />

        <div class="card overflow-hidden">
            <div class="p-6">

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if($errors->any())
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                            <strong class="font-bold">Error!</strong>
                            <ul class="mt-2 list-disc list-inside">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('admin.assessment-templates.store') }}" method="POST">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Academic Year -->
                            <div>
                                <label for="academic_year_id" class="block text-sm font-medium text-gray-700">Academic Year</label>
                                <select name="academic_year_id" id="academic_year_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    <option value="">Select Year</option>
                                    @foreach($academicYears as $year)
                                        <option value="{{ $year->id }}" {{ old('academic_year_id') == $year->id ? 'selected' : '' }}>{{ $year->name }}</option>
                                    @endforeach
                                </select>
                                @error('academic_year_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Terms (Multiple) -->
                            <div>
                                <label for="term_ids" class="block text-sm font-medium text-gray-700">Terms</label>
                                <select name="term_ids[]" id="term_ids" multiple class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" size="3">
                                    <option value="">All Terms (Global)</option>
                                    <!-- Populated via AJAX -->
                                </select>
                                <p class="mt-1 text-xs text-gray-500">Hold Ctrl (Cmd on Mac) to select multiple terms. Leave 'All Terms' for global templates.</p>
                                @error('term_ids')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Grade Levels (Multiple) -->
                            <div>
                                <label for="grade_level_ids" class="block text-sm font-medium text-gray-700">Grade Levels</label>
                                <select name="grade_level_ids[]" id="grade_level_ids" multiple class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" size="5" required>
                                    @foreach($gradeLevels as $grade)
                                        <option value="{{ $grade->id }}" {{ (is_array(old('grade_level_ids')) && in_array($grade->id, old('grade_level_ids'))) ? 'selected' : '' }}>{{ $grade->name }}</option>
                                    @endforeach
                                </select>
                                <p class="mt-1 text-xs text-gray-500">Hold Ctrl (Cmd on Mac) to select multiple grade levels</p>
                                @error('grade_level_ids')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Subjects (Multiple) -->
                            <div>
                                <label for="subject_ids" class="block text-sm font-medium text-gray-700">Subjects</label>
                                <select name="subject_ids[]" id="subject_ids" multiple class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" size="5" required>
                                    <!-- Populated via AJAX based on selected grade levels -->
                                </select>
                                <p class="mt-1 text-xs text-gray-500">Hold Ctrl (Cmd on Mac) to select multiple subjects</p>
                                @error('subject_ids')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Assessment Type -->
                            <div>
                                <label for="assessment_type_id" class="block text-sm font-medium text-gray-700">Assessment Type</label>
                                <select name="assessment_type_id" id="assessment_type_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    <option value="">Select Type</option>
                                    @foreach($assessmentTypes as $type)
                                        <option value="{{ $type->id }}" {{ old('assessment_type_id') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                                    @endforeach
                                </select>
                                @error('assessment_type_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Name -->
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Template Name</label>
                                <input type="text" name="name" id="name" value="{{ old('name') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="e.g., Quiz 1" required>
                                @error('name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Weight / Max Score -->
                            <div>
                                <label for="weight" class="block text-sm font-medium text-gray-700">Weight / Max Score</label>
                                <input type="number" name="weight" id="weight" value="{{ old('weight') }}" min="0" max="100" step="0.01" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="e.g., 10" required>
                                <p class="mt-1 text-xs text-gray-500">The weight represents both the percentage of the final grade and the maximum score for this Template.</p>
                                @error('weight')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Max Score (Hidden, auto-set to weight) -->
                            <input type="hidden" name="max_score" id="max_score" value="{{ old('weight') }}">

                            <!-- Order -->
                            <div>
                                <label for="order" class="block text-sm font-medium text-gray-700">Display Order</label>
                                <input type="number" name="order" id="order" value="{{ old('order', 0) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('order')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Is Active -->
                            <div class="flex items-center mt-6">
                                <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <label for="is_active" class="ml-2 block text-sm text-gray-900">Active</label>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <a href="{{ route('admin.assessment-templates.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded mr-2">
                                Cancel
                            </a>
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Create Template
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
            const gradeSelect = document.getElementById('grade_level_ids');
            const termSelect = document.getElementById('term_id');
            const subjectSelect = document.getElementById('subject_ids');
            
            // Initial load if values are selected (e.g. validation error)
            if(yearSelect.value) {
                loadTerms(yearSelect.value, '{{ old('term_id') }}');
            }
            
            // Always load subjects for all selected grade levels on page load
            const initialGrades = Array.from(gradeSelect.selectedOptions).map(opt => opt.value);
            if(yearSelect.value && initialGrades.length > 0) {
                loadSubjects(yearSelect.value, initialGrades, {{ json_encode(old('subject_ids', [])) }});
            }

            yearSelect.addEventListener('change', function() {
                loadTerms(this.value);
                const selectedGrades = Array.from(gradeSelect.selectedOptions).map(opt => opt.value);
                if(selectedGrades.length > 0 && this.value) {
                    loadSubjects(this.value, selectedGrades);
                } else {
                    subjectSelect.innerHTML = '';
                }
            });

            gradeSelect.addEventListener('change', function() {
                const selectedGrades = Array.from(this.selectedOptions).map(opt => opt.value);
                if(yearSelect.value && selectedGrades.length > 0) {
                    loadSubjects(yearSelect.value, selectedGrades);
                } else {
                    subjectSelect.innerHTML = '';
                }
            });

            function loadTerms(yearId, selectedTermIds = []) {
                const termSelect = document.getElementById('term_ids');
                if(!yearId) {
                    termSelect.innerHTML = '<option value="">All Terms (Global)</option>';
                    return;
                }

                // Ensure selectedTermIds is an array
                if (!Array.isArray(selectedTermIds)) {
                    selectedTermIds = selectedTermIds ? [selectedTermIds] : [];
                }

                fetch(`{{ route('admin.gradebook.get-terms') }}?academic_year_id=${yearId}`)
                    .then(response => response.json())
                    .then(data => {
                        termSelect.innerHTML = '<option value="">All Terms (Global)</option>';
                        data.forEach(term => {
                            const selected = selectedTermIds.includes(term.id.toString()) ? 'selected' : '';
                            termSelect.innerHTML += `<option value="${term.id}" ${selected}>${term.name}</option>`;
                        });
                    });
            }

            function loadSubjects(yearId, gradeIds, selectedSubjectIds = []) {
                console.log('loadSubjects called with:', {yearId, gradeIds, selectedSubjectIds});
                
                if(!yearId || gradeIds.length === 0) {
                    console.log('Skipping subject load - missing yearId or gradeIds');
                    subjectSelect.innerHTML = '';
                    return;
                }

                // Fetch subjects for all selected grade levels
                const promises = gradeIds.map(gradeId => 
                    fetch(`{{ route('admin.gradebook.get-subjects') }}?academic_year_id=${yearId}&grade_level_id=${gradeId}`)
                        .then(response => response.json())
                );

                Promise.all(promises)
                    .then(results => {
                        console.log('Subjects fetched:', results);
                        
                        // Merge and deduplicate subjects from all grade levels
                        const subjectMap = new Map();
                        results.forEach(subjects => {
                            subjects.forEach(subject => {
                                subjectMap.set(subject.id, subject);
                            });
                        });

                        console.log('Unique subjects:', Array.from(subjectMap.values()));

                        subjectSelect.innerHTML = '';
                        if (subjectMap.size === 0) {
                            subjectSelect.innerHTML = '<option value="" disabled>No subjects found for selected grades</option>';
                        } else {
                            Array.from(subjectMap.values()).forEach(subject => {
                                const selected = selectedSubjectIds.includes(subject.id) ? 'selected' : '';
                                subjectSelect.innerHTML += `<option value="${subject.id}" ${selected}>${subject.name}</option>`;
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error loading subjects:', error);
                        subjectSelect.innerHTML = '<option value="">Error loading subjects</option>';
                    });
            }
        });
    </script>
    @endpush
    </div>
</x-admin-layout>
