<x-admin-layout>
    <x-slot name="header">Result Analysis Matrix: {{ $term->name }}</x-slot>

    <div class="space-y-8">
        <div class="flex justify-between items-center bg-white p-4 rounded-lg shadow-sm border border-slate-200 no-print">
            <div>
                <h2 class="text-lg font-bold text-slate-800">Consolidated Result Analysis</h2>
                <p class="text-sm text-slate-500">{{ $term->name }} - {{ $academicYear->name }}</p>
            </div>
            <div class="flex gap-3">
                <button onclick="window.print()" class="btn-primary flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    Print Report
                </button>
                <a href="{{ route('admin.academic-reports.index') }}" class="btn-secondary">Back</a>
            </div>
        </div>


        
        <!-- Interactive Analytics Row -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 no-print">
            <!-- Summary Stats -->
            <div class="bg-gradient-to-br from-indigo-700 to-purple-800 rounded-[2rem] p-8 text-white shadow-2xl relative overflow-hidden flex flex-col justify-center">
                <div class="absolute -top-10 -right-10 w-40 h-40 bg-white/10 rounded-full blur-3xl"></div>
                <div class="relative z-10">
                    <span class="text-[10px] font-black uppercase tracking-[0.2em] opacity-60">School-Wide Performance</span>
                    <h1 class="text-6xl font-black mt-2 leading-none">{{ number_format($overallAverage, 1) }}%</h1>
                    <p class="text-indigo-100 mt-4 text-sm font-medium opacity-80 leading-relaxed uppercase tracking-wider">
                        Overall average across <strong>{{ $gradeLevels->count() }} grades</strong> and <strong>{{ $allSubjects->count() }} subjects</strong>.
                    </p>
                    
                    <div class="grid grid-cols-2 gap-4 mt-8">
                        <div class="bg-white/10 p-4 rounded-2xl border border-white/10">
                            <p class="text-[10px] font-bold uppercase opacity-60 mb-1">Top Grade</p>
                            @php $topG = collect($gradeAverages)->sortDesc()->keys()->first(); $topGName = $gradeLevels->firstWhere('id', $topG)?->name ?? 'N/A'; @endphp
                            <p class="text-xl font-black">{{ $topGName }}</p>
                        </div>
                        <div class="bg-white/10 p-4 rounded-2xl border border-white/10">
                            <p class="text-[10px] font-bold uppercase opacity-60 mb-1">Top Subject</p>
                            @php $topS = collect($subjectAverages)->sortDesc()->keys()->first(); $topSName = $allSubjects->firstWhere('id', $topS)?->name ?? 'N/A'; @endphp
                            <p class="text-xl font-black truncate" title="{{ $topSName }}">{{ $topSName }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Chart Visualization -->
            <div class="lg:col-span-2 bg-white rounded-[2rem] p-8 shadow-xl border border-gray-100">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-black text-gray-900 tracking-tight">Performance Visualization</h3>
                    <div class="flex gap-2">
                        <span class="flex items-center text-xs font-bold text-gray-500"><span class="w-3 h-3 bg-indigo-500 rounded-sm mr-1"></span> Subjects</span>
                    </div>
                </div>
                <div class="h-64 relative">
                    <canvas id="performanceChart"></canvas>
                </div>
            </div>
        </div>

        <!-- The Matrix Table (Matching Request) -->
        <div class="bg-white shadow-2xl rounded-[2rem] overflow-hidden border border-gray-100 print:shadow-none print:border-0">
            <div class="p-8 no-print border-b border-gray-50 flex justify-between items-center bg-gray-50/50">
                <h3 class="text-xl font-black text-gray-900 flex items-center tracking-tight">
                    <div class="w-2 h-8 bg-indigo-600 mr-3 rounded-full"></div>
                    Subject Proficiency Matrix
                </h3>
                <div class="text-xs font-bold text-gray-400 uppercase tracking-widest">
                    All scores represent Mean Average (%)
                </div>
            </div>
            
            <div class="overflow-x-auto print:overflow-visible">
                <table class="w-full text-center border-collapse">
                    <thead>
                        <tr class="bg-gray-900 text-white">
                            <th class="px-6 py-6 font-black uppercase tracking-widest text-xs border-r border-gray-800 sticky left-0 z-20 bg-gray-900 min-w-[150px]">
                                Grade / Subject
                            </th>
                            @foreach($allSubjects as $subject)
                                <th class="vertical-header-th relative px-2 py-8 border-r border-gray-800">
                                    <div class="vertical-header-text font-black uppercase tracking-widest text-[10px] whitespace-nowrap">
                                        {{ $subject->name }}
                                    </div>
                                </th>
                            @endforeach
                            <th class="px-6 py-6 font-black uppercase tracking-widest text-xs bg-indigo-900">
                                {{ $term->name }} Average
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($gradeLevels as $grade)
                            <tr class="hover:bg-indigo-50/50 transition-colors group">
                                <td class="px-6 py-4 font-black text-gray-900 border-r border-gray-100 sticky left-0 z-20 bg-white group-hover:bg-indigo-50/50 transition-colors text-left whitespace-nowrap shadow-[4px_0_10px_rgba(0,0,0,0.02)]">
                                    {{ $grade->name }}
                                </td>
                                @foreach($allSubjects as $subject)
                                    @php $score = $matrix[$grade->id][$subject->id] ?? null; @endphp
                                    <td class="px-3 py-4 font-bold border-r border-gray-50 text-sm {{ $score ? ($score < 50 ? 'text-red-600 bg-red-50/30' : 'text-gray-700') : 'text-gray-200 italic' }}">
                                        {{ $score ? number_format($score, 2) : '-' }}
                                    </td>
                                @endforeach
                                <td class="px-6 py-4 font-black text-lg text-indigo-700 bg-indigo-50/50 border-l border-indigo-100">
                                    {{ number_format($gradeAverages[$grade->id], 2) }}%
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-gray-100 text-gray-900 font-black">
                            <td class="px-6 py-6 sticky left-0 z-20 bg-gray-100 uppercase tracking-tight text-xs text-left">
                                {{ $term->name }} Subject Average
                            </td>
                            @foreach($allSubjects as $subject)
                                <td class="px-3 py-6 border-r border-white/50 bg-gray-200/50">
                                    {{ $subjectAverages[$subject->id] ? number_format($subjectAverages[$subject->id], 2) : '-' }}
                                </td>
                            @endforeach
                            <td class="px-6 py-6 bg-indigo-600 text-white text-xl">
                                {{ number_format($overallAverage, 2) }}%
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('performanceChart');
            if (ctx) {
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: {!! json_encode($allSubjects->pluck('name')) !!},
                        datasets: [{
                            label: 'Subject Average (%)',
                            data: {!! json_encode($allSubjects->map(fn($s) => $subjectAverages[$s->id] ? round($subjectAverages[$s->id], 1) : 0)) !!},
                            backgroundColor: 'rgba(79, 70, 229, 0.8)',
                            borderColor: 'rgb(79, 70, 229)',
                            borderWidth: 0,
                            borderRadius: 8,
                            barThickness: 15,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#111827',
                                titleFont: { size: 12, weight: 'bold' },
                                bodyFont: { size: 14, weight: '900' },
                                padding: 12,
                                cornerRadius: 12,
                                displayColors: false,
                                callbacks: {
                                    label: function(context) { return context.parsed.y + '% Marks'; }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 100,
                                grid: { color: '#f3f4f6', drawBorder: false },
                                ticks: { font: { weight: 'bold', size: 10 } }
                            },
                            x: {
                                grid: { display: false },
                                ticks: { 
                                    font: { weight: 'bold', size: 9 },
                                    maxRotation: 45,
                                    minRotation: 45
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>

    <style>
        .vertical-header-th {
            height: 180px;
            min-width: 45px;
        }
        .vertical-header-text {
            writing-mode: vertical-rl;
            transform: rotate(180deg);
            display: inline-block;
            margin: 0 auto;
        }

        @media print {
            .no-print { display: none !important; }
            body { background: white !important; margin: 0; padding: 0; }
            .py-10 { padding: 0 !important; }
            .px-4, .px-6, .px-8 { padding-left: 2px !important; padding-right: 2px !important; }
            .shadow-2xl, .shadow-xl { box-shadow: none !important; }
            .rounded-[2rem], .rounded-xl { border-radius: 0 !important; }
            .bg-gray-900 { background-color: #000 !important; color: white !important; -webkit-print-color-adjust: exact; }
            .bg-indigo-900 { background-color: #312e81 !important; -webkit-print-color-adjust: exact; }
            .bg-gray-100 { background-color: #f3f4f6 !important; -webkit-print-color-adjust: exact; }
            .bg-emerald-50\/20 { background-color: transparent !important; }
            .sticky { position: static !important; }
            table { font-size: 8pt; border: 1px solid #ddd !important; }
            th, td { border: 1px solid #ddd !important; -webkit-print-color-adjust: exact; }
            .vertical-header-th { height: 120px !important; }
            
            @page {
                size: landscape;
                margin: 0.5cm;
            }
        }
    </style>
    </div>
</x-admin-layout>
