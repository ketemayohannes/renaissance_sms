<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Reorder Subjects') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Breadcrumb -->
            <x-breadcrumb :items="[
                ['label' => 'Subjects', 'url' => route('admin.subjects.index')],
                ['label' => 'Reorder', 'url' => '#']
            ]" />
            
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="mb-8 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                        <label for="grade_level_selector" class="block text-sm font-semibold text-blue-900 mb-2">Select Grade Level to Order Subjects</label>
                        <form id="filter-form" action="{{ route('admin.subjects.reorder') }}" method="GET" class="flex items-center space-x-2">
                            <select name="grade_level_id" id="grade_level_selector" class="block w-full max-w-xs rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" onchange="this.form.submit()">
                                <option value="">-- Choose a Grade Level --</option>
                                @foreach($gradeLevels as $grade)
                                    <option value="{{ $grade->id }}" {{ $selectedGradeId == $grade->id ? 'selected' : '' }}>
                                        {{ $grade->name }}
                                    </option>
                                @endforeach
                            </select>
                            <noscript>
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-1 px-4 rounded text-sm">Filter</button>
                            </noscript>
                        </form>
                    </div>

                    @if($selectedGradeId)
                        <div class="mb-4">
                            <p class="text-sm text-gray-600">Set the display order for subjects in <strong>{{ $gradeLevels->find($selectedGradeId)->name }}</strong>. Lower numbers will appear first in the Master Sheet.</p>
                        </div>

                        <form action="{{ route('admin.subjects.update-order') }}" method="POST">
                            @csrf
                            <input type="hidden" name="grade_level_id" value="{{ $selectedGradeId }}">
                            
                            <div class="overflow-x-auto border rounded-lg">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subject Name</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase w-32">Sort Order</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @forelse($subjects as $subject)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    {{ $subject->name }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ $subject->code }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <input type="number" name="orders[{{ $subject->id }}]" value="{{ $subject->pivot->sort_order }}" 
                                                        class="block w-24 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                        min="0">
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="px-6 py-10 text-center text-gray-500 italic">
                                                    No subjects assigned to this grade level yet.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            @if($subjects->isNotEmpty())
                                <div class="mt-6 flex justify-end">
                                    <a href="{{ route('admin.subjects.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded mr-2">
                                        Cancel
                                    </a>
                                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded shadow-sm">
                                        Save New Order for this Grade
                                    </button>
                                </div>
                            @endif
                        </form>
                    @else
                        <div class="py-12 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No Grade Level Selected</h3>
                            <p class="mt-1 text-sm text-gray-500">Please select a grade level from the dropdown above to start reordering subjects.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
