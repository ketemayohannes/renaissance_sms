<x-admin-layout>
    <x-slot name="header">Subject Analysis: {{ $subject->name }} ({{ $gradeLevel->name }})</x-slot>

    <div class="space-y-6">
        <div class="flex justify-between items-center bg-white p-4 rounded-lg shadow-sm border border-slate-200 no-print">
            <div>
                <h2 class="text-lg font-bold text-slate-800">Subject Analysis Report</h2>
                <p class="text-sm text-slate-500">{{ $subject->name }} ({{ $gradeLevel->name }})</p>
            </div>
            <div class="flex gap-2">
                <button onclick="window.print()" class="btn-primary flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    Print Report
                </button>
                <a href="{{ route('admin.academic-reports.index') }}" class="btn-secondary">Back to Selection</a>
            </div>
        </div>


        
        <!-- Header Info Card -->
        <div class="bg-indigo-900 rounded-3xl p-8 text-white shadow-2xl mb-8 relative overflow-hidden">
            <div class="absolute top-0 right-0 -mt-20 -mr-20 w-80 h-80 bg-indigo-500 rounded-full opacity-20 blur-3xl"></div>
            <div class="relative z-10">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div>
                        <span class="bg-indigo-500 bg-opacity-30 text-indigo-100 text-xs font-bold px-3 py-1 rounded-full uppercase tracking-widest whitespace-nowrap">Grade-Level Analytics</span>
                        <h1 class="text-4xl font-extrabold mt-4 tracking-tight">{{ $subject->name }} <span class="text-indigo-300">Analysis</span></h1>
                        <p class="text-indigo-200 mt-2 text-lg font-medium opacity-90">{{ $gradeLevel->name }} • {{ $academicYear->name }} • {{ $term->name }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 mt-10">
                    <div class="bg-white bg-opacity-10 backdrop-blur-md p-5 rounded-2xl border border-white border-opacity-20">
                        <p class="text-indigo-200 text-xs font-bold uppercase tracking-wider mb-1">Pass Rate</p>
                        <p class="text-3xl font-black text-white">
                            {{ $overallStats->total_appeared > 0 ? round(($overallStats->total_passed / $overallStats->total_appeared) * 100, 1) : 0 }}%
                        </p>
                    </div>
                    <div class="bg-white bg-opacity-10 backdrop-blur-md p-5 rounded-2xl border border-white border-opacity-20">
                        <p class="text-indigo-200 text-xs font-bold uppercase tracking-wider mb-1">Average Score</p>
                        <p class="text-3xl font-black text-white">{{ number_format($overallStats->average, 1) }}</p>
                    </div>
                    <div class="bg-white bg-opacity-10 backdrop-blur-md p-5 rounded-2xl border border-white border-opacity-20">
                        <p class="text-indigo-200 text-xs font-bold uppercase tracking-wider mb-1">Highest Score</p>
                        <p class="text-3xl font-black text-white">{{ number_format($overallStats->highest, 1) }}</p>
                    </div>
                    <div class="bg-white bg-opacity-10 backdrop-blur-md p-5 rounded-2xl border border-white border-opacity-20">
                        <p class="text-indigo-200 text-xs font-bold uppercase tracking-wider mb-1">Total Appeared</p>
                        <p class="text-3xl font-black text-white">{{ $overallStats->total_appeared }} <span class="text-sm font-normal text-indigo-200 ml-1">Students</span></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Section-wise Comparison -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-3xl shadow-xl overflow-hidden border border-gray-100 h-full">
                    <div class="p-6 border-b border-gray-50 flex justify-between items-center bg-gray-50/50">
                        <h3 class="text-xl font-bold text-gray-900 flex items-center">
                            <svg class="w-6 h-6 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                            Section-wise Comparison
                        </h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="bg-gray-50 text-gray-500 text-xs font-bold uppercase tracking-wider">
                                    <th class="px-6 py-4">Section</th>
                                    <th class="px-4 py-4 text-center">Appeared</th>
                                    <th class="px-4 py-4 text-center">Pass %</th>
                                    <th class="px-4 py-4 text-center">High</th>
                                    <th class="px-4 py-4 text-center">Avg</th>
                                    <th class="px-4 py-4">Trend</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($sectionStats as $stats)
                                    <tr class="hover:bg-indigo-50/30 transition-colors">
                                        <td class="px-6 py-4 font-bold text-gray-900 text-lg">Section {{ $stats->section->name }}</td>
                                        <td class="px-4 py-4 text-center font-medium">{{ $stats->appeared }}</td>
                                        <td class="px-4 py-4 text-center whitespace-nowrap">
                                            <div class="flex flex-col items-center gap-1">
                                                <span class="px-3 py-1 rounded-full text-sm font-bold {{ $stats->pass_rate >= 85 ? 'bg-green-100 text-green-700' : ($stats->pass_rate >= 75 ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700') }}">
                                                    {{ round($stats->pass_rate, 1) }}%
                                                </span>
                                                <span class="text-[10px] font-medium text-gray-500">
                                                    {{ $stats->passed }} out of {{ $stats->appeared }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 text-center font-bold text-indigo-600">{{ number_format($stats->highest, 1) }}</td>
                                        <td class="px-4 py-4 text-center font-bold text-gray-700">{{ number_format($stats->average, 1) }}</td>
                                        <td class="px-4 py-4">
                                            @php
                                                $barWidth = ($stats->average / max(1, $overallStats->highest)) * 100;
                                            @endphp
                                            <div class="w-24 h-2 bg-gray-100 rounded-full overflow-hidden">
                                                <div class="h-full bg-indigo-500" style="width: {{ $barWidth }}%"></div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-12 text-center text-gray-500 italic">No data available for sections.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Top Performers & Others -->
            <div class="space-y-8">
                <!-- Top Performers -->
                <div class="bg-white rounded-3xl shadow-xl p-6 border border-gray-100">
                    <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                        <svg class="w-6 h-6 mr-2 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path></svg>
                        Grade Top Students
                    </h3>
                    
                    <div class="space-y-4">
                        @foreach($topPerformers as $row)
                        <div class="flex items-center justify-between p-4 rounded-2xl bg-gray-50 hover:bg-indigo-50 transition-all border border-transparent hover:border-indigo-100 group">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-indigo-600 flex items-center justify-center text-white font-bold text-sm">
                                    {{ $loop->iteration }}
                                </div>
                                <div>
                                    <p class="font-bold text-gray-900 group-hover:text-indigo-700 transition-colors uppercase text-sm">{{ $row['student']->full_name }}</p>
                                    <p class="text-xs text-gray-500 font-medium">Sec {{ $row['section']->name }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-black text-indigo-600">{{ number_format($row['score'], 1) }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Interpretation Card -->
                <div class="bg-gradient-to-br from-indigo-600 to-purple-700 rounded-3xl p-6 text-white shadow-xl">
                    <h4 class="font-bold text-lg mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Report Summary
                    </h4>
                    <p class="text-indigo-100 text-sm leading-relaxed mb-4 opacity-90">
                        This report aggregates academic data across all <strong>{{ $gradeLevel->sections->count() }} sections</strong>. 
                        A total of <strong>{{ $overallStats->total_appeared }} students</strong> were evaluated for {{ $subject->name }}.
                    </p>
                    <div class="space-y-2">
                        @php
                            $bestSection = collect($sectionStats)->sortByDesc('pass_rate')->first();
                        @endphp
                        @if($bestSection)
                            <div class="flex items-center gap-2 text-sm bg-white/10 p-2 rounded-lg">
                                <span class="bg-green-400 w-2 h-2 rounded-full"></span>
                                <span><strong>Section {{ $bestSection->section->name }}</strong> has the highest pass rate ({{ round($bestSection->pass_rate, 1) }}%).</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
            .py-12 { padding-top: 0 !important; }
            .shadow-xl, .shadow-2xl { box-shadow: none !important; }
            .rounded-3xl, .rounded-2xl { border-radius: 8px !important; }
            .bg-indigo-900 { background-color: #312e81 !important; color: white !important; -webkit-print-color-adjust: exact; }
            .bg-gray-50 { background-color: #f9fafb !important; -webkit-print-color-adjust: exact; }
            .bg-indigo-50\/30 { background-color: transparent !important; }
        }
    </style>
</x-admin-layout>
