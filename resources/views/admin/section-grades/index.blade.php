<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Section Grade Entry (Master Sheet)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-breadcrumb :items="[
                ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
                ['label' => 'Section Grades', 'url' => '#']
            ]" />

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium text-gray-900 border-b pb-2 mb-4">Select Criteria</h3>
                    
                    <form action="{{ route('admin.section-grades.entry') }}" method="GET">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
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
                                <label for="term_id" class="block text-sm font-medium text-gray-700">Term</label>
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
                        </div>

                        <div class="mt-6 flex justify-end">
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition duration-150 ease-in-out">
                                Load Master Sheet
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

            // Initial load
            if(yearSelect.value) {
                loadTerms(yearSelect.value);
            }

            yearSelect.addEventListener('change', function() {
                loadTerms(this.value);
            });

            gradeSelect.addEventListener('change', function() {
                if(this.value && yearSelect.value) {
                    loadSections(yearSelect.value, this.value);
                } else {
                    sectionSelect.innerHTML = '<option value="">Select Section</option>';
                }
            });

            function loadTerms(yearId) {
                if(!yearId) return;
                // Reuse existing gradebook route for terms
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
                // Reuse existing gradebook route for sections
                fetch(`{{ route('admin.gradebook.get-sections') }}?academic_year_id=${yearId}&grade_level_id=${gradeId}`)
                    .then(response => response.json())
                    .then(data => {
                        sectionSelect.innerHTML = '<option value="">Select Section</option>';
                        data.forEach(section => {
                            sectionSelect.innerHTML += `<option value="${section.id}">${section.name}</option>`;
                        });
                    });
            }
        });
    </script>
    @endpush
</x-app-layout>
