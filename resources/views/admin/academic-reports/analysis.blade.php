<x-admin-layout>
    <x-slot name="header">Result Analysis: {{ $section->name }} - {{ $term->name }}</x-slot>

    <div class="space-y-6">
        <div class="flex justify-between items-center bg-white p-4 rounded-lg shadow-sm border border-slate-200 no-print">
            <div>
                <h2 class="text-lg font-bold text-slate-800">Result Analysis Report</h2>
                <p class="text-sm text-slate-500">{{ $section->gradeLevel->name }} - {{ $section->name }} | {{ $term->name }} | {{ $academicYear->name }}</p>
            </div>
            <button onclick="window.print()" class="btn-primary flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Print Report
            </button>
        </div>

        <x-breadcrumb :items="[
            ['label' => 'Academic Reports', 'url' => route('admin.academic-reports.index')],
            ['label' => 'Result Analysis', 'url' => '#']
        ]" />



            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                <div class="bg-white rounded-lg shadow p-5 border-l-4 border-indigo-500">
                    <p class="text-xs font-bold text-indigo-600 uppercase tracking-wider mb-1">Class Average</p>
                    <p class="text-2xl font-bold text-gray-800">{{ number_format($classStats->class_average, 2) }}%</p>
                </div>
                <div class="bg-white rounded-lg shadow p-5 border-l-4 border-green-500">
                    <p class="text-xs font-bold text-green-600 uppercase tracking-wider mb-1">Pass Rate</p>
                    <p class="text-2xl font-bold text-gray-800">
                        {{ $classStats->total_students > 0 ? number_format(($classStats->total_passed / $classStats->total_students) * 100, 1) : 0 }}%
                    </p>
                    <p class="text-xs text-gray-500 mt-1">{{ $classStats->total_passed }} of {{ $classStats->total_students }} passed</p>
                </div>
                <div class="bg-white rounded-lg shadow p-5 border-l-4 border-yellow-500">
                    <p class="text-xs font-bold text-yellow-600 uppercase tracking-wider mb-1">Highest Average</p>
                    <p class="text-2xl font-bold text-gray-800">{{ number_format($classStats->highest_avg, 2) }}%</p>
                </div>
                <div class="bg-white rounded-lg shadow p-5 border-l-4 border-blue-500">
                    <p class="text-xs font-bold text-blue-600 uppercase tracking-wider mb-1">Enrolled Subjects</p>
                    <p class="text-2xl font-bold text-gray-800">{{ count($subjects) }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Subject Performance Table -->
                <div class="lg:col-span-2">
                    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                        <div class="px-4 py-5 sm:px-6 bg-gray-50 border-b border-gray-200">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 font-bold">Subject Performance Summary</h3>
                        </div>
                        <div class="p-0 overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Appeared</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Pass %</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">High</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Low</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Avg</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($subjects as $subject)
                                        @if(isset($subjectStats[$subject->id]))
                                            @php $stats = $subjectStats[$subject->id]; @endphp
                                            <tr class="hover:bg-gray-50 transition">
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $subject->name }}</td>
                                                <td class="px-4 py-4 whitespace-nowrap text-sm text-center text-gray-500">{{ $stats->appeared }}</td>
                                                <td class="px-4 py-4 whitespace-nowrap text-center">
                                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $stats->pass_rate >= 75 ? 'bg-green-100 text-green-800' : ($stats->pass_rate >= 50 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                                        {{ number_format($stats->pass_rate, 1) }}%
                                                    </span>
                                                </td>
                                                <td class="px-4 py-4 whitespace-nowrap text-sm text-center text-indigo-600 font-bold">{{ number_format($stats->highest, 1) }}</td>
                                                <td class="px-4 py-4 whitespace-nowrap text-sm text-center text-red-600">{{ number_format($stats->lowest, 1) }}</td>
                                                <td class="px-4 py-4 whitespace-nowrap text-sm text-center font-bold">{{ number_format($stats->average, 1) }}</td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Top Performers -->
                <div>
                    <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-8">
                        <div class="px-4 py-5 sm:px-6 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 font-bold">Top High Performers</h3>
                            <svg class="w-6 h-6 text-yellow-500" fill="currentColor" viewBox="0 0 20 20"><path d="M5 4a2 2 0 012-2h6a2 2 0 012 2v14l-5-2.5L5 18V4z"></path></svg>
                        </div>
                        <div class="p-0">
                            <ul class="divide-y divide-gray-200">
                                @forelse($topPerformers as $index => $report)
                                    <li class="p-4 flex items-center justify-between hover:bg-gray-50 transition">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-sm">
                                                {{ $index + 1 }}
                                            </div>
                                            <div>
                                                <p class="text-sm font-bold text-gray-900">{{ $report['student']->full_name }}</p>
                                                <p class="text-xs text-gray-500">{{ $report['student']->student_id }}</p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-sm font-bold text-indigo-600">
                                                {{ number_format($term->type === 'yearly' ? ($report['rows']['avg']['average'] ?? 0) : ($report['average'] ?? 0), 2) }}%
                                            </p>
                                            <p class="text-xs text-gray-400">Rank: {{ $term->type === 'yearly' ? ($report['rows']['avg']['rank'] ?? '-') : ($report['rank'] ?? '-') }}</p>
                                        </div>
                                    </li>
                                @empty
                                    <li class="p-4 text-center text-gray-500 text-sm">No data available</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>

                    <!-- Gender Performance -->
                    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                        <div class="px-4 py-5 sm:px-6 bg-gray-100 border-b border-gray-200">
                            <h3 class="text-lg leading-6 font-medium text-gray-800 font-bold">Average by Gender</h3>
                        </div>
                        <div class="p-6">
                            @php
                                $genderData = collect($reports)->groupBy(fn($r) => $r['student']->gender)->map(function($group) use ($term) {
                                    return $group->avg(fn($r) => $term->type === 'yearly' ? ($r['rows']['avg']['average'] ?? 0) : ($r['average'] ?? 0));
                                });
                            @endphp
                            <div class="space-y-6">
                                @foreach(['male' => 'Indigo', 'female' => 'Pink'] as $gender => $color)
                                    @if(isset($genderData[$gender]))
                                        <div>
                                            <div class="flex justify-between items-center mb-1">
                                                <span class="text-sm font-bold text-gray-700 uppercase">{{ ucfirst($gender) }}s</span>
                                                <span class="text-sm font-bold text-gray-900">{{ number_format($genderData[$gender], 2) }}%</span>
                                            </div>
                                            <div class="w-full bg-gray-200 rounded-full h-2.5">
                                                <div class="bg-{{ strtolower($color) }}-600 h-2.5 rounded-full" style="width: {{ $genderData[$gender] }}%"></div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
</x-admin-layout>
