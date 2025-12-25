<x-admin-layout>
    <x-slot name="header">Edit Division: {{ $division->name }}</x-slot>

    <div class="space-y-6">
        <div class="max-w-2xl">
        <div class="card overflow-hidden">
            <div class="p-6">
                    <form action="{{ route('admin.divisions.update', $division) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-4">
                            <label for="name" class="block text-sm font-medium text-gray-700">Division Name *</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $division->name) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            @error('name')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                        </div>

                        <div class="mb-4">
                            <label for="code" class="block text-sm font-medium text-gray-700">Code *</label>
                            <input type="text" name="code" id="code" value="{{ old('code', $division->code) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            @error('code')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                        </div>

                        <div class="mb-4">
                            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea name="description" id="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $division->description) }}</textarea>
                        </div>

                        <div class="mb-4">
                            <label for="sort_order" class="block text-sm font-medium text-gray-700">Sort Order *</label>
                            <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', $division->sort_order) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        </div>

                        <div class="mb-4">
                            <label class="flex items-center">
                                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $division->is_active) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-gray-600">Active</span>
                            </label>
                        </div>

                        <div class="flex justify-end">
                            <a href="{{ route('admin.divisions.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded mr-2">Cancel</a>
                            <button type="submit" class="bg-gray-800 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">Update</button>
                        </div>
                    </form>
                </div>
        </div>
    </div>
</x-admin-layout>
