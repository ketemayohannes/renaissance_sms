<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Bulk Assign Subjects') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Breadcrumb -->
            <x-breadcrumb :items="[
                ['label' => 'Subject Assignments', 'url' => route('admin.subject-assignments.index')],
                ['label' => 'Bulk Assign', 'url' => '#']
            ]" />
            
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg" x-data="{ confirmModal: false }">
                <div class="p-6 text-gray-900">
                    <div class="mb-6">
                        <p class="text-sm text-gray-600">Select multiple grade levels and the subjects you want to assign to them. This will overwrite any existing assignments for the selected grades.</p>
                    </div>

                    <form action="{{ route('admin.subject-assignments.bulk-assign.store') }}" method="POST">
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
                            <button type="button" @click="confirmModal = true" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow">
                                Assign Subjects to Selected Grades
                            </button>
                        </div>

                        <!-- Confirmation Modal -->
                        <div x-show="confirmModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
                            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                                <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                                </div>

                                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                        <div class="sm:flex sm:items-start">
                                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                                <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                </svg>
                                            </div>
                                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                                    Confirm Bulk Assignment
                                                </h3>
                                                <div class="mt-2">
                                                    <p class="text-sm text-gray-500">
                                                        Are you sure you want to proceed? This action will <strong>overwrite</strong> any existing subject assignments for the selected grade levels. This cannot be undone.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                                            Yes, Overwrite & Assign
                                        </button>
                                        <button type="button" @click="confirmModal = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                            Cancel
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
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
    </script>
    @endpush
</x-app-layout>
