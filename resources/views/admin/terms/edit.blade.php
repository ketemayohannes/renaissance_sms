<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Term') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form action="{{ route('admin.terms.update', $term) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Academic Year -->
                            <div>
                                <label for="academic_year_id" class="block text-sm font-medium text-gray-700">Academic Year</label>
                                <select name="academic_year_id" id="academic_year_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    @foreach($academicYears as $year)
                                        <option value="{{ $year->id }}" {{ $term->academic_year_id == $year->id ? 'selected' : '' }}>{{ $year->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Name -->
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Term Name</label>
                                <input type="text" name="name" id="name" value="{{ $term->name }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            </div>

                            <!-- Type -->
                            <div>
                                <label for="type" class="block text-sm font-medium text-gray-700">Type</label>
                                <select name="type" id="type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    <option value="semester" {{ $term->type == 'semester' ? 'selected' : '' }}>Semester</option>
                                    <option value="quarter" {{ $term->type == 'quarter' ? 'selected' : '' }}>Quarter</option>
                                </select>
                            </div>

                            <!-- Term Number -->
                            <div>
                                <label for="term_number" class="block text-sm font-medium text-gray-700">Term Number</label>
                                <input type="number" name="term_number" id="term_number" value="{{ $term->term_number }}" min="1" max="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            </div>

                            <!-- Quarter Selection (for semesters only) -->
                            <div id="quarterSelectionDiv" class="md:col-span-2" style="display: none;">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Selected Quarters</label>
                                <div id="quarterCheckboxes" class="space-y-2">
                                    <!-- Quarters will be loaded here via JavaScript -->
                                </div>
                                @error('quarter_ids')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-2 text-sm text-gray-500">Select exactly 2 consecutive quarters.</p>
                            </div>

                            <!-- Start Date -->
                            <div id="startDateDiv">
                                <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                                <input type="date" name="start_date" id="start_date" value="{{ $term->start_date ? $term->start_date->format('Y-m-d') : '' }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('start_date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- End Date -->
                            <div id="endDateDiv">
                                <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                                <input type="date" name="end_date" id="end_date" value="{{ $term->end_date ? $term->end_date->format('Y-m-d') : '' }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('end_date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Grading Locks -->
                            <div class="md:col-span-2 border-t pt-4 mt-2">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Grading Controls</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="block">
                                        <label for="is_grading_open" class="inline-flex items-center">
                                            <input id="is_grading_open" type="checkbox" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500" name="is_grading_open" value="1" {{ old('is_grading_open', $term->is_grading_open) ? 'checked' : '' }}>
                                            <span class="ml-2 text-sm text-gray-700 font-bold">{{ __('Subject Grading Open') }}</span>
                                        </label>
                                        <p class="text-xs text-gray-500 ml-6">Allow teachers to enter marks for subjects.</p>
                                    </div>
                                    <div class="block">
                                        <label for="is_master_grading_open" class="inline-flex items-center">
                                            <input id="is_master_grading_open" type="checkbox" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500" name="is_master_grading_open" value="1" {{ old('is_master_grading_open', $term->is_master_grading_open) ? 'checked' : '' }}>
                                            <span class="ml-2 text-sm text-gray-700 font-bold">{{ __('Master Sheet Grading Open') }}</span>
                                        </label>
                                        <p class="text-xs text-gray-500 ml-6">Allow admins to enter marks in Master Sheet.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <a href="{{ route('admin.terms.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded mr-2">Cancel</a>
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                                Update Term
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const typeSelect = document.getElementById('type');
            const academicYearSelect = document.getElementById('academic_year_id');
            const quarterSelectionDiv = document.getElementById('quarterSelectionDiv');
            const quarterCheckboxes = document.getElementById('quarterCheckboxes');
            const startDateDiv = document.getElementById('startDateDiv');
            const endDateDiv = document.getElementById('endDateDiv');
            
            // Get currently assigned quarter IDs
            const assignedQuarterIds = @json($term->quarters->pluck('id')->toArray());

            function toggleFields() {
                const isSemester = typeSelect.value === 'semester';
                
                if (isSemester) {
                    quarterSelectionDiv.style.display = 'block';
                    startDateDiv.style.display = 'none';
                    endDateDiv.style.display = 'none';
                    document.getElementById('start_date').removeAttribute('required');
                    document.getElementById('end_date').removeAttribute('required');
                    loadQuarters();
                } else {
                    quarterSelectionDiv.style.display = 'none';
                    startDateDiv.style.display = 'block';
                    endDateDiv.style.display = 'block';
                    document.getElementById('start_date').setAttribute('required', 'required');
                    document.getElementById('end_date').setAttribute('required', 'required');
                }
            }

            function loadQuarters() {
                const academicYearId = academicYearSelect.value;
                if (!academicYearId) return;

                // For Edit, we need all quarters from the academic year, not just unassigned ones
                // Actually, the API might need to be adjusted or we just fetch and filter.
                // The current API: /admin/terms/quarters/{academicYearId} filters by whereNull('parent_term_id')
                // This is a bug for EDITING because it won't show the ALREADY assigned quarters.
                
                // We'll call a version that includes current ones if possible, or just fetch all
                fetch(`/admin/terms/quarters/${academicYearId}?include_assigned_to=${@json($term->id)}`)
                    .then(response => response.json())
                    .then(quarters => {
                        let html = '';
                        quarters.forEach(quarter => {
                            const checked = assignedQuarterIds.includes(quarter.id) ? 'checked' : '';
                            html += `
                                <div class="flex items-center">
                                    <input type="checkbox" name="quarter_ids[]" value="${quarter.id}" id="quarter_${quarter.id}" 
                                           ${checked}
                                           class="quarter-checkbox rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <label for="quarter_${quarter.id}" class="ml-2 text-sm text-gray-700">
                                        ${quarter.name} (${quarter.start_date} to ${quarter.end_date})
                                    </label>
                                </div>
                            `;
                        });
                        quarterCheckboxes.innerHTML = html || '<p class="text-sm text-gray-500">No available quarters found.</p>';
                    });
            }

            typeSelect.addEventListener('change', toggleFields);
            academicYearSelect.addEventListener('change', toggleFields);
            
            toggleFields();
        });
    </script>
</x-app-layout>
