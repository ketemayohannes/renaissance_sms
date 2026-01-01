<x-admin-layout>
    <x-slot name="header">Edit Grade Component</x-slot>

    <div class="space-y-6">
        <x-breadcrumb :items="[
            ['label' => 'Grade Components', 'url' => route('admin.grade-components.index')],
            ['label' => 'Edit', 'url' => '#']
        ]" />

        <div class="card overflow-hidden">
            <div class="p-6">
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

                    <form action="{{ route('admin.grade-components.update', $gradeComponent) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Academic Year -->
                            <div>
                                <label for="academic_year_id" class="block text-sm font-medium text-gray-700">Academic Year</label>
                                <select name="academic_year_id" id="academic_year_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    <option value="">Select Year</option>
                                    @foreach($academicYears as $year)
                                        <option value="{{ $year->id }}" {{ old('academic_year_id', $gradeComponent->academic_year_id) == $year->id ? 'selected' : '' }}>{{ $year->name }}</option>
                                    @endforeach
                                </select>
                                @error('academic_year_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Term -->
                            <div>
                                <label for="term_id" class="block text-sm font-medium text-gray-700">Term (Optional)</label>
                                <select name="term_id" id="term_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">All Terms</option>
                                    @foreach($terms as $term)
                                        <option value="{{ $term->id }}" {{ old('term_id', $gradeComponent->term_id) == $term->id ? 'selected' : '' }}>{{ $term->name }}</option>
                                    @endforeach
                                </select>
                                @error('term_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Grade Level -->
                            <div>
                                <label for="grade_level_id" class="block text-sm font-medium text-gray-700">Grade Level</label>
                                <select name="grade_level_id" id="grade_level_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    <option value="">Select Grade Level</option>
                                    @foreach($gradeLevels as $grade)
                                        <option value="{{ $grade->id }}" {{ old('grade_level_id', $gradeComponent->grade_level_id) == $grade->id ? 'selected' : '' }}>{{ $grade->name }}</option>
                                    @endforeach
                                </select>
                                @error('grade_level_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Subject -->
                            <div>
                                <label for="subject_id" class="block text-sm font-medium text-gray-700">Subject</label>
                                <select name="subject_id" id="subject_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    <option value="">Select Subject</option>
                                    @foreach($subjects as $subject)
                                        <option value="{{ $subject->id }}" {{ old('subject_id', $gradeComponent->subject_id) == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                                    @endforeach
                                </select>
                                @error('subject_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Assessment Type -->
                            <div>
                                <label for="assessment_type_id" class="block text-sm font-medium text-gray-700">Assessment Type</label>
                                <select name="assessment_type_id" id="assessment_type_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    <option value="">Select Type</option>
                                    @foreach($assessmentTypes as $type)
                                        <option value="{{ $type->id }}" {{ old('assessment_type_id', $gradeComponent->assessment_type_id) == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                                    @endforeach
                                </select>
                                @error('assessment_type_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Name -->
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Component Name</label>
                                <input type="text" name="name" id="name" value="{{ old('name', $gradeComponent->name) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="e.g., Quiz 1" required>
                                @error('name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Weight / Max Score -->
                            <div>
                                <label for="weight" class="block text-sm font-medium text-gray-700">Weight / Max Score</label>
                                <input type="number" name="weight" id="weight" value="{{ old('weight', $gradeComponent->weight) }}" min="0" max="100" step="0.01" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="e.g., 10" required>
                                <p class="mt-1 text-xs text-gray-500">The weight represents both the percentage of the final grade and the maximum score for this component.</p>
                                @error('weight')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Max Score (Hidden, auto-set to weight) -->
                            <input type="hidden" name="max_score" id="max_score" value="{{ old('weight', $gradeComponent->weight) }}">

                            <!-- Order -->
                            <div>
                                <label for="order" class="block text-sm font-medium text-gray-700">Display Order</label>
                                <input type="number" name="order" id="order" value="{{ old('order', $gradeComponent->order) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('order')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Is Active -->
                            <div class="flex items-center mt-6">
                                <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $gradeComponent->is_active) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <label for="is_active" class="ml-2 block text-sm text-gray-900">Active</label>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <a href="{{ route('admin.grade-components.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded mr-2">
                                Cancel
                            </a>
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Update Component
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
            const subjectSelect = document.getElementById('subject_id');
            
            // Initial load
            if(yearSelect.value) {
                // We don't reload terms on edit initial load to preserve the selected one, 
                // unless we want to ensure the list is up to date. 
                // But for subjects, we want to filter the list.
                if(gradeSelect.value) {
                    loadSubjects(yearSelect.value, gradeSelect.value, '{{ old('subject_id', $gradeComponent->subject_id) }}');
                }
            }

            yearSelect.addEventListener('change', function() {
                loadTerms(this.value);
                if(gradeSelect.value) {
                    loadSubjects(this.value, gradeSelect.value);
                }
            });

            gradeSelect.addEventListener('change', function() {
                if(yearSelect.value) {
                    loadSubjects(yearSelect.value, this.value);
                }
            });

            function loadTerms(yearId) {
                if(!yearId) {
                    termSelect.innerHTML = '<option value="">All Terms</option>';
                    return;
                }

                fetch(`{{ route('admin.gradebook.get-terms') }}?academic_year_id=${yearId}`)
                    .then(response => response.json())
                    .then(data => {
                        termSelect.innerHTML = '<option value="">All Terms</option>';
                        data.forEach(term => {
                            termSelect.innerHTML += `<option value="${term.id}">${term.name}</option>`;
                        });
                    });
            }

            function loadSubjects(yearId, gradeId, selectedSubjectId = null) {
                if(!yearId || !gradeId) {
                    subjectSelect.innerHTML = '<option value="">Select Subject</option>';
                    return;
                }

                fetch(`{{ route('admin.gradebook.get-subjects') }}?academic_year_id=${yearId}&grade_level_id=${gradeId}`)
                    .then(response => response.json())
                    .then(data => {
                        subjectSelect.innerHTML = '<option value="">Select Subject</option>';
                        if (data.length === 0) {
                            subjectSelect.innerHTML += '<option value="" disabled>No subjects found for this grade</option>';
                        }
                        data.forEach(subject => {
                            const selected = selectedSubjectId == subject.id ? 'selected' : '';
                            subjectSelect.innerHTML += `<option value="${subject.id}" ${selected}>${subject.name}</option>`;
                        });
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
