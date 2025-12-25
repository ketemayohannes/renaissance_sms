<x-admin-layout>
    <x-slot name="header">Section Management</x-slot>

    <div class="space-y-6">
            <!-- Breadcrumb -->
            <x-breadcrumb :items="[
                ['label' => 'Sections', 'url' => '#']
            ]" />
            
        <!-- Action Buttons -->
        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.sections.bulk-create') }}" class="btn-secondary">
                Bulk Create
            </a>
            <a href="{{ route('admin.sections.import') }}" class="btn-secondary">
                Import
            </a>
            <a href="{{ route('admin.sections.create') }}" class="btn-primary">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Add Section
            </a>
        </div>

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                    {{ session('error') }}
                </div>
            @endif

        <div class="card overflow-hidden">
            <div class="p-6">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Section Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Grade Level</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Academic Year</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Homeroom Teacher</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Capacity</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($sections as $section)
                                <tr>
                                    <td class="px-6 py-4 font-medium">{{ $section->name }}</td>
                                    <td class="px-6 py-4">{{ $section->gradeLevel->name }} ({{ $section->gradeLevel->division->name }})</td>
                                    <td class="px-6 py-4">{{ $section->academicYear->name }}</td>
                                    <td class="px-6 py-4">{{ $section->homeroomTeacher->name ?? '-' }}</td>
                                    <td class="px-6 py-4">{{ $section->capacity }}</td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $section->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $section->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm font-medium">
                                        <a href="{{ route('admin.sections.edit', $section) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                                        <form action="{{ route('admin.sections.destroy', $section) }}" method="POST" class="inline-block delete-form" data-confirm-message="Are you sure you want to delete this section?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">No sections found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
    </div>
</x-admin-layout>
