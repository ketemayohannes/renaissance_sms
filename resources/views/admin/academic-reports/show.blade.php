<x-admin-layout>
    <x-slot name="header">Academic Roster: {{ $section->name }}</x-slot>

    <div class="space-y-8 pb-12">
        <!-- Modern Header & Actions (No-Print) -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 no-print">
            <div>
                <x-breadcrumb :items="[
                    ['label' => 'Academic Reports', 'url' => route('admin.academic-reports.index')],
                    ['label' => 'Roster Evidence', 'url' => '#']
                ]" />
                <h1 class="text-4xl font-black text-slate-900 tracking-tight mt-2 flex items-center gap-3">
                    <span class="w-1.5 h-8 bg-indigo-600 rounded-full shadow-[0_0_15px_rgba(79,70,229,0.4)]"></span>
                    Term Roster
                </h1>
                <p class="text-slate-500 font-semibold mt-1 uppercase text-[10px] tracking-[0.3em] italic">{{ $section->gradeLevel->name }} — {{ $section->name }} | {{ $term->name }}</p>
            </div>
            
            <div class="flex items-center gap-3">
                <form action="{{ route('admin.academic-reports.recalculate') }}" method="POST" class="inline">
                    @csrf
                    <input type="hidden" name="academic_year_id" value="{{ $academicYear->id }}">
                    <input type="hidden" name="term_id" value="{{ $term->id }}">
                    <input type="hidden" name="section_id" value="{{ $section->id }}">
                    <button type="submit" class="px-6 py-4 bg-white/80 backdrop-blur-xl border border-slate-200 text-slate-600 font-black text-[10px] uppercase tracking-widest rounded-2xl hover:bg-indigo-50 hover:text-indigo-600 shadow-xl shadow-slate-200/50 transition-all flex items-center gap-3 active:scale-95 group">
                        <div class="w-8 h-8 rounded-xl bg-indigo-50 group-hover:bg-indigo-100 flex items-center justify-center transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                        </div>
                        Sync Stats
                    </button>
                </form>

                <button onclick="window.print()" class="px-8 py-4 bg-slate-900 text-white font-black text-[10px] uppercase tracking-widest rounded-2xl hover:bg-indigo-600 shadow-xl shadow-slate-200 transition-all flex items-center gap-3 active:scale-95 group">
                    <div class="w-8 h-8 rounded-xl bg-white/10 flex items-center justify-center group-hover:rotate-12 transition-transform">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    </div>
                    Print Certified Roster
                </button>
            </div>
        </div>

        <!-- Roster Document Container -->
        <div class="max-w-[100%] mx-auto bg-white p-4 sm:p-12 shadow-2xl rounded-[3rem] border border-slate-100 print:shadow-none print:p-0 print:border-0 print:rounded-none print-container">
            @php
                $isSem = $term->isSemester();
                $isYearly = ($term->type === 'yearly');
                $subjectCount = count($subjects);
                // Width calculations
                $fixedWidth = 3 + 32 + 3 + ($isYearly || $isSem ? 6 : 0) + 5 + 5 + 3 + 3 + 4;
                $subjectWidth = ($subjectCount > 0) ? (100 - $fixedWidth) / $subjectCount : 0;
                
                // Chunking logic for robust multi-page printing
                if ($isSem) {
                    $p1Size = 5;
                    $otherSize = 5;
                } elseif ($isYearly) {
                    $p1Size = 3;
                    $otherSize = 3;
                } else {
                    $p1Size = 25; 
                    $otherSize = 25;
                }

                $pages = [];
                $pages[] = array_slice($reports, 0, $p1Size);
                if (count($reports) > $p1Size) {
                    $remain = array_chunk(array_slice($reports, $p1Size), $otherSize);
                    foreach($remain as $r) $pages[] = $r;
                }
                
                $totalCols = 8 + $subjectCount + ($isSem || $isYearly ? 1 : 0) + 1;
                $globalIndex = 0;
            @endphp
            
            @foreach($pages as $pageIndex => $pageReports)
            @if(count($pageReports) > 0)
            <div class="roster-page-chunk" style="{{ $loop->last ? 'page-break-after: avoid; break-after: avoid;' : 'page-break-after: always; break-after: page;' }} clear: both; display: block; width: 100%;">
                
                <!-- STABLE TABLE-BASED HEADER -->
                <table class="w-full header-info-table" style="border: none !important; margin-bottom: 5px; width: 100%; border-collapse: collapse;">
                    <tr style="border: none !important;">
                        <td style="width: 25%; border: none !important; vertical-align: middle; text-align: left; padding: 5px;">
                            @if($settings && $settings->roster_logo_path)
                                <img src="{{ asset('storage/' . $settings->roster_logo_path) }}" alt="Roster Logo" style="height: 75px; width: auto; object-fit: contain;">
                            @elseif($generalSettings && $generalSettings->logo_path)
                                <img src="{{ asset('storage/' . $generalSettings->logo_path) }}" alt="General Logo" style="height: 75px; width: auto; object-fit: contain;">
                            @else
                                <div style="height: 65px; width: 65px; border: 2pt solid #1e3a8a; border-radius: 50%; color: #1e3a8a; font-weight: bold; font-size: 8pt; display: flex; align-items: center; justify-content: center; text-align: center; line-height: 1;">
                                    Renaissance<br>School
                                </div>
                            @endif
                        </td>
                        <td style="width: 50%; border: none !important; text-align: center; vertical-align: middle; padding: 2px;">
                            <h1 style="font-family: serif; font-weight: 800; font-size: 22pt; margin: 0; color: black; text-transform: uppercase; line-height: 1.0;">
                                {{ $settings->school_name ?? ($generalSettings->school_name ?? 'RENAISSANCE SCHOOL') }}
                            </h1>
                            <h2 style="font-family: serif; font-size: 15pt; margin: 5px 0 0 0; color: black; font-weight: normal; font-style: italic;">
                                {{ $term->name }} Roster
                            </h2>
                        </td>
                        <td style="width: 25%; border: none !important; vertical-align: middle; text-align: right; padding: 5px;">
                             <!-- Removed Generated Info per User Request -->
                        </td>
                    </tr>
                    <tr style="border: none !important;">
                        <td colspan="3" style="border: none !important; padding-top: 5px;">
                            <table style="width: 100%; border: none !important; border-top: 1.5pt solid black; border-bottom: 1.5pt solid black; padding: 5px 0;">
                                <tr style="border: none !important;">
                                    <td style="width: 25%; border: none !important; text-align: left; font-size: 13pt; font-family: serif; font-weight: bold;">Grade: {{ str_replace('Grade ', '', $section->gradeLevel->name) }}</td>
                                    <td style="width: 50%; border: none !important; text-align: center; font-size: 13pt; font-family: serif; font-weight: bold; padding-left: 45px;">Section: {{ $section->name }}</td>
                                    <td style="width: 25%; border: none !important; text-align: right; font-size: 13pt; font-family: serif; font-weight: bold; white-space: nowrap;">
                                        Academic Year: {{ $academicYear->name }} G.C ({{ (int)explode('/', $academicYear->name)[0] - 7 }} E.C)
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>

                <div class="roster-scroll-wrapper">
                    <table class="roster-table mx-auto text-[10px] sm:text-[11px] font-sans table-fixed whitespace-nowrap w-full" style="width: 100%; border-collapse: collapse;">
                        <colgroup>
                            <col style="width: 3%;"><!-- S.no -->
                            <col style="width: 32%;"><!-- Student Full Name -->
                            <col style="width: 3%;"><!-- Gender -->
                            @if($isSem || $isYearly)
                            <col style="width: 6%;"><!-- Quarter/Term -->
                            @endif
                            @foreach($subjects as $subject)
                            <col style="width:{{ $subjectWidth }}%;"><!-- Subject -->
                            @endforeach
                            <col style="width: 5%;"><!-- Total -->
                            <col style="width: 5%;"><!-- Average -->
                            <col style="width: 3%;"><!-- Conduct -->
                            <col style="width: 3%;"><!-- Absence -->
                            <col style="width: 4%;"><!-- Rank -->
                        </colgroup>
                        <thead>
                            <tr class="header-row">
                                <th class="p-0 text-center"><div class="vertical-text">S.no</div></th>
                                <th class="p-2 text-center font-bold text-sm bg-gray-100" style="width: 32% !important;">Full Name of the Student</th>
                                <th class="p-0 text-center"><div class="vertical-text">Sex</div></th>
                                @if($isSem || $isYearly)
                                <th class="p-0 text-center"><div class="vertical-text">Term</div></th>
                                @endif
                                @foreach($subjects as $subject)
                                    <th class="p-0 h-28 relative text-center align-bottom pb-1">
                                        <div class="vertical-text">{{ $subject->name }}</div>
                                    </th>
                                @endforeach
                                <th class="p-0 text-center"><div class="vertical-text">Total</div></th>
                                <th class="p-0 text-center"><div class="vertical-text">Average</div></th>
                                <th class="p-0 text-center"><div class="vertical-text">Conduct</div></th>
                                <th class="p-0 text-center"><div class="vertical-text">Absence</div></th>
                                <th class="p-0 text-center"><div class="vertical-text">Rank</div></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pageReports as $report)
                                @php $globalIndex++; @endphp
                                @if($isSem)
                                    @foreach(['q1', 'q2', 'avg'] as $rowIndex => $type)
                                        <tr class="h-[26px]">
                                            @if($rowIndex === 0)
                                                <td rowspan="3" class="p-0.5 text-center align-middle border-b-[2pt] border-black text-[12px] font-black">{{ $globalIndex }}</td>
                                                <td rowspan="3" class="p-1 px-4 uppercase text-[12px] whitespace-normal leading-[1.2] break-words font-black text-left align-middle border-b-[2pt] border-black">{{ $report['student']->full_name }}</td>
                                                <td rowspan="3" class="p-0.5 text-center align-middle border-b-[2pt] border-black text-[12px] font-black">{{ substr($report['student']->gender, 0, 1) }}</td>
                                            @endif
                                            <td class="p-0.5 text-center text-[10px] font-bold {{ $rowIndex === 2 ? 'border-b-[2pt] border-black bg-gray-200 font-black' : '' }}">
                                                {{ $report['rows'][$type]['label'] ?? '' }}
                                            </td>
                                            @foreach($subjects as $subject)
                                                <td class="p-0.5 text-center text-[11px] {{ $rowIndex === 2 ? 'border-b-[2pt] border-black bg-gray-200 font-black' : '' }}">
                                                    @php $sM = ($report['rows'][$type]['marks'] ?? null) ? ($report['rows'][$type]['marks'][$subject->id] ?? null) : null; @endphp
                                                    {{ \App\Helpers\NumberFormatter::format($sM) }}
                                                </td>
                                            @endforeach
                                            <td class="p-0.5 text-center text-[11px] font-black {{ $rowIndex === 2 ? 'border-b-[2pt] border-black bg-gray-200' : '' }}">{{ \App\Helpers\NumberFormatter::format($report['rows'][$type]['total'] ?? 0) }}</td>
                                            <td class="p-0.5 text-center text-[11px] font-black {{ $rowIndex === 2 ? 'border-b-[2pt] border-black bg-gray-200' : '' }}">{{ \App\Helpers\NumberFormatter::format($report['rows'][$type]['average'] ?? 0) }}</td>
                                            <td class="p-0.5 text-center text-[11px] font-bold {{ $rowIndex === 2 ? 'border-b-[2pt] border-black bg-gray-200' : '' }}">{{ $report['rows'][$type]['conduct'] ?? '' }}</td>
                                            <td class="p-0.5 text-center text-[11px] {{ $rowIndex === 2 ? 'border-b-[2pt] border-black bg-gray-200 font-bold' : '' }}">{{ $report['rows'][$type]['absence'] ?? '_' }}</td>
                                            <td class="p-0.5 text-center text-[11px] font-black {{ $rowIndex === 2 ? 'border-b-[2pt] border-black bg-gray-200' : '' }}">{{ $report['rows'][$type]['rank'] ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                @elseif($isYearly)
                                    @foreach(['q1', 'q2', 's1', 'q3', 'q4', 's2', 'avg'] as $rowIndex => $type)
                                        @php $isSemM = in_array($type, ['s1', 's2']); $isAyp = ($type === 'avg'); @endphp
                                        <tr class="h-[17px]">
                                            @if($rowIndex === 0)
                                                <td rowspan="7" class="p-0.5 text-center align-middle border-b-[2pt] border-black text-sm font-black">{{ $globalIndex }}</td>
                                                <td rowspan="7" class="p-1 px-4 uppercase text-[11px] whitespace-normal leading-[1.1] break-words font-black text-left align-middle border-b-[2pt] border-black">{{ $report['student']->full_name }}</td>
                                                <td rowspan="7" class="p-0.5 text-center align-middle border-b-[2pt] border-black text-sm font-black">{{ substr($report['student']->gender, 0, 1) }}</td>
                                            @endif
                                            <td class="p-0.5 text-center text-[9px] font-bold {{ $isSemM ? 'bg-gray-100' : '' }} {{ $isAyp ? 'border-b-[2pt] border-black bg-gray-300 font-black' : '' }}">{{ $report['rows'][$type]['label'] ?? $type }}</td>
                                            @foreach($subjects as $subject)
                                                <td class="p-0.5 text-center text-[10px] {{ $isSemM ? 'bg-gray-100 font-bold' : '' }} {{ $isAyp ? 'border-b-[2pt] border-black bg-gray-300 font-black' : '' }}">
                                                    @php $sM = ($report['rows'][$type]['marks'] ?? null) ? ($report['rows'][$type]['marks'][$subject->id] ?? null) : null; @endphp
                                                    {{ \App\Helpers\NumberFormatter::format($sM) }}
                                                </td>
                                            @endforeach
                                            <td class="p-0.5 text-center font-black text-[10px] {{ $isSemM ? 'bg-gray-100' : '' }} {{ $isAyp ? 'border-b-[2pt] border-black bg-gray-300' : '' }}">{{ \App\Helpers\NumberFormatter::format($report['rows'][$type]['total'] ?? 0) }}</td>
                                            <td class="p-0.5 text-center font-black text-[10px] {{ $isSemM ? 'bg-gray-100' : '' }} {{ $isAyp ? 'border-b-[2pt] border-black bg-gray-300' : '' }}">{{ \App\Helpers\NumberFormatter::format($report['rows'][$type]['average'] ?? 0) }}</td>
                                            <td class="p-0.5 text-center font-bold text-[10px] {{ $isSemM ? 'bg-gray-100' : '' }} {{ $isAyp ? 'border-b-[2pt] border-black bg-gray-300' : '' }}">{{ $report['rows'][$type]['conduct'] ?? '' }}</td>
                                            <td class="p-0.5 text-center text-[10px] {{ $isSemM ? 'bg-gray-100' : '' }} {{ $isAyp ? 'border-b-[2pt] border-black bg-gray-300' : '' }}">{{ $report['rows'][$type]['absence'] ?? '_' }}</td>
                                            <td class="p-0.5 text-center font-black text-[10px] {{ $isSemM ? 'bg-gray-100' : '' }} {{ $isAyp ? 'border-b-[2pt] border-black bg-gray-300' : '' }}">{{ $report['rows'][$type]['rank'] ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr class="h-[27px] text-[10px]">
                                        <td class="p-0.5 text-center border-b border-black font-black">{{ $globalIndex }}</td>
                                        <td class="p-0.5 px-4 uppercase whitespace-normal leading-tight font-black border-b border-black text-left text-[10px]">{{ $report['student']->full_name }}</td>
                                        <td class="p-0.5 text-center border-b border-black font-black">{{ substr($report['student']->gender, 0, 1) }}</td>
                                        @foreach($subjects as $subject)
                                            <td class="p-0.5 text-center border-b border-black font-bold">
                                                @php $sM = $report['marks'][$subject->id] ?? null; @endphp
                                                {{ \App\Helpers\NumberFormatter::format($sM) }}
                                            </td>
                                        @endforeach
                                        <td class="p-0.5 text-center font-black border-b border-black">{{ \App\Helpers\NumberFormatter::format($report['total']) }}</td>
                                        <td class="p-0.5 text-center font-black border-b border-black">{{ \App\Helpers\NumberFormatter::format($report['average']) }}</td>
                                        <td class="p-0.5 text-center border-b border-black font-bold">{{ $report['conduct'] }}</td>
                                        <td class="p-0.5 text-center border-b border-black">{{ $report['absence'] }}</td>
                                        <td class="p-0.5 text-center font-black border-b border-black">{{ $report['rank'] }}</td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>

                    <!-- SIGNATURE FOOTER (Outside table to avoid borders) -->
                    <div class="print-footer-container {{ ($isSem || $isYearly) ? 'mt-3' : 'mt-10' }} mb-1" style="page-break-inside: avoid !important;">
                        <table style="width: 100%; border-collapse: collapse; border: none !important;">
                            <tr style="border: none !important;">
                                <td style="width: 33%; text-align: left; border: none !important; font-size: 13px; font-style: italic; vertical-align: top; color: black !important; padding: 0;">
                                    <p style="margin-bottom: 12px;">Homeroom Teacher: ________________</p>
                                    <p>Signature: __________________</p>
                                </td>
                                <td style="width: 33%; text-align: center; border: none !important; font-size: 13px; font-style: italic; vertical-align: top; color: black !important; padding: 0;">
                                    <p style="margin-bottom: 12px;">Principal: ____________________</p>
                                    <p>Signature: __________________</p>
                                </td>
                                <td style="width: 33%; text-align: right; border: none !important; font-size: 13px; font-style: italic; vertical-align: top; color: black !important; padding: 0;">
                                    <p style="margin-bottom: 12px;">Record Officer: ________________</p>
                                    <p>Signature: __________________</p>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>@endif @endforeach

        </div>
    </div>

    <style>
        .roster-table {
            border-collapse: collapse !important;
            border-spacing: 0 !important;
            width: 100%;
            border: 2pt solid black !important;
            table-layout: fixed;
        }
        .roster-table th, .roster-table td {
            border: 1pt solid black !important;
            box-sizing: border-box !important;
            background-clip: padding-box !important;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .header-row th {
            border-bottom: 2pt solid black !important;
        }
        .vertical-text {
            writing-mode: vertical-rl;
            transform: rotate(180deg);
            white-space: normal;
            word-break: break-word;
            display: inline-block;
            font-weight: 900;
            font-size: 11px;
            line-height: 0.95;
            margin: 0 auto;
            text-align: left;
            max-height: 140px;
            color: black;
            text-transform: uppercase;
            letter-spacing: 0.02em;
        }
        @media print {
            * { overflow: visible !important; scrollbar-width: none !important; -ms-overflow-style: none !important; }
            *::-webkit-scrollbar { display: none !important; }
            
            @page {
                size: {{ ($isSem || $isYearly) ? 'landscape' : 'portrait' }};
                margin: 5mm 10mm 5mm 10mm;
            }
            html, body { 
                background: white !important;
                -webkit-print-color-adjust: exact; 
                margin: 0;
                padding: 0;
                width: 100% !important;
                height: auto !important;
                overflow: visible !important;
            }
            nav, header, .no-print, [role="navigation"], .sidebar { display: none !important; height: 0 !important; width: 0 !important; visibility: hidden !important; }
            .print-container {
                display: block !important;
                width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
                background: white !important;
                overflow: visible !important;
            }
            .roster-page-chunk {
                display: block !important;
                margin: 0 !important;
                padding: 0 !important;
                clear: both !important;
            }
            .header-info-table h1 { font-size: 20pt !important; }
            .header-info-table h2 { font-size: 14pt !important; }
            .roster-table th, .roster-table td { font-size: 10px !important; padding: 2px !important; color: black !important; }
            .vertical-text { font-size: 9px !important; max-height: 110px !important; line-height: 0.9 !important; }
            .roster-table th { height: 120px !important; }
            .roster-scroll-wrapper { overflow: visible !important; width: 100% !important; }
        }
        .roster-scroll-wrapper {
            overflow-x: auto;
            width: 100%;
        }
    </style>
</x-admin-layout>
