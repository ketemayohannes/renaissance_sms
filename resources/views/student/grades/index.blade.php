<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My Grades') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Quick Info -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900 flex flex-col md:flex-row justify-between items-center gap-4">
                    <div>
                        <h3 class="text-lg font-bold">{{ $student->full_name }}</h3>
                        <p class="text-sm text-gray-500">
                            {{ $enrollment->section->gradeLevel->name }} - {{ $enrollment->section->name }}
                        </p>
                    </div>
                    
                    <!-- Filter Form -->
                    <form action="{{ route('student.grades.index') }}" method="GET" class="flex items-center gap-2">
                        <select name="period" onchange="this.form.submit()" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                            <option value="all" {{ $selectedPeriod == 'all' ? 'selected' : '' }}>All Records</option>
                            <option disabled>── Quarters ──</option>
                            @foreach($quarters as $quarter)
                                <option value="term_{{ $quarter->id }}" {{ $selectedPeriod == 'term_'.$quarter->id ? 'selected' : '' }}>{{ $quarter->name }}</option>
                            @endforeach
                            <option disabled>── Semesters ──</option>
                            @foreach($semesters as $semester)
                                <option value="semester_{{ $semester->id }}" {{ $selectedPeriod == 'semester_'.$semester->id ? 'selected' : '' }}>{{ $semester->name }}</option>
                            @endforeach
                            <option disabled>── Yearly ──</option>
                            <option value="yearly" {{ $selectedPeriod == 'yearly' ? 'selected' : '' }}>Yearly Report</option>
                        </select>
                    </form>

                    <div class="text-right hidden md:block">
                        <span class="block text-sm text-gray-500">Academic Year</span>
                        <span class="font-bold">{{ $enrollment->academicYear->name }}</span>
                    </div>
                </div>
            </div>

            <!-- Header for Selected Period -->
            <div class="mb-6 flex justify-between items-center">
                <h2 class="text-xl font-bold text-gray-800">{{ $periodName }}</h2>
                @if($selectedPeriod !== 'all')
                    <a href="{{ route('student.grades.download', ['period' => $selectedPeriod]) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <span class="mr-2">📥</span> Download PDF
                    </a>
                @endif
            </div>

            @forelse($grades as $termName => $termGrades)
                <div class="mb-8">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 px-2 border-l-4 border-indigo-500">{{ $termName }}</h3>
                    
                    @php
                        $groupedBySubject = $termGrades->groupBy('subject.name');
                    @endphp

                    <div class="grid grid-cols-1 gap-6">
                        @foreach($groupedBySubject as $subjectName => $marks)
                            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                                    <h4 class="font-bold text-lg text-gray-700">{{ $subjectName }}</h4>
                                </div>
                                <div class="p-6">
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <thead>
                                                <tr>
                                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assessment</th>
                                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Score</th>
                                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Max</th>
                                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Remarks</th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-gray-200">
                                                @php $subjectTotal = 0; $subjectMax = 0; @endphp
                                                @foreach($marks as $mark)
                                                    <tr>
                                                        <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900">
                                                            {{ $mark->assessmentTemplate->name ?? 'N/A' }}
                                                        </td>
                                                        <td class="px-3 py-2 whitespace-nowrap text-sm font-bold text-gray-900">
                                                            {{ $mark->score }}
                                                        </td>
                                                        <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500">
                                                            / {{ $mark->assessmentTemplate->max_score ?? '-' }}
                                                        </td>
                                                        <td class="px-3 py-2 text-sm text-gray-500">
                                                            {{ $mark->remarks ?? '-' }}
                                                        </td>
                                                    </tr>
                                                    @php 
                                                        $subjectTotal += $mark->score; 
                                                        $subjectMax += $mark->assessmentTemplate->max_score ?? 0;
                                                    @endphp
                                                @endforeach
                                                <!-- Total Row -->
                                                <tr class="bg-gray-50 font-bold">
                                                    <td class="px-3 py-2 text-right text-sm text-gray-700">Total</td>
                                                    <td class="px-3 py-2 text-sm text-indigo-700">{{ $subjectTotal }}</td>
                                                    <td class="px-3 py-2 text-sm text-gray-500">/ {{ $subjectMax }}</td>
                                                    <td></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-12 text-center text-gray-500">
                    <p class="text-lg">No grade records found for this academic year.</p>
                </div>
            @endforelse

        </div>
    </div>
</x-app-layout>
