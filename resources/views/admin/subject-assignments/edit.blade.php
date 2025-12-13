<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Assign Subjects to') }} {{ $gradeLevel->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-6 flex justify-between items-center">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Academic Year: {{ $activeYear->name }}</h3>
                            <p class="text-sm text-gray-500">Select the subjects taught in {{ $gradeLevel->name }} for this year.</p>
                        </div>
                        <a href="{{ route('admin.subject-assignments.index') }}" class="text-gray-600 hover:text-gray-900">Back to List</a>
                    </div>

                    <form action="{{ route('admin.subject-assignments.update', $gradeLevel) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <div class="flex items-center mb-4">
                                <input id="select-all" type="checkbox" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                                <label for="select-all" class="ml-2 text-sm font-medium text-gray-900">Select All</label>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                            @foreach($subjects as $subject)
                                <div class="flex items-center p-3 border rounded hover:bg-gray-50">
                                    <input id="subject-{{ $subject->id }}" name="subjects[]" value="{{ $subject->id }}" type="checkbox" 
                                        class="subject-checkbox w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500"
                                        {{ in_array($subject->id, $assignedSubjectIds) ? 'checked' : '' }}>
                                    <label for="subject-{{ $subject->id }}" class="w-full ml-2 text-sm font-medium text-gray-900 cursor-pointer">
                                        {{ $subject->name }} <span class="text-gray-500 text-xs">({{ $subject->code }})</span>
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
</x-app-layout>
