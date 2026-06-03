<x-admin-layout>
    <x-slot name="header">Promotion Preview: {{ $section->gradeLevel->name }}{{ $section->name }}</x-slot>

    <div class="space-y-6">
        <x-breadcrumb :items="[
            ['label' => 'Promotions', 'url' => route('admin.promotions.index')],
            ['label' => 'Preview', 'url' => '#']
        ]" />

        <div class="card overflow-hidden">
            <div class="p-6">
                    <div class="mb-6 flex justify-between items-center bg-blue-50 p-4 rounded-lg">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 flex-1">
                            <div><span class="text-gray-500">From:</span> <strong>{{ $section->gradeLevel->name }}</strong></div>
                            <div><span class="text-gray-500">To:</span> <strong>{{ $nextGradeLevel?->name ?? 'N/A' }}</strong></div>
                            <div><span class="text-gray-500">Rule:</span> <strong>{{ $promotionRule ? 'Min ' . $promotionRule->min_average . '%' : 'Default (50%)' }}</strong></div>
                            <div><span class="text-gray-500">Students:</span> <strong>{{ count($previewData) }}</strong></div>
                        </div>
                        <a href="{{ route('admin.academic-reports.show') }}?academic_year_id={{$academicYear->id}}&term_id=yearly&section_id={{$section->id}}&report_type=result_analysis" target="_blank" class="text-sm font-bold text-indigo-600 hover:text-indigo-800 flex items-center bg-white px-4 py-2 rounded-lg shadow-sm border border-indigo-100 transition">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                            View Full Yearly Analysis
                        </a>
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
                                    @php
                                        $rowClass = '';
                                        if ($data['recommended'] === 'retained') {
                                            $rowClass = 'bg-red-50/50';
                                        } elseif ($data['recommended'] === 're_exam') {
                                            $rowClass = 'bg-amber-50/50';
                                        } elseif ($data['recommended'] === 'graduated') {
                                            $rowClass = 'bg-emerald-50/30';
                                        }
                                    @endphp
                                    <tr class="{{ $rowClass }}">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="font-bold text-slate-700">{{ $data['student']->full_name }}</div>
                                            @if($data['failedSubjects'] > 0)
                                                <span class="block text-[10px] text-slate-400 font-bold uppercase mt-0.5">
                                                    {{ $data['failedSubjects'] }} {{ Str::plural('Fail', $data['failedSubjects']) }}
                                                    ({{ $data['failedMajorCount'] }} Major, {{ $data['failedNonMajorCount'] }} Non-major)
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-center font-bold {{ $data['passesAverage'] ? 'text-green-600' : 'text-red-600' }}">
                                            {{ \App\Helpers\NumberFormatter::format($data['average']) }}%
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            @if($data['recommended'] === 'promoted')
                                                <span class="px-2.5 py-1 text-xs font-bold bg-green-100 text-green-800 rounded-full uppercase tracking-wider">Promoted</span>
                                            @elseif($data['recommended'] === 'graduated')
                                                <span class="px-2.5 py-1 text-xs font-bold bg-emerald-100 text-emerald-800 rounded-full uppercase tracking-wider">Graduate</span>
                                            @elseif($data['recommended'] === 're_exam')
                                                <span class="px-2.5 py-1 text-xs font-bold bg-amber-100 text-amber-800 rounded-full uppercase tracking-wider">Re-exam</span>
                                            @else
                                                <span class="px-2.5 py-1 text-xs font-bold bg-red-100 text-red-800 rounded-full uppercase tracking-wider">Retained</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <select name="decisions[{{ $data['student']->id }}]" class="border-slate-300 rounded-lg shadow-sm text-sm">
                                                <option value="promoted" {{ $data['recommended'] === 'promoted' ? 'selected' : '' }}>Promote</option>
                                                <option value="retained" {{ $data['recommended'] === 'retained' ? 'selected' : '' }}>Retain</option>
                                                <option value="re_exam" {{ $data['recommended'] === 're_exam' ? 'selected' : '' }}>Re-exam</option>
                                                <option value="graduated" {{ $data['recommended'] === 'graduated' ? 'selected' : '' }}>Graduate</option>
                                                <option value="conditionally_promoted" {{ $data['recommended'] === 'conditionally_promoted' ? 'selected' : '' }}>Conditional</option>
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
</x-admin-layout>
