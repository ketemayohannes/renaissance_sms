<x-admin-layout>
    <x-slot name="header">Academic Report: {{ $section->gradeLevel->name }} - {{ $section->name }} - {{ $term->name }}</x-slot>

    <div class="space-y-6">
        <div class="flex justify-between items-center bg-white p-4 rounded-lg shadow-sm border border-slate-200 no-print">
            <div>
                <h2 class="text-lg font-bold text-slate-800">Academic Roster Report</h2>
                <p class="text-sm text-slate-500">{{ $section->gradeLevel->name }} - {{ $section->name }} - {{ $term->name }}</p>
            </div>
            <button onclick="window.print()" class="btn-primary">
                Print Report
            </button>
        </div>

        <x-breadcrumb :items="[
            ['label' => 'Academic Reports', 'url' => route('admin.academic-reports.index')],
            ['label' => 'View Report', 'url' => '#']
        ]" class="no-print" />


        <div class="max-w-[100%] mx-auto bg-white p-4 sm:p-8 shadow-sm print:shadow-none print:p-0 print-container">


            @php
                $isSem = $term->isSemester();
                $isYearly = ($term->type === 'yearly');
                $subjectCount = count($subjects);
                // Width calculations
                $fixedWidth = 3 + 32 + 3 + ($isYearly || $isSem ? 6 : 0) + 5 + 5 + 3 + 3 + 4;
                $subjectWidth = ($subjectCount > 0) ? (100 - $fixedWidth) / $subjectCount : 0;
                
                // Chunking logic for robust multi-page printing
                // Increased p1Size slightly as we have more vertical space now
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
            <div class="roster-page-chunk" style="{{ $loop->last ? '' : 'page-break-after: always;' }} margin-bottom: 5px; clear: both; display: block; width: 100%;">
                
                <!-- STABLE TABLE-BASED HEADER (Repeated) -->
                <table class="w-full header-info-table" style="border: none !important; margin-bottom: 8px; width: 100%; border-collapse: collapse;">
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
                            <h1 style="font-family: serif; font-weight: 800; font-size: 19pt; margin: 0; color: black; text-transform: uppercase; line-height: 1.0;">
                                {{ $settings->school_name ?? ($generalSettings->school_name ?? 'RENAISSANCE SCHOOL') }}
                            </h1>
                            <h2 style="font-family: serif; font-size: 14pt; margin: 2px 0 0 0; color: black; font-weight: normal;">
                                {{ $term->name }} Roster Report
                            </h2>
                        </td>
                        <td style="width: 25%; border: none !important; vertical-align: middle; text-align: right; padding: 5px;">
                            <!-- Empty right column to keep school name perfectly centered -->
                        </td>
                    </tr>
                    <tr style="border: none !important;">
                        <td colspan="3" style="border: none !important; padding-top: 5px;">
                            <table style="width: 100%; border: none !important;">
                                <tr style="border: none !important;">
                                    <td style="width: 25%; border: none !important; text-align: left; font-size: 12pt; font-family: serif;">Grade: {{ str_replace('Grade ', '', $section->gradeLevel->name) }}</td>
                                    <td style="width: 50%; border: none !important; text-align: center; font-size: 12pt; font-family: serif;">Section: {{ $section->name }}</td>
                                    <td style="width: 25%; border: none !important; text-align: right; font-size: 12pt; font-family: serif; white-space: nowrap;">
                                        Academic Year: {{ $academicYear->name }} G.C {{ \App\Helpers\EthiopianDateHelper::fromGregorian($academicYear->start_date)->format('Y') }} E.C
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
                                <th class="p-1 text-center font-bold text-sm bg-white" style="width: 32% !important;">Student Full Name</th>
                                <th class="p-0 text-center"><div class="vertical-text">Gender</div></th>
                                @if($isSem || $isYearly)
                                <th class="p-0 text-center"><div class="vertical-text">Term</div></th>
                                @endif
                                @foreach($subjects as $subject)
                                    <th class="p-0 h-24 relative text-center align-bottom pb-2">
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
                                        <tr class="h-6">
                                            @if($rowIndex === 0)
                                                <td rowspan="3" class="p-0.5 text-center align-middle border-b-[1.5pt] border-black text-sm font-bold">{{ $globalIndex }}</td>
                                                <td rowspan="3" class="p-0.5 px-2 uppercase text-[11px] whitespace-normal leading-[1.1] break-words font-bold text-center align-middle border-b-[1.5pt] border-black">{{ $report['student']->full_name }}</td>
                                                <td rowspan="3" class="p-0.5 text-center align-middle border-b-[1.5pt] border-black text-sm font-bold">{{ substr($report['student']->gender, 0, 1) }}</td>
                                            @endif
                                            <td class="p-0.5 text-center text-[9px] font-semibold {{ $rowIndex === 2 ? 'border-b-[1.5pt] border-black bg-[#eee] font-bold' : '' }}">
                                                {{ $report['rows'][$type]['label'] ?? '' }}
                                            </td>
                                            @foreach($subjects as $subject)
                                                <td class="p-0.5 text-center {{ $rowIndex === 2 ? 'border-b-[1.5pt] border-black bg-[#eee] font-bold' : '' }}">
                                                    @php $sM = ($report['rows'][$type]['marks'] ?? null) ? ($report['rows'][$type]['marks'][$subject->id] ?? null) : null; @endphp
                                                    {{ \App\Helpers\NumberFormatter::format($sM) }}
                                                </td>
                                            @endforeach
                                            <td class="p-0.5 text-center font-bold {{ $rowIndex === 2 ? 'border-b-[1.5pt] border-black bg-[#eee]' : '' }}">{{ \App\Helpers\NumberFormatter::format($report['rows'][$type]['total'] ?? 0) }}</td>
                                            <td class="p-0.5 text-center font-bold {{ $rowIndex === 2 ? 'border-b-[1.5pt] border-black bg-[#eee]' : '' }}">{{ \App\Helpers\NumberFormatter::format($report['rows'][$type]['average'] ?? 0) }}</td>
                                            <td class="p-0.5 text-center {{ $rowIndex === 2 ? 'border-b-[1.5pt] border-black bg-[#eee]' : '' }}">{{ $report['rows'][$type]['conduct'] ?? '' }}</td>
                                            <td class="p-0.5 text-center {{ $rowIndex === 2 ? 'border-b-[1.5pt] border-black bg-[#eee]' : '' }}">{{ $report['rows'][$type]['absence'] ?? '_' }}</td>
                                            <td class="p-0.5 text-center font-bold {{ $rowIndex === 2 ? 'border-b-[1.5pt] border-black bg-[#eee]' : '' }}">{{ $report['rows'][$type]['rank'] ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                @elseif($isYearly)
                                    @foreach(['q1', 'q2', 's1', 'q3', 'q4', 's2', 'avg'] as $rowIndex => $type)
                                        @php $isSemM = in_array($type, ['s1', 's2']); $isAyp = ($type === 'avg'); @endphp
                                        <tr class="h-5">
                                            @if($rowIndex === 0)
                                                <td rowspan="7" class="p-0.5 text-center align-middle border-b-[1.5pt] border-black text-sm font-bold">{{ $globalIndex }}</td>
                                                <td rowspan="7" class="p-0.5 px-2 uppercase text-[10px] whitespace-normal leading-[1.1] break-words font-bold text-center align-middle border-b-[1.5pt] border-black">{{ $report['student']->full_name }}</td>
                                                <td rowspan="7" class="p-0.5 text-center align-middle border-b-[1.5pt] border-black text-sm font-bold">{{ substr($report['student']->gender, 0, 1) }}</td>
                                            @endif
                                            <td class="p-0.5 text-center text-[8px] font-semibold {{ $isSemM ? 'bg-[#ddd]' : '' }} {{ $isAyp ? 'border-b-[1.5pt] border-black bg-[#bbb] font-bold' : '' }}">{{ $report['rows'][$type]['label'] ?? $type }}</td>
                                            @foreach($subjects as $subject)
                                                <td class="p-0.5 text-center {{ $isSemM ? 'bg-[#ddd]' : '' }} {{ $isAyp ? 'border-b-[1.5pt] border-black bg-[#bbb] font-bold' : '' }}">
                                                    @php $sM = ($report['rows'][$type]['marks'] ?? null) ? ($report['rows'][$type]['marks'][$subject->id] ?? null) : null; @endphp
                                                    {{ \App\Helpers\NumberFormatter::format($sM) }}
                                                </td>
                                            @endforeach
                                            <td class="p-0.5 text-center font-bold {{ $isSemM ? 'bg-[#ddd]' : '' }} {{ $isAyp ? 'border-b-[1.5pt] border-black bg-[#bbb]' : '' }}">{{ \App\Helpers\NumberFormatter::format($report['rows'][$type]['total'] ?? 0) }}</td>
                                            <td class="p-0.5 text-center font-bold {{ $isSemM ? 'bg-[#ddd]' : '' }} {{ $isAyp ? 'border-b-[1.5pt] border-black bg-[#bbb]' : '' }}">{{ \App\Helpers\NumberFormatter::format($report['rows'][$type]['average'] ?? 0) }}</td>
                                            <td class="p-0.5 text-center {{ $isSemM ? 'bg-[#ddd]' : '' }} {{ $isAyp ? 'border-b-[1.5pt] border-black bg-[#bbb]' : '' }}">{{ $report['rows'][$type]['conduct'] ?? '' }}</td>
                                            <td class="p-0.5 text-center {{ $isSemM ? 'bg-[#ddd]' : '' }} {{ $isAyp ? 'border-b-[1.5pt] border-black bg-[#bbb]' : '' }}">{{ $report['rows'][$type]['absence'] ?? '_' }}</td>
                                            <td class="p-0.5 text-center font-bold {{ $isSemM ? 'bg-[#ddd]' : '' }} {{ $isAyp ? 'border-b-[1.5pt] border-black bg-[#bbb]' : '' }}">{{ $report['rows'][$type]['rank'] ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr class="h-7 text-xs">
                                        <td class="p-1 text-center border-b border-gray-300 font-bold">{{ $globalIndex }}</td>
                                        <td class="p-1 px-2 uppercase text-[11px] whitespace-normal leading-tight font-semibold border-b border-gray-300">{{ $report['student']->full_name }}</td>
                                        <td class="p-1 text-center border-b border-gray-300">{{ substr($report['student']->gender, 0, 1) }}</td>
                                        @foreach($subjects as $subject)
                                            <td class="p-1 text-center border-b border-gray-300">
                                                @php $sM = $report['marks'][$subject->id] ?? null; @endphp
                                                {{ \App\Helpers\NumberFormatter::format($sM) }}
                                            </td>
                                        @endforeach
                                        <td class="p-1 text-center font-bold border-b border-gray-300">{{ \App\Helpers\NumberFormatter::format($report['total']) }}</td>
                                        <td class="p-1 text-center font-bold border-b border-gray-300">{{ \App\Helpers\NumberFormatter::format($report['average']) }}</td>
                                        <td class="p-1 text-center border-b border-gray-300">{{ $report['conduct'] }}</td>
                                        <td class="p-1 text-center border-b border-gray-300">{{ $report['absence'] }}</td>
                                        <td class="p-1 text-center font-bold border-b border-gray-300">{{ $report['rank'] }}</td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="print-footer-row" style="page-break-inside: avoid !important; border: none !important;">
                                <td colspan="{{ $totalCols }}" style="border: none !important; padding-top: 30px !important;">
                                    <table style="width: 100%; border-collapse: collapse; border: none !important;">
                                        <tr style="border: none !important;">
                                            <td style="width: 33%; text-align: left; border: none !important; font-size: 15.5px; font-style: italic; vertical-align: top; color: black !important; padding: 0;">
                                                <p>Homeroom Teacher's Name: ____________________</p>
                                                <p style="margin-top: 10px;">Signature: __________________</p>
                                            </td>
                                            <td style="width: 33%; text-align: center; border: none !important; font-size: 15.5px; font-style: italic; vertical-align: top; color: black !important; padding: 0;">
                                                <p>Principal's Name: ____________________</p>
                                                <p style="margin-top: 10px;">Signature: __________________</p>
                                            </td>
                                            <td style="width: 33%; text-align: right; border: none !important; font-size: 15.5px; font-style: italic; vertical-align: top; color: black !important; padding: 0;">
                                                <p>Record Officer's Name: ____________________</p>
                                                <p style="margin-top: 10px;">Signature: __________________</p>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            @endif
            @endforeach

        </div>
    </div>

    <style>
        .roster-table {
            border-collapse: collapse !important;
            border-spacing: 0 !important;
            width: 100%;
            border: none !important;
            table-layout: fixed;
        }
        .roster-table th, .roster-table td {
            border: 1px solid black !important;
            box-sizing: border-box !important;
            background-clip: padding-box !important;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .header-row {
            background-color: #eee !important;
        }
        .vertical-text {
            writing-mode: vertical-rl;
            transform: rotate(180deg);
            white-space: nowrap;
            display: inline-block;
            font-weight: bold;
            font-size: 11px;
            margin: 0 auto;
            text-align: left;
            max-height: 120px;
        }
        @media print {
            * { overflow: visible !important; scrollbar-width: none !important; -ms-overflow-style: none !important; }
            *::-webkit-scrollbar { display: none !important; }
            
            @page {
                size: {{ ($isSem || $isYearly) ? 'landscape' : 'portrait' }};
                margin: 5mm 10mm;
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
                page-break-after: always !important;
                margin: 0 !important;
                padding: 0 !important;
                clear: both !important;
            }
            .header-info-table h1 { font-size: 18pt !important; }
            .header-info-table h2 { font-size: 14pt !important; }
            .header-row { background-color: #ddd !important; }
            .roster-table th, .roster-table td { font-size: 8.5px !important; padding: 1px !important; }
            .vertical-text { font-size: 8px !important; max-height: 90px !important; }
            .roster-table th { height: 95px !important; }
            .roster-scroll-wrapper { overflow: visible !important; width: 100% !important; }
        }
        .roster-scroll-wrapper {
            overflow-x: auto;
            width: 100%;
        }
    </style>
</x-admin-layout>
