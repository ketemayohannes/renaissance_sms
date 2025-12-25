<x-admin-layout>
    <x-slot name="header">Assign Elective Subjects: {{ $student->full_name }}</x-slot>

    <div class="space-y-6">
        <x-breadcrumb :items="[
            ['label' => 'Students', 'url' => route('admin.students.index')],
            ['label' => $student->first_name, 'url' => route('admin.students.show', $student)],
            ['label' => 'Assign Electives', 'url' => '#']
        ]" />

        <div class="card overflow-hidden">
            <div class="p-6">
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900">Current Enrollment</h3>
                        <p class="text-sm text-gray-600">
                            <strong>Academic Year:</strong> {{ $currentEnrollment->academicYear->name }} <br>
                            <strong>Grade & Section:</strong> {{ $currentEnrollment->section->gradeLevel->name }} - {{ $currentEnrollment->section->name }}
                        </p>
                    </div>

                    @if($availableElectives->isEmpty())
                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-700">
                                        No elective subjects found for this grade level. Please ensure subjects are assigned to the grade level and marked as 'Elective'.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4">
                            <a href="{{ route('admin.students.show', $student) }}" class="text-indigo-600 hover:text-indigo-900">Back to Student Profile</a>
                        </div>
                    @else
                        <form action="{{ route('admin.students.assign-electives.store', $student) }}" method="POST">
                            @csrf
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                                @foreach($availableElectives as $subject)
                                    <div class="relative flex items-start p-4 border rounded-lg hover:bg-gray-50 transition-colors">
                                        <div class="flex items-center h-5">
                                            <input id="subject_{{ $subject->id }}" 
                                                   name="electives[]" 
                                                   value="{{ $subject->id }}" 
                                                   type="checkbox" 
                                                   class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded"
                                                   {{ in_array($subject->id, $assignedElectiveIds) ? 'checked' : '' }}>
                                        </div>
                                        <div class="ml-3 text-sm">
                                            <label for="subject_{{ $subject->id }}" class="font-medium text-gray-700">{{ $subject->name }}</label>
                                            <p class="text-gray-500">{{ $subject->code }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="flex items-center justify-end mt-4">
                                <a href="{{ route('admin.students.show', $student) }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded mr-2">
                                    Cancel
                                </a>
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                    Save Assignments
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
        </div>
</x-admin-layout>
