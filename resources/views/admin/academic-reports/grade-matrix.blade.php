<x-admin-layout>
    @php
        $divisionName = $gradeLevels->first()?->division?->name ?? 'Institutional';
        $reportTitle = $term->name . ' ' . $divisionName . ' Result Analysis';
        $summaryData = $termData[$term->id] ?? null;
        $overallAvg = $summaryData['overallAverage'] ?? 0;
    @endphp

    <x-slot name="header">{{ $reportTitle }}</x-slot>

    <div class="space-y-12 pb-20">
        <!-- Header & Top Actions -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 no-print">
            <div>
                <x-breadcrumb :items="[
                    ['label' => 'Academic Reports', 'url' => route('admin.academic-reports.index')],
                    ['label' => 'School Matrix', 'url' => '#']
                ]" />
                <h1 class="text-4xl font-black text-slate-900 tracking-tight mt-2 flex items-center gap-3">
                    <span class="w-1.5 h-8 bg-indigo-600 rounded-full shadow-[0_0_15px_rgba(79,70,229,0.4)]"></span>
                    {{ $reportTitle }}
                </h1>
                <p class="text-slate-500 font-semibold mt-1 uppercase text-[10px] tracking-[0.3em] italic">Consolidated academic performance across grade levels</p>
            </div>
            
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.academic-reports.matrix-reorder') }}" class="px-6 py-4 bg-white border-2 border-slate-200 text-slate-600 font-black text-[10px] uppercase tracking-widest rounded-2xl hover:bg-slate-50 transition-all flex items-center gap-3 active:scale-95 no-print">
                    <div class="w-8 h-8 rounded-xl bg-slate-100 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"></path></svg>
                    </div>
                    Reorder
                </a>
                <a href="{{ route('admin.academic-reports.grade-matrix.pdf', request()->all()) }}" class="px-8 py-4 bg-indigo-600 text-white font-black text-[10px] uppercase tracking-widest rounded-2xl hover:bg-indigo-700 shadow-xl shadow-indigo-100 transition-all flex items-center gap-3 active:scale-95 no-print">
                    <div class="w-8 h-8 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    </div>
                    Download PDF
                </a>
                <button onclick="window.print()" class="px-8 py-4 bg-slate-900 text-white font-black text-[10px] uppercase tracking-widest rounded-2xl hover:bg-indigo-600 shadow-xl shadow-slate-200 transition-all flex items-center gap-3 active:scale-95">
                    <div class="w-8 h-8 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    </div>
                    Print Official Analysis
                </button>
            </div>
        </div>

        <!-- Print-Only Header -->
        <div class="hidden print:block mb-8">
            <div class="flex items-center justify-between border-b-2 border-slate-900 pb-4">
                <div class="flex items-center gap-4">
                    @php $settings = \App\Models\AcademicReportSetting::first(); @endphp
                    @if($settings && $settings->roster_logo_path)
                        <img src="{{ Storage::url($settings->roster_logo_path) }}" class="w-16 h-16 object-contain" alt="Logo">
                    @else
                        <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center text-slate-400 font-black text-xs border border-slate-200">LOGO</div>
                    @endif
                    <div>
                        <h2 class="text-2xl font-black uppercase tracking-tight">{{ $reportTitle }}</h2>
                        <p class="text-xs font-bold text-slate-500 uppercase tracking-widest">{{ $academicYear->name }} Academic Cycle</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-xs font-black uppercase text-slate-400 tracking-widest">Generated On</p>
                    <p class="text-sm font-black">{{ now()->format('M d, Y') }}</p>
                </div>
            </div>
        </div>

        @foreach($termData as $tId => $data)
            @php 
                $currTerm = $data['term'];
                $matrix = $data['matrix'];
                $gradeAverages = $data['gradeAverages'];
                $subjectAverages = $data['subjectAverages'];
                $termAvg = $data['overallAverage'];
            @endphp
            
            <div class="space-y-6">
                <!-- Term Header (Only in multi-term view or print) -->
                <div class="flex items-center gap-3 px-6 py-3 bg-slate-50 rounded-2xl border border-slate-100 no-print">
                    <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                    <h3 class="text-sm font-black text-slate-600 uppercase tracking-[0.2em]">{{ $currTerm->name }} Data Set</h3>
                </div>

                <div class="bg-white rounded-[2rem] border border-slate-100 shadow-xl overflow-hidden print:border-0 print:shadow-none print:rounded-none">
                    <div class="overflow-x-auto print:overflow-visible">
                        <table class="w-full text-center border-collapse">
                            <thead>
                                <tr class="bg-slate-200 text-slate-800">
                                    <th class="px-6 py-6 font-black uppercase tracking-widest text-[10px] border border-slate-300 min-w-[150px] text-left">
                                        {{ $currTerm->type === 'semester' ? 'Grade / Term Avg' : 'Quarter / Subject' }}
                                    </th>
                                    @foreach($allSubjects as $subject)
                                        <th class="vertical-header-th relative px-2 py-8 border border-slate-300 bg-slate-100">
                                            <div class="vertical-header-text font-black uppercase tracking-widest text-[9px] whitespace-nowrap leading-none">
                                                {{ $subject->name }}
                                            </div>
                                        </th>
                                    @endforeach
                                    <th class="px-6 py-6 font-black uppercase tracking-widest text-[10px] bg-slate-200 border border-slate-300">
                                        {{ $currTerm->name }} Average
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($gradeLevels as $grade)
                                    <tr class="hover:bg-indigo-50/30 transition-all">
                                        <td class="px-6 py-4 font-black text-slate-700 border border-slate-300 text-left text-xs uppercase">
                                            {{ $grade->name }}
                                        </td>
                                        @foreach($allSubjects as $subject)
                                            @php $score = $matrix[$grade->id][$subject->id] ?? null; @endphp
                                            <td class="px-2 py-4 font-bold border border-slate-300 text-[11px] {{ $score && $score < 50 ? 'text-rose-600' : 'text-slate-600' }}">
                                                {{ $score ? number_format($score, 2) : '-' }}
                                            </td>
                                        @endforeach
                                        <td class="px-6 py-4 font-black text-base text-slate-900 border border-slate-300 bg-slate-50/50">
                                            {{ number_format($gradeAverages[$grade->id], 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="bg-slate-100 font-black text-slate-900">
                                    <td class="px-6 py-6 border border-slate-300 text-left text-[10px] uppercase tracking-widest">
                                        {{ $currTerm->name }} Subject Average
                                    </td>
                                    @foreach($allSubjects as $subject)
                                        <td class="px-2 py-6 border border-slate-300 text-[11px]">
                                            {{ $subjectAverages[$subject->id] ? number_format($subjectAverages[$subject->id], 2) : '-' }}
                                        </td>
                                    @endforeach
                                    <td class="px-6 py-6 border border-slate-300 text-2xl tracking-tighter italic">
                                        {{ number_format($termAvg, 2) }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Spacer between blocks -->
            @if(!$loop->last)
                <div class="h-1 bg-slate-900 my-8 print:my-4"></div>
            @endif
        @endforeach
    </div>

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

            /* Hide all admin layout chrome */
            nav,
            header,
            footer,
            aside,
            [role="navigation"],
            .sidebar,
            #sidebar {
                display: none !important;
                width: 0 !important;
                height: 0 !important;
            }

            /* Make main content fill the full page */
            body { background: white !important; font-family: serif; margin: 0; padding: 0; }
            main, main > * { padding: 0 !important; margin: 0 !important; }

            .shadow-xl { box-shadow: none !important; }
            .bg-slate-200 { background-color: #e2e8f0 !important; -webkit-print-color-adjust: exact; }
            .bg-slate-100 { background-color: #f1f5f9 !important; -webkit-print-color-adjust: exact; }
            .bg-slate-50 { background-color: #f8fafc !important; -webkit-print-color-adjust: exact; }
            
            table { font-size: 8pt; width: 100%; border-collapse: collapse; }
            th, td { border: 0.5pt solid #cbd5e1 !important; padding: 4pt !important; -webkit-print-color-adjust: exact; }
            .vertical-header-th { height: 120px !important; }
            
            @page {
                size: landscape;
                margin: 0.8cm;
            }
        }
    </style>
</x-admin-layout>
