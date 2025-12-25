<x-admin-layout>
    <x-slot name="header">Bulk Assign Electives</x-slot>

    <div class="space-y-6">
        <x-breadcrumb :items="[
            ['label' => 'Electives', 'url' => '#'],
            ['label' => 'Bulk Assign', 'url' => '#']
        ]" />

        <div class="card overflow-hidden">
            <div class="p-6">

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

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form action="{{ route('admin.electives.bulk-assign.store') }}" method="POST">
                        @csrf
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <!-- Academic Year -->
                            <div>
                                <label for="academic_year_id" class="block text-sm font-medium text-gray-700">Academic Year</label>
                                <select id="academic_year_id" name="academic_year_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    @foreach($academicYears as $year)
                                        <option value="{{ $year->id }}">{{ $year->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Grade Level -->
                            <div>
                                <label for="grade_level_id" class="block text-sm font-medium text-gray-700">Grade Level</label>
                                <select id="grade_level_id" name="grade_level_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required onchange="loadSubjects()">
                                    <option value="">Select Grade Level</option>
                                    @foreach($gradeLevels as $grade)
                                        <option value="{{ $grade->id }}">{{ $grade->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Subject -->
                        <div class="mb-6">
                            <label for="subject_id" class="block text-sm font-medium text-gray-700">Elective Subject</label>
                            <select id="subject_id" name="subject_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required disabled>
                                <option value="">Select Grade Level First</option>
                            </select>
                        </div>

                        <!-- Target Type Toggle -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Assign Mode</label>
                            <div class="flex items-center space-x-4">
                                <label class="inline-flex items-center">
                                    <input type="radio" name="target_type" value="section" class="form-radio text-indigo-600" checked onchange="toggleTargetType('section')">
                                    <span class="ml-2">By Section</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="target_type" value="student" class="form-radio text-indigo-600" onchange="toggleTargetType('student')">
                                    <span class="ml-2">By Student</span>
                                </label>
                            </div>
                        </div>

                        <!-- Sections Selector -->
                        <div id="section-selector" class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Select Sections</label>
                            <div id="sections-container" class="grid grid-cols-2 md:grid-cols-4 gap-4 p-4 border rounded bg-gray-50 max-h-60 overflow-y-auto">
                                <p class="text-gray-500 text-sm italic col-span-full">Select Grade Level first.</p>
                            </div>
                        </div>

                        <!-- Students Selector -->
                        <div id="student-selector" class="mb-6 hidden">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Select Students</label>
                            <div class="mb-2">
                                <input type="text" id="student-search" placeholder="Search students..." class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" onkeyup="filterStudents()">
                            </div>
                            <div id="students-container" class="grid grid-cols-1 md:grid-cols-3 gap-2 p-4 border rounded bg-gray-50 max-h-96 overflow-y-auto">
                                <p class="text-gray-500 text-sm italic col-span-full">Select Grade Level first.</p>
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-6 rounded shadow">
                                Assign Elective
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function toggleTargetType(type) {
            const sectionSelector = document.getElementById('section-selector');
            const studentSelector = document.getElementById('student-selector');

            if (type === 'section') {
                sectionSelector.classList.remove('hidden');
                studentSelector.classList.add('hidden');
            } else {
                sectionSelector.classList.add('hidden');
                studentSelector.classList.remove('hidden');
            }
        }

        function loadSubjects() {
            const gradeLevelId = document.getElementById('grade_level_id').value;
            const academicYearId = document.getElementById('academic_year_id').value;
            const subjectSelect = document.getElementById('subject_id');
            const sectionsContainer = document.getElementById('sections-container');
            const studentsContainer = document.getElementById('students-container');

            // Reset and Show Loading
            subjectSelect.innerHTML = '<option value="">Loading...</option>';
            subjectSelect.disabled = true;
            sectionsContainer.innerHTML = '<p class="text-gray-500 text-sm italic col-span-full">Loading sections...</p>';
            studentsContainer.innerHTML = '<p class="text-gray-500 text-sm italic col-span-full">Loading students...</p>';

            // Fetch Subjects
            fetch(`{{ route('admin.electives.get-subjects') }}?grade_level_id=${gradeLevelId}`)
                .then(response => {
                    if (!response.ok) throw new Error('Failed to load subjects');
                    return response.json();
                })
                .then(data => {
                    if (data.length === 0) {
                        subjectSelect.innerHTML = '<option value="">No Electives found for this Grade</option>';
                        subjectSelect.disabled = true;
                        sectionsContainer.innerHTML = '<p class="text-gray-500 text-sm italic col-span-full">Select Grade Level first.</p>'; // Reset
                        studentsContainer.innerHTML = '<p class="text-gray-500 text-sm italic col-span-full">Select Grade Level first.</p>'; // Reset
                        alert('No elective subjects are assigned to this Grade Level. Please go to Subject Assignments to assign electives first.');
                        return;
                    }
                    let options = '<option value="">Select Subject</option>';
                    data.forEach(subject => {
                        options += `<option value="${subject.id}">${subject.name} (${subject.code})</option>`;
                    });
                    subjectSelect.innerHTML = options;
                    subjectSelect.disabled = false;
                })
                .catch(error => {
                    console.error('Error fetching subjects:', error);
                    subjectSelect.innerHTML = '<option value="">Error loading subjects</option>';
                });

            // Fetch Sections
            fetch(`{{ route('admin.electives.get-sections') }}?grade_level_id=${gradeLevelId}&academic_year_id=${academicYearId}`)
                .then(response => {
                    if (!response.ok) throw new Error('Failed to load sections');
                    return response.json();
                })
                .then(data => {
                    if (data.length === 0) {
                        sectionsContainer.innerHTML = '<p class="text-red-500 text-sm italic col-span-full">No active sections found.</p>';
                        return;
                    }
                    let html = '';
                    data.forEach(section => {
                        html += `
                            <label class="flex items-center space-x-2 p-2 bg-white rounded shadow-sm hover:bg-gray-50 cursor-pointer">
                                <input type="checkbox" name="section_ids[]" value="${section.id}" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <span>${section.name}</span>
                            </label>
                        `;
                    });
                    sectionsContainer.innerHTML = html;
                })
                .catch(error => {
                    console.error('Error fetching sections:', error);
                    sectionsContainer.innerHTML = '<p class="text-red-500 text-sm italic col-span-full">Error loading sections.</p>';
                });

            // Fetch Students
            fetch(`{{ route('admin.electives.get-students') }}?grade_level_id=${gradeLevelId}&academic_year_id=${academicYearId}`)
                .then(response => {
                    if (!response.ok) throw new Error('Failed to load students');
                    return response.json();
                })
                .then(data => {
                     if (data.length === 0) {
                        studentsContainer.innerHTML = '<p class="text-red-500 text-sm italic col-span-full">No active students found.</p>';
                        return;
                    }
                    let html = '';
                    // Checkbox for Select All
                    html += `
                        <div class="col-span-full mb-2">
                             <label class="inline-flex items-center">
                                <input type="checkbox" id="select-all-students" onchange="selectAllStudents(this)" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <span class="ml-2 font-bold text-gray-700">Select All</span>
                            </label>
                        </div>
                    `;

                    data.forEach(student => {
                        html += `
                            <label class="flex items-center space-x-2 p-2 bg-white rounded shadow-sm hover:bg-gray-50 cursor-pointer student-item">
                                <input type="checkbox" name="student_ids[]" value="${student.id}" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 student-checkbox">
                                <span>${student.name}</span>
                            </label>
                        `;
                    });
                    studentsContainer.innerHTML = html;
                })
                .catch(error => {
                     console.error('Error fetching students:', error);
                     studentsContainer.innerHTML = '<p class="text-red-500 text-sm italic col-span-full">Error loading students. Check console.</p>';
                });
        }

        function filterStudents() {
            const search = document.getElementById('student-search').value.toLowerCase();
            const items = document.querySelectorAll('.student-item');
            
            items.forEach(item => {
                const text = item.querySelector('span').innerText.toLowerCase();
                if (text.includes(search)) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        }

        function selectAllStudents(checkbox) {
            const checkboxes = document.querySelectorAll('.student-checkbox');
            checkboxes.forEach(cb => {
                // Only check visible ones if filtering? Usually select all applies to all.
                // But UX wise, if I filter "John", select all should probably select all Johns.
                // For simplicity, select all visible checkboxes.
                if(cb.closest('.student-item').style.display !== 'none') {
                    cb.checked = checkbox.checked;
                }
            });
        }
    </script>
    @endpush
    </div>
</x-admin-layout>
