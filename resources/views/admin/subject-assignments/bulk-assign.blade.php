<x-admin-layout>
    <x-slot name="header">Bulk Assign Subjects</x-slot>

    <div class="space-y-6">
        <!-- Breadcrumb -->
        <x-breadcrumb :items="[
            ['label' => 'Subject Assignments', 'url' => route('admin.subject-assignments.index')],
            ['label' => 'Bulk Assign', 'url' => '#']
        ]" />
        
        <div class="card overflow-hidden">
            <div class="p-6">
                    <div class="mb-6">
                        <p class="text-sm text-gray-600">Select multiple grade levels and the subjects you want to assign to them. This will overwrite any existing assignments for the selected grades.</p>
                    </div>

                    <form id="bulk-assign-form" action="{{ route('admin.subject-assignments.bulk-assign.store') }}" method="POST">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <!-- Column 1: Grade Levels -->
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2">1. Select Grade Levels</h3>
                                <div class="mb-2">
                                    <label class="inline-flex items-center">
                                        <input type="checkbox" id="select-all-grades" class="rounded text-blue-600">
                                        <span class="ml-2 font-semibold text-gray-700">Select All Grades</span>
                                    </label>
                                </div>
                                <div class="bg-gray-50 p-4 rounded-lg border h-96 overflow-y-auto">
                                    <div class="space-y-2">
                                        @foreach($gradeLevels as $grade)
                                            <div class="flex items-center">
                                                <input type="checkbox" name="grade_levels[]" value="{{ $grade->id }}" id="grade-{{ $grade->id }}" class="grade-checkbox rounded text-blue-600">
                                                <label for="grade-{{ $grade->id }}" class="ml-2 text-sm text-gray-700 cursor-pointer">
                                                    {{ $grade->name }} ({{ $grade->division->name }})
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                @error('grade_levels') <span class="text-red-500 text-sm block mt-1">{{ $message }}</span> @enderror
                            </div>

                            <!-- Column 2: Subjects -->
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2">2. Select Subjects</h3>
                                <div class="mb-2">
                                    <label class="inline-flex items-center">
                                        <input type="checkbox" id="select-all-subjects" class="rounded text-blue-600">
                                        <span class="ml-2 font-semibold text-gray-700">Select All Subjects</span>
                                    </label>
                                </div>
                                <div class="bg-gray-50 p-4 rounded-lg border h-96 overflow-y-auto">
                                    <div class="space-y-2">
                                        @foreach($subjects as $subject)
                                            <div class="flex items-center">
                                                <input type="checkbox" name="subjects[]" value="{{ $subject->id }}" id="subject-{{ $subject->id }}" class="subject-checkbox rounded text-blue-600">
                                                <label for="subject-{{ $subject->id }}" class="ml-2 text-sm text-gray-700 cursor-pointer">
                                                    {{ $subject->name }} ({{ $subject->code }})
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                @error('subjects') <span class="text-red-500 text-sm block mt-1">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="mt-8 flex justify-end">
                            <a href="{{ route('admin.subject-assignments.index') }}" class="mr-4 bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded">
                                Cancel
                            </a>
                            <button type="button" onclick="confirmBulkAssign()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow">
                                Assign Subjects to Selected Grades
                            </button>
                        </div>

                    </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Select All Grades
        document.getElementById('select-all-grades').addEventListener('change', function() {
            document.querySelectorAll('.grade-checkbox').forEach(cb => cb.checked = this.checked);
        });

        // Select All Subjects
        document.getElementById('select-all-subjects').addEventListener('change', function() {
            document.querySelectorAll('.subject-checkbox').forEach(cb => cb.checked = this.checked);
        });

        function confirmBulkAssign() {
            window.confirmUI({
                type: 'danger',
                title: 'Confirm Bulk Assignment',
                message: 'Are you sure you want to proceed? This action will overwrite any existing subject assignments for the selected grade levels. This cannot be undone.',
                buttonText: 'Yes, Overwrite & Assign',
                form: document.getElementById('bulk-assign-form')
            });
        }
    </script>
    @endpush
</x-admin-layout>
