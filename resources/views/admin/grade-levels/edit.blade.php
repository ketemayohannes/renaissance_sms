<x-admin-layout>
    <x-slot name="header">Edit Grade Level: {{ $gradeLevel->name }}</x-slot>

    <div class="space-y-6">
        <x-breadcrumb :items="[
            ['label' => 'Grade Levels', 'url' => route('admin.grade-levels.index')],
            ['label' => 'Edit', 'url' => '#']
        ]" />
        <div class="max-w-2xl">
        <div class="card overflow-hidden">
            <div class="p-6">
                    <form action="{{ route('admin.grade-levels.update', $gradeLevel) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-4">
                            <label for="division_id" class="block text-sm font-medium text-gray-700">Division *</label>
                            <select name="division_id" id="division_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                @foreach($divisions as $division)
                                    <option value="{{ $division->id }}" {{ old('division_id', $gradeLevel->division_id) == $division->id ? 'selected' : '' }}>
                                        {{ $division->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('division_id')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                        </div>

                        <div class="mb-4">
                            <label for="name" class="block text-sm font-medium text-gray-700">Grade Level Name *</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $gradeLevel->name) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            @error('name')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                        </div>

                        <div class="mb-4">
                            <label for="code" class="block text-sm font-medium text-gray-700">Code *</label>
                            <input type="text" name="code" id="code" value="{{ old('code', $gradeLevel->code) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            @error('code')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                        </div>

                        <div class="mb-4">
                            <label for="sort_order" class="block text-sm font-medium text-gray-700">Sort Order *</label>
                            <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', $gradeLevel->sort_order) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        </div>

                        <div class="mb-4">
                            <label class="flex items-center">
                                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $gradeLevel->is_active) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-gray-600">Active</span>
                            </label>
                        </div>

                        <div class="flex justify-end">
                            <a href="{{ route('admin.grade-levels.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded mr-2">Cancel</a>
                            <button type="submit" class="bg-gray-800 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">Update</button>
                        </div>
                    </form>
                </div>
        </div>
    </div>
</x-admin-layout>
