<x-admin-layout>
    <x-slot name="header">Create Section</x-slot>

    <div class="space-y-6">
        <div class="max-w-2xl">
        <div class="card overflow-hidden">
            <div class="p-6">
                    <form action="{{ route('admin.sections.store') }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label for="academic_year_id" class="block text-sm font-medium text-gray-700">Academic Year *</label>
                            <select name="academic_year_id" id="academic_year_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                <option value="">Select Academic Year</option>
                                @foreach($academicYears as $year)
                                    <option value="{{ $year->id }}" {{ old('academic_year_id') == $year->id ? 'selected' : '' }}>
                                        {{ $year->name }} {{ $year->is_active ? '(Active)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('academic_year_id')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                        </div>

                        <div class="mb-4">
                            <label for="grade_level_id" class="block text-sm font-medium text-gray-700">Grade Level *</label>
                            <select name="grade_level_id" id="grade_level_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                <option value="">Select Grade Level</option>
                                @foreach($gradeLevels as $gradeLevel)
                                    <option value="{{ $gradeLevel->id }}" {{ old('grade_level_id') == $gradeLevel->id ? 'selected' : '' }}>
                                        {{ $gradeLevel->name }} ({{ $gradeLevel->division->name }})
                                    </option>
                                @endforeach
                            </select>
                            @error('grade_level_id')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                        </div>

                        <div class="mb-4">
                            <label for="name" class="block text-sm font-medium text-gray-700">Section Name *</label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" placeholder="e.g., Section A, Section B" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            @error('name')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                        </div>

                        <div class="mb-4">
                            <label for="capacity" class="block text-sm font-medium text-gray-700">Capacity *</label>
                            <input type="number" name="capacity" id="capacity" value="{{ old('capacity', 30) }}" min="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            @error('capacity')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                        </div>

                        <div class="mb-4">
                            <label for="homeroom_teacher_id" class="block text-sm font-medium text-gray-700">Homeroom Teacher</label>
                            <select name="homeroom_teacher_id" id="homeroom_teacher_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Select Teacher (Optional)</option>
                                @foreach($teachers as $teacher)
                                    <option value="{{ $teacher->id }}" {{ old('homeroom_teacher_id') == $teacher->id ? 'selected' : '' }}>
                                        {{ $teacher->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex justify-end">
                            <a href="{{ route('admin.sections.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded mr-2">Cancel</a>
                            <button type="submit" class="bg-gray-800 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">Create</button>
                        </div>
                    </form>
                </div>
        </div>
    </div>
</x-admin-layout>
