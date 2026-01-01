<x-admin-layout>
    <x-slot name="header">Proficiency Matrix: {{ $term->name }}</x-slot>

    <div class="space-y-8 pb-12">
        <!-- Header & Top Actions -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 no-print">
            <div>
                <x-breadcrumb :items="[
                    ['label' => 'Academic Reports', 'url' => route('admin.academic-reports.index')],
                    ['label' => 'School Matrix', 'url' => '#']
                ]" />
                <h1 class="text-4xl font-black text-slate-900 tracking-tight mt-2 flex items-center gap-3">
                    <span class="w-1.5 h-8 bg-purple-600 rounded-full shadow-[0_0_15px_rgba(147,51,234,0.4)]"></span>
                    Consolidated Matrix
                </h1>
                <p class="text-slate-500 font-semibold mt-1 uppercase text-[10px] tracking-[0.3em] italic">Cross-grade proficiency and subject benchmarks</p>
            </div>
            
            <div class="flex items-center gap-3">
                <button onclick="window.print()" class="px-8 py-4 bg-slate-900 text-white font-black text-[10px] uppercase tracking-widest rounded-2xl hover:bg-purple-600 shadow-xl shadow-slate-200 transition-all flex items-center gap-3 active:scale-95">
                    <div class="w-8 h-8 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    </div>
                    Print Intelligence Report
                </button>
            </div>
        </div>

        <!-- Analytical Context Layer -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 no-print">
            <!-- Global Core Metrics -->
            <div class="bg-gradient-to-br from-indigo-900 via-purple-900 to-indigo-950 rounded-[2.5rem] p-10 text-white shadow-2xl relative overflow-hidden flex flex-col justify-center">
                <div class="absolute -top-20 -right-20 w-80 h-80 bg-white/5 rounded-full blur-3xl animate-pulse"></div>
                <div class="relative z-10">
                    <span class="text-[10px] font-black uppercase tracking-[0.4em] opacity-50 mb-4 block">Global Institutional Average</span>
                    <div class="flex items-baseline gap-3">
                        <span class="text-6xl font-black italic tracking-tighter">{{ number_format($overallAverage, 1) }}</span>
                        <span class="text-2xl font-black text-purple-400 opacity-80">%</span>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4 mt-12 pt-8 border-t border-white/10">
                        <div class="space-y-1">
                            <span class="text-[9px] font-black uppercase opacity-40">Elite Grade</span>
                            @php $topG = collect($gradeAverages)->sortDesc()->keys()->first(); $topGName = $gradeLevels->firstWhere('id', $topG)?->name ?? 'N/A'; @endphp
                            <p class="text-xl font-black tracking-tight uppercase truncate">{{ $topGName }}</p>
                        </div>
                        <div class="space-y-1">
                            <span class="text-[9px] font-black uppercase opacity-40">Core Dominance</span>
                            @php $topS = collect($subjectAverages)->sortDesc()->keys()->first(); $topSName = $allSubjects->firstWhere('id', $topS)?->name ?? 'N/A'; @endphp
                            <p class="text-xl font-black tracking-tight uppercase truncate" title="{{ $topSName }}">{{ $topSName }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Visualization Engine -->
            <div class="lg:col-span-2 bg-white/80 backdrop-blur-xl rounded-[2.5rem] p-10 shadow-2xl border border-white relative overflow-hidden group">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h3 class="text-xl font-black text-slate-900 tracking-tight">Subject proficiency curve</h3>
                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1">Cross-subject mean average synthesis</p>
                    </div>
                    <div class="flex items-center gap-4">
                         <div class="flex items-center gap-2">
                             <span class="w-3 h-3 rounded-full bg-purple-600 shadow-[0_0_8px_rgba(147,51,234,0.4)]"></span>
                             <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Mean (%)</span>
                         </div>
                    </div>
                </div>
                <div class="h-64 relative">
                    <canvas id="performanceChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Proficiency Matrix Body -->
        <div class="bg-white/80 backdrop-blur-xl rounded-[3rem] border border-white shadow-2xl overflow-hidden print:shadow-none print:border-0">
            <div class="p-10 no-print border-b border-slate-50 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="flex items-center gap-4">
                    <div class="w-1.5 h-8 bg-slate-900 rounded-full"></div>
                    <div>
                        <h3 class="text-xl font-black text-slate-900 uppercase tracking-tight">Technical Proficiency Matrix</h3>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Institutional Mean Data across all academic divisions</p>
                    </div>
                </div>
                <div class="px-6 py-2 bg-slate-50 rounded-full border border-slate-100">
                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] italic">Normalized @ 100% marks</span>
                </div>
            </div>
            
            <div class="overflow-x-auto print:overflow-visible">
                <table class="w-full text-center border-separate border-spacing-0">
                    <thead>
                        <tr class="bg-slate-900 text-white">
                            <th class="px-8 py-8 font-black uppercase tracking-[0.2em] text-[10px] border-r border-white/5 sticky left-0 z-30 bg-slate-900 min-w-[180px] text-left">
                                Grade Level Matrix
                            </th>
                            @foreach($allSubjects as $subject)
                                <th class="vertical-header-th relative px-2 py-10 border-r border-white/5 bg-slate-900/95 backdrop-blur-sm group/th">
                                    <div class="absolute inset-0 bg-white/5 opacity-0 group-hover/th:opacity-100 transition-opacity"></div>
                                    <div class="vertical-header-text font-black uppercase tracking-[0.2em] text-[10px] whitespace-nowrap leading-none">
                                        {{ $subject->name }}
                                    </div>
                                </th>
                            @endforeach
                            <th class="px-8 py-8 font-black uppercase tracking-[0.2em] text-[10px] bg-purple-600 shadow-[-10px_0_20px_rgba(0,0,0,0.1)] z-10">
                                Global Average
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($gradeLevels as $grade)
                            <tr class="group hover:bg-purple-50 transition-all">
                                <td class="px-8 py-6 font-black text-slate-900 border-r border-slate-100 sticky left-0 z-20 bg-white group-hover:bg-purple-50 transition-all text-left whitespace-nowrap shadow-[10px_0_15px_-10px_rgba(0,0,0,0.05)]">
                                    <div class="flex items-center gap-3">
                                        <div class="w-2 h-2 rounded-full bg-slate-300 group-hover:bg-purple-600 transition-colors"></div>
                                        {{ $grade->name }}
                                    </div>
                                </td>
                                @foreach($allSubjects as $subject)
                                    @php $score = $matrix[$grade->id][$subject->id] ?? null; @endphp
                                    <td class="px-3 py-6 font-black border-r border-slate-50 text-xs transition-all {{ $score ? ($score < 50 ? 'text-rose-500 bg-rose-50/20' : ($score > 85 ? 'text-emerald-500' : 'text-slate-600')) : 'text-slate-200 italic' }}">
                                        {{ $score ? number_format($score, 1) : '-' }}
                                    </td>
                                @endforeach
                                <td class="px-8 py-6 font-black text-lg text-purple-700 bg-purple-50/50 border-l border-purple-100 shadow-[-5px_0_15px_-5px_rgba(0,0,0,0.03)] group-hover:bg-purple-100">
                                    {{ number_format($gradeAverages[$grade->id], 1) }}<span class="text-[10px] font-bold opacity-40 ml-0.5">%</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-slate-50 text-slate-900 shadow-[0_-10px_30px_rgba(0,0,0,0.05)] relative z-20">
                            <td class="px-8 py-8 sticky left-0 z-20 bg-slate-50 font-black uppercase tracking-[0.2em] text-[10px] text-left border-t-2 border-slate-900">
                                Operational Means
                            </td>
                            @foreach($allSubjects as $subject)
                                <td class="px-3 py-8 border-r border-white font-black text-xs border-t-2 border-slate-900">
                                    {{ $subjectAverages[$subject->id] ? number_format($subjectAverages[$subject->id], 1) : '-' }}%
                                </td>
                            @endforeach
                            <td class="px-8 py-8 bg-slate-900 text-white font-black text-2xl italic tracking-tighter border-t-2 border-slate-900">
                                {{ number_format($overallAverage, 1) }}%
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Visualization Stack -->
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
                            label: 'Institutional Average (%)',
                            data: {!! json_encode($allSubjects->map(fn($s) => $subjectAverages[$s->id] ? round($subjectAverages[$s->id], 1) : 0)) !!},
                            backgroundColor: 'rgba(147, 51, 234, 0.8)',
                            borderColor: 'transparent',
                            borderWidth: 0,
                            borderRadius: (ctx.width / 100),
                            barThickness: 18,
                            hoverBackgroundColor: 'rgba(147, 51, 234, 1)',
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#0f172a',
                                titleFont: { size: 11, weight: 'black', family: "'Inter', sans-serif" },
                                bodyFont: { size: 14, weight: '900', family: "'Inter', sans-serif" },
                                padding: 16,
                                cornerRadius: 16,
                                displayColors: false,
                                callbacks: {
                                    label: function(context) { return context.parsed.y + '% Mean Score'; }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 100,
                                grid: { color: 'rgba(241, 245, 249, 1)', drawBorder: false },
                                ticks: { font: { weight: 'black', size: 10, family: "'Inter', sans-serif" }, padding: 10 }
                            },
                            x: {
                                grid: { display: false },
                                ticks: { 
                                    font: { weight: 'black', size: 8, family: "'Inter', sans-serif" },
                                    maxRotation: 45,
                                    minRotation: 45,
                                    padding: 10
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
            height: 200px;
            min-width: 50px;
        }
        .vertical-header-text {
            writing-mode: vertical-rl;
            transform: rotate(180deg);
            display: inline-block;
            margin: 0 auto;
        }

        @media print {
            .no-print { display: none !important; }
            body { background: white !important; font-family: serif; }
            .shadow-2xl, .shadow-xl { box-shadow: none !important; }
            .rounded-[3rem], .rounded-[2.5rem] { border-radius: 0 !important; }
            .bg-slate-900 { background-color: #0f172a !important; color: white !important; -webkit-print-color-adjust: exact; }
            .bg-purple-600 { background-color: #9333ea !important; color: white !important; -webkit-print-color-adjust: exact; }
            .bg-slate-50 { background-color: #f8fafc !important; -webkit-print-color-adjust: exact; }
            .sticky { position: static !important; }
            table { font-size: 8pt; width: 100%; border-collapse: collapse; }
            th, td { border: 0.5pt solid #e2e8f0 !important; -webkit-print-color-adjust: exact; }
            .vertical-header-th { height: 140px !important; }
            @page {
                size: landscape;
                margin: 0.5cm;
            }
        }
    </style>
</x-admin-layout>
