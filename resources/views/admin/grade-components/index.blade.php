<x-admin-layout>
    <x-slot name="header">Grade Components</x-slot>

    <div class="space-y-6">
        <div class="flex justify-between items-center bg-white p-4 rounded-lg shadow-sm border border-slate-200">
            <div>
                <h2 class="text-lg font-bold text-slate-800">Assessment Breakdown</h2>
                <p class="text-sm text-slate-500">Manage how subjects are graded across terms.</p>
            </div>
            <a href="{{ route('admin.grade-components.create') }}" class="btn-primary">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add New Component
            </a>
        </div>

        <x-breadcrumb :items="[
            ['label' => 'Grade Components', 'url' => '#']
        ]" />

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @if($weightTotals->isNotEmpty())
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800">Weight Validation Warnings</h3>
                            <div class="mt-2 text-sm text-yellow-700">
                                <p class="mb-2">The following grade/subject combinations do not have weights totaling 100%:</p>
                                <ul class="list-disc list-inside space-y-1">
                                    @foreach($weightTotals as $total)
                                        <li>
                                            <strong>{{ $total['academic_year'] }}</strong> - {{ $total['term'] }} - 
                                            <strong>{{ $total['grade_level'] }}</strong> - {{ $total['subject'] }}: 
                                            <span class="font-semibold {{ $total['total'] > 100 ? 'text-red-600' : 'text-yellow-600' }}">
                                                {{ $total['total'] }}%
                                            </span>
                                            @if($total['total'] > 100)
                                                <span class="text-red-600">(Exceeds 100%!)</span>
                                            @elseif($total['total'] < 100)
                                                <span class="text-yellow-600">({{ 100 - $total['total'] }}% remaining)</span>
                                            @endif
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
                    <form action="{{ route('admin.grade-components.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
                        <div>
                            <label for="academic_year_id" class="block text-sm font-medium text-gray-700">Academic Year</label>
                            <select name="academic_year_id" id="academic_year_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">All Years</option>
                                @foreach($academicYears as $year)
                                    <option value="{{ $year->id }}" {{ request('academic_year_id') == $year->id ? 'selected' : '' }}>{{ $year->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="grade_level_id" class="block text-sm font-medium text-gray-700">Grade Level</label>
                            <select name="grade_level_id" id="grade_level_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">All Grades</option>
                                @foreach($gradeLevels as $grade)
                                    <option value="{{ $grade->id }}" {{ request('grade_level_id') == $grade->id ? 'selected' : '' }}>{{ $grade->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="subject_id" class="block text-sm font-medium text-gray-700">Subject</label>
                            <select name="subject_id" id="subject_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">All Subjects</option>
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="term_id" class="block text-sm font-medium text-gray-700">Term</label>
                            <select name="term_id" id="term_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">All Terms</option>
                                @foreach($terms as $term)
                                    <option value="{{ $term->id }}" {{ request('term_id') == $term->id ? 'selected' : '' }}>{{ $term->name }}</option>
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
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Academic Year / Term</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assessment Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Grade Levels</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subjects</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Weight</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($groupedComponents as $group)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $group['academic_year']->name }}</div>
                                            <div class="text-xs text-gray-500">{{ $group['term']->name ?? 'All Terms' }}</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $group['name'] }}</div>
                                            <div class="text-xs text-gray-500">{{ $group['count'] }} assignment(s)</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
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
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $group['weight'] }}%</div>
                                            <div class="text-xs text-gray-500">Max: {{ $group['max_score'] }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="{{ route('admin.grade-components.edit', $group['id']) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                                            <form action="{{ route('admin.grade-components.destroy', $group['id']) }}" method="POST" class="inline-block delete-form" data-confirm-message="Are you sure you want to delete this grade component group ({{ $group['count'] }} assignment(s))?">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">No grade components found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
</x-admin-layout>
