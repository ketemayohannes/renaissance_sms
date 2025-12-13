<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Assessment Templates') }}
            </h2>
            <a href="{{ route('admin.assessment-templates.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Add New Template
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Breadcrumb -->
            <x-breadcrumb :items="[
                ['label' => 'Assessment Templates', 'url' => '#']
            ]" />

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @if($weightWarnings->isNotEmpty())
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                    <div class="flex">
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800">Weight Validation Warnings</h3>
                            <div class="mt-2 text-sm text-yellow-700">
                                <p class="mb-2">The following grade/subject combinations do not have weights totaling 100%:</p>
                                <ul class="list-disc list-inside space-y-1">
                                    @foreach($weightWarnings as $total)
                                        <li>
                                            <strong>{{ $total['academic_year'] }}</strong> - {{ $total['term'] }} - 
                                            <strong>{{ $total['grade_level'] }}</strong> - {{ $total['subject'] }}: 
                                            <span class="font-semibold {{ $total['total'] > 100 ? 'text-red-600' : 'text-yellow-600' }}">
                                                {{ $total['total'] }}%
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Filters -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <form action="{{ route('admin.assessment-templates.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                        <div>
                            <label for="academic_year_id" class="block text-sm font-medium text-gray-700">Academic Year</label>
                            <select name="academic_year_id" id="academic_year_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                <option value="">All Years</option>
                                @foreach($academicYears as $year)
                                    <option value="{{ $year->id }}">{{ $year->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="term_id" class="block text-sm font-medium text-gray-700">Term</label>
                            <select name="term_id" id="term_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                <option value="">All Terms</option>
                                @foreach($terms as $term)
                                    <option value="{{ $term->id }}">{{ $term->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <button type="submit" class="w-full bg-gray-800 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Academic Year / Term</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Assessment Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Grade Levels</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subjects</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Weight</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($groupedTemplates as $group)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $group['academic_year']->name }}</div>
                                            <div class="text-xs text-gray-500">{{ $group['term']->name ?? 'All Terms' }}</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $group['name'] }}</div>
                                            <div class="text-xs text-gray-500">{{ $group['assignment_count'] }} assignment(s)</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="px-2 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                                {{ $group['assessment_type']->name }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-gray-900">
                                                {{ $group['grade_levels']->pluck('name')->join(', ') }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-gray-900">
                                                {{ $group['subjects']->pluck('name')->join(', ') }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $group['weight'] }}%</div>
                                        </td>
                                        <td class="px-6 py-4 text-sm font-medium">
                                            <a href="{{ route('admin.assessment-templates.edit', $group['id']) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                                            <form action="{{ route('admin.assessment-templates.destroy', $group['id']) }}\" method="POST" class="inline-block">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">No assessment templates found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
