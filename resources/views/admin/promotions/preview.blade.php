<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Promotion Preview') }}: {{ $section->gradeLevel->name }}{{ $section->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Breadcrumb -->
            <x-breadcrumb :items="[
                ['label' => 'Promotions', 'url' => route('admin.promotions.index')],
                ['label' => 'Preview', 'url' => '#']
            ]" />
            
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-6 grid grid-cols-1 md:grid-cols-4 gap-4 bg-blue-50 p-4 rounded-lg">
                        <div><span class="text-gray-500">From:</span> <strong>{{ $section->gradeLevel->name }}</strong></div>
                        <div><span class="text-gray-500">To:</span> <strong>{{ $nextGradeLevel?->name ?? 'N/A' }}</strong></div>
                        <div><span class="text-gray-500">Rule:</span> <strong>{{ $promotionRule ? 'Min ' . $promotionRule->min_average . '%' : 'Default (50%)' }}</strong></div>
                        <div><span class="text-gray-500">Students:</span> <strong>{{ count($previewData) }}</strong></div>
                    </div>

                    <form action="{{ route('admin.promotions.execute') }}" method="POST">
                        @csrf
                        <input type="hidden" name="section_id" value="{{ $section->id }}">
                        <input type="hidden" name="next_academic_year_id" value="{{ $nextAcademicYear->id }}">

                        <table class="min-w-full divide-y divide-gray-200 border">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Yearly Average</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Decision</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Remarks</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($previewData as $data)
                                    <tr class="{{ $data['recommended'] === 'retained' ? 'bg-red-50' : '' }}">
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $data['student']->full_name }}</td>
                                        <td class="px-6 py-4 text-center font-bold {{ $data['passesAverage'] ? 'text-green-600' : 'text-red-600' }}">
                                            {{ \App\Helpers\NumberFormatter::format($data['average']) }}%
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            @if($data['passesAverage'])
                                                <span class="px-2 py-1 text-xs font-semibold bg-green-100 text-green-800 rounded-full">Passes</span>
                                            @else
                                                <span class="px-2 py-1 text-xs font-semibold bg-red-100 text-red-800 rounded-full">Below Min</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <select name="decisions[{{ $data['student']->id }}]" class="border-gray-300 rounded-md shadow-sm text-sm">
                                                <option value="promoted" {{ $data['recommended'] === 'promoted' ? 'selected' : '' }}>Promote</option>
                                                <option value="retained" {{ $data['recommended'] === 'retained' ? 'selected' : '' }}>Retain</option>
                                                <option value="conditionally_promoted">Conditional</option>
                                            </select>
                                        </td>
                                        <td class="px-6 py-4">
                                            <input type="text" name="remarks[{{ $data['student']->id }}]" placeholder="Optional" class="border-gray-300 rounded-md shadow-sm text-sm w-full">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <div class="mt-8 flex justify-end gap-4">
                            <a href="{{ route('admin.promotions.process') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-6 rounded transition">
                                Cancel
                            </a>
                            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-10 rounded shadow-lg transition">
                                Confirm & Execute Promotions
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
