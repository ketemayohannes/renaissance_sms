<x-admin-layout>
    <x-slot name="header">Student Promotion Management</x-slot>

    <div class="space-y-6">
        <!-- Action Buttons -->
        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.promotions.history') }}" class="btn-secondary">
                View History
            </a>
            <a href="{{ route('admin.promotions.process') }}" class="btn-primary">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                Process Promotions
            </a>
        </div>
            <!-- Breadcrumb -->
            <x-breadcrumb :items="[
                ['label' => 'Promotions', 'url' => '#']
            ]" />
            
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

        <div class="card overflow-hidden mb-6">
            <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Add/Edit Promotion Rule</h3>
                    <form action="{{ route('admin.promotions.store-rule') }}" method="POST" class="grid grid-cols-1 md:grid-cols-6 gap-4">
                        @csrf
                        <div>
                            <label for="from_grade_level_id" class="block text-sm font-medium text-gray-700">From Grade</label>
                            <select name="from_grade_level_id" id="from_grade_level_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                                <option value="">Select</option>
                                @foreach($gradeLevels as $gl)
                                    <option value="{{ $gl->id }}">{{ $gl->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="to_grade_level_id" class="block text-sm font-medium text-gray-700">To Grade</label>
                            <select name="to_grade_level_id" id="to_grade_level_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                                <option value="">Select</option>
                                @foreach($gradeLevels as $gl)
                                    <option value="{{ $gl->id }}">{{ $gl->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="min_average" class="block text-sm font-medium text-gray-700">Min Average (%)</label>
                            <input type="number" step="0.01" name="min_average" id="min_average" value="50" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                        </div>
                        <div>
                            <label for="max_failed_subjects" class="block text-sm font-medium text-gray-700">Max Failed Subjects</label>
                            <input type="number" name="max_failed_subjects" id="max_failed_subjects" value="0" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                            <input type="text" name="description" id="description" placeholder="Optional" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition w-full">
                                Save Rule
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        <div class="card overflow-hidden">
            <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Existing Promotion Rules ({{ $academicYear->name }})</h3>
                    @if($promotionRules->isEmpty())
                        <p class="text-gray-500 italic">No promotion rules configured for this academic year hideously.</p>
                    @else
                        <table class="min-w-full divide-y divide-gray-200 border">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">From Grade</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">To Grade</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Min Average</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Max Failed</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($promotionRules as $rule)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $rule->fromGradeLevel->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $rule->toGradeLevel->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center font-bold">{{ $rule->min_average }}%</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">{{ $rule->max_failed_subjects }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-500">{{ $rule->description ?? '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <form action="{{ route('admin.promotions.delete-rule', $rule) }}" method="POST" class="inline delete-form" data-confirm-message="Delete this rule?">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
    </div>
</x-admin-layout>
