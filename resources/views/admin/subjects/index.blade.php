<x-admin-layout>
    <x-slot name="header">Subject Management</x-slot>

    <div class="space-y-6">
            <!-- Breadcrumb -->
            <x-breadcrumb :items="[
                ['label' => 'Subjects', 'url' => '#']
            ]" />
            
        <!-- Action Buttons -->
        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.subjects.reorder') }}" class="btn-secondary">
                Reorder
            </a>
            <a href="{{ route('admin.subjects.create') }}" class="btn-primary">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Add Subject
            </a>
        </div>

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                    {{ session('success') }}
                </div>
            @endif

        <div class="card overflow-hidden">
            <div class="p-6">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase w-20">Order</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($subjects as $subject)
                                <tr>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $subject->sort_order }}</td>
                                    <td class="px-6 py-4">{{ $subject->name }}</td>
                                    <td class="px-6 py-4">{{ $subject->code }}</td>
                                    <td class="px-6 py-4">{{ $subject->description ?? '-' }}</td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $subject->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $subject->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm font-medium">
                                        <a href="{{ route('admin.subjects.edit', $subject) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                                        <form action="{{ route('admin.subjects.destroy', $subject) }}" method="POST" class="inline-block delete-form" data-confirm-message="Are you sure you want to delete this subject?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">No subjects found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
    </div>
</x-admin-layout>
