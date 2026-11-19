<x-admin-layout>
    <x-slot name="header">Assign Subjects to {{ $gradeLevel->name }} ({{ $academicYear->name }})</x-slot>

    <div class="space-y-6">
        <x-breadcrumb :items="[
            ['label' => 'Subject Assignments', 'url' => route('admin.subject-assignments.index', ['academic_year_id' => $academicYear->id])],
            ['label' => 'Manage Subjects', 'url' => '#']
        ]" />

        <div class="card overflow-hidden">
            <div class="p-6 text-gray-900">
                    <div class="mb-6 flex justify-between items-center">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">{{ $gradeLevel->name }} Subjects &mdash; {{ $academicYear->name }}</h3>
                            <p class="text-sm text-gray-500">Select the subjects taught in {{ $gradeLevel->name }} in {{ $academicYear->name }}. Tick <strong>Elective</strong> for subjects only the students who chose them take. Other academic years are not affected.</p>
                        </div>
                        <a href="{{ route('admin.subject-assignments.index', ['academic_year_id' => $academicYear->id]) }}" class="text-gray-600 hover:text-gray-900">Back to List</a>
                    </div>

                    <form action="{{ route('admin.subject-assignments.update', $gradeLevel) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="academic_year_id" value="{{ $academicYear->id }}">

                        <div class="mb-4">
                            <div class="flex items-center mb-4">
                                <input id="select-all" type="checkbox" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                                <label for="select-all" class="ml-2 text-sm font-medium text-gray-900">Select All</label>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                            @foreach($subjects as $subject)
                                <div class="flex items-center p-3 border rounded hover:bg-gray-50 gap-2">
                                    <input id="subject-{{ $subject->id }}" name="subjects[]" value="{{ $subject->id }}" type="checkbox" 
                                        class="subject-checkbox w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500"
                                        {{ in_array($subject->id, $assignedSubjectIds) ? 'checked' : '' }}>
                                    <label for="subject-{{ $subject->id }}" class="flex-1 ml-2 text-sm font-medium text-gray-900 cursor-pointer">
                                        {{ $subject->name }} <span class="text-gray-500 text-xs">({{ $subject->code }})</span>
                                    </label>
                                    <label class="inline-flex items-center gap-1 text-xs text-gray-600 cursor-pointer" title="Elective: only students who chose it take it. Untick for a subject every student in the grade takes.">
                                        <input name="electives[]" value="{{ $subject->id }}" type="checkbox"
                                            class="w-3.5 h-3.5 text-amber-600 bg-gray-100 border-gray-300 rounded focus:ring-amber-500"
                                            {{ in_array($subject->id, $electiveSubjectIds) ? 'checked' : '' }}>
                                        Elective
                                    </label>
                                </div>
                            @endforeach
                        </div>

                        <div class="flex items-center justify-end">
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                                Save Assignments
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.getElementById('select-all').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.subject-checkbox');
            checkboxes.forEach(cb => cb.checked = this.checked);
        });
    </script>
    @endpush
    </div>
</x-admin-layout>
