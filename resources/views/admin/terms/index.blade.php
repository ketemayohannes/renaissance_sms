<x-admin-layout>
    <x-slot name="header">Terms Management</x-slot>

    <div class="space-y-6">
        <!-- Action Buttons -->
        <div class="flex justify-end">
            <a href="{{ route('admin.terms.create') }}" class="btn-primary">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Add Term
            </a>
        </div>
        
        <!-- Breadcrumb -->
        <x-breadcrumb :items="[
            ['label' => 'Terms', 'url' => '#']
        ]" />
            <!-- Breadcrumb -->
            <x-breadcrumb :items="[
                ['label' => 'Terms', 'url' => '#']
            ]" />
            
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                    {{ session('success') }}
                </div>
            @endif

        <div class="card overflow-hidden">
            <div class="p-6">
                    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                        <table class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3">Academic Year</th>
                                    <th scope="col" class="px-6 py-3">Name</th>
                                    <th scope="col" class="px-6 py-3">Type</th>
                                    <th scope="col" class="px-6 py-3">Component Quarters</th>
                                    <th scope="col" class="px-6 py-3">Start Date</th>
                                    <th scope="col" class="px-6 py-3">End Date</th>
                                    <th scope="col" class="px-6 py-3">Grading Status</th>
                                    <th scope="col" class="px-6 py-3">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($terms as $term)
                                    <tr class="bg-white border-b hover:bg-gray-50 {{ $term->isSemester() ? 'bg-blue-50' : '' }}">
                                        <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                            {{ $term->academicYear->name }}
                                        </td>
                                        <td class="px-6 py-4">
                                            {{ $term->name }}
                                            @if($term->semester)
                                                <span class="text-xs text-gray-500">(Part of {{ $term->semester->name }})</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            @if($term->isQuarter())
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                    Quarter
                                                </span>
                                            @else
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                                    Semester
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            @if($term->isSemester() && $term->quarters->count() > 0)
                                                <div class="flex flex-col space-y-1">
                                                    @foreach($term->quarters as $quarter)
                                                        <span class="text-xs bg-gray-100 px-2 py-1 rounded">
                                                            {{ $quarter->name }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            {{ $term->start_date->format('M d, Y') }}
                                        </td>
                                        <td class="px-6 py-4">
                                            {{ $term->end_date->format('M d, Y') }}
                                        </td>
                                        <td class="px-6 py-4 text-xs space-y-1">
                                            <div class="flex items-center">
                                                <span class="w-2 h-2 rounded-full {{ $term->is_grading_open ? 'bg-green-500' : 'bg-red-500' }} mr-2"></span>
                                                Subject: {{ $term->is_grading_open ? 'Open' : 'Closed' }}
                                            </div>
                                            <div class="flex items-center">
                                                <span class="w-2 h-2 rounded-full {{ $term->is_master_grading_open ? 'bg-green-500' : 'bg-red-500' }} mr-2"></span>
                                                Master: {{ $term->is_master_grading_open ? 'Open' : 'Closed' }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <a href="{{ route('admin.terms.edit', $term) }}" class="font-medium text-blue-600 hover:underline mr-3">Edit</a>
                                            <form action="{{ route('admin.terms.destroy', $term) }}" method="POST" class="inline-block delete-form" data-confirm-message="Are you sure you want to delete this term?">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="font-medium text-red-600 hover:underline">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-4 text-center">No terms found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
    </div>
</x-admin-layout>
