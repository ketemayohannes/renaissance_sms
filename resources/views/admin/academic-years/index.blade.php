<x-admin-layout>
    <x-slot name="header">Academic Year Management</x-slot>

    <div class="space-y-6">
            <!-- Breadcrumb -->
            <x-breadcrumb :items="[
                ['label' => 'Academic Years', 'url' => '#']
            ]" />
            
        <div class="flex justify-end">
            <a href="{{ route('admin.academic-years.create') }}" class="btn-primary">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Add Academic Year
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
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Start Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">End Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($academicYears as $year)
                                <tr>
                                    <td class="px-6 py-4 font-medium">{{ $year->name }}</td>
                                    <td class="px-6 py-4">{{ $year->start_date->format('M d, Y') }}</td>
                                    <td class="px-6 py-4">{{ $year->end_date->format('M d, Y') }}</td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $year->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                            {{ $year->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm font-medium">
                                        <a href="{{ route('admin.academic-years.edit', $year) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                                        @if(!$year->is_active)
                                            <form action="{{ route('admin.academic-years.destroy', $year) }}" method="POST" class="inline-block delete-form" data-confirm-message="Are you sure you want to delete this academic year?">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">No academic years found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
    </div>
</x-admin-layout>
