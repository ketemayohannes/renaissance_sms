<x-admin-layout>
    <x-slot name="header">Edit Academic Year: {{ $academicYear->name }}</x-slot>

    <div class="space-y-6">
        <div class="max-w-2xl">
        <div class="card overflow-hidden">
            <div class="p-6">
                    <form action="{{ route('admin.academic-years.update', $academicYear) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-4">
                            <label for="name" class="block text-sm font-medium text-gray-700">Academic Year Name *</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $academicYear->name) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            @error('name')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                        </div>

                        <div class="mb-4">
                            <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date *</label>
                            <input type="date" name="start_date" id="start_date" value="{{ old('start_date', $academicYear->start_date->format('Y-m-d')) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            @error('start_date')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                        </div>

                        <div class="mb-4">
                            <label for="end_date" class="block text-sm font-medium text-gray-700">End Date *</label>
                            <input type="date" name="end_date" id="end_date" value="{{ old('end_date', $academicYear->end_date->format('Y-m-d')) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            @error('end_date')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                        </div>

                        <div class="mb-4">
                            <label class="flex items-center">
                                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $academicYear->is_active) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-gray-600">Set as Active Year</span>
                            </label>
                            <p class="text-xs text-gray-500 mt-1">Only one academic year can be active at a time</p>
                        </div>

                        <div class="flex justify-end">
                            <a href="{{ route('admin.academic-years.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded mr-2">Cancel</a>
                            <button type="submit" class="bg-gray-800 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">Update</button>
                        </div>
                    </form>
                </div>
        </div>
    </div>
</x-admin-layout>
