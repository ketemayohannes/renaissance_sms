<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center no-print">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Academic Report: ') }} {{ $section->name }} - {{ $term->name }}
            </h2>
            <button onclick="window.print()" class="bg-gray-800 text-white px-4 py-2 rounded text-sm hover:bg-gray-700">
                Print Report
            </button>
        </div>
    </x-slot>

    <div class="py-12 px-2 sm:px-4 print:py-0 print:px-0 print:bg-white">
        <div class="max-w-[100%] mx-auto bg-white p-4 sm:p-8 shadow-sm print:shadow-none print:px-10 print:py-8 print-container">
            <x-breadcrumb :items="[
                ['label' => 'Academic Reports', 'url' => route('admin.academic-reports.index')],
                ['label' => 'View Report', 'url' => '#']
            ]" class="no-print mb-6" />

            @php
                $isSem = $term->isSemester();
                $isYearly = ($term->type === 'yearly');
                $subjectCount = count($subjects);
                // Fixed categories: S.no(3%), Name(35%), Gender(3% if !sem), Quarter(6% if sem or yearly), Total(5%), Avg(5%), Cond(3%), Abs(3%), Rank(4%)
                $fixedWidth = 3 + 35 + ($isYearly ? 9 : ($isSem ? 6 : 3)) + 5 + 5 + 3 + 3 + 4;
                $subjectWidth = ($subjectCount > 0) ? (100 - $fixedWidth) / $subjectCount : 0;
            @endphp


            <!-- Header Section (Outside Table) -->
            <div class="relative mb-2 print:mt-0 mt-8">
                <!-- Logo -->
                <div class="absolute -left-2 -top-10 logo-container">
                    <div class="inline-block">
                        @if($settings && $settings->roster_logo_path)
                            <img src="{{ asset('storage/' . $settings->roster_logo_path) }}" alt="Roster Logo" class="h-24 w-auto object-contain">
                        @elseif($generalSettings && $generalSettings->logo_path)
                            <img src="{{ asset('storage/' . $generalSettings->logo_path) }}" alt="General Logo" class="h-24 w-auto object-contain">
                        @else
                            <div class="h-20 w-20 bg-white border-4 border-blue-900 rounded-full flex items-center justify-center text-[10px] font-bold text-center leading-tight shadow-md">
                                Renaissance<br>School
                            </div>
                        @endif
                    </div>
                </div>

                <div class="text-center pt-2">
                    <h1 class="text-2xl font-serif font-bold tracking-tight uppercase leading-none">{{ $settings->school_name ?? ($generalSettings->school_name ?? 'RENAISSANCE SCHOOL') }}</h1>
                    <h2 class="text-xl font-serif mt-1">{{ $term->name }} Roster Report</h2>
                </div>

                <div class="mt-4 flex justify-between text-sm sm:text-base font-serif px-1 roster-info-line">
                    <div class="w-1/3 text-left whitespace-nowrap">Grade: {{ str_replace('Grade ', '', $section->gradeLevel->name) }}</div>
                    <div class="w-1/3 text-center whitespace-nowrap">Section: {{ $section->name }}</div>
                    <div class="w-1/3 text-right whitespace-nowrap">Academic Year: {{ $academicYear->name }} G.C {{ \App\Helpers\EthiopianDateHelper::fromGregorian($academicYear->start_date)->format('Y') }} E.C</div>
                </div>
            </div>

            <!-- Table Section -->
            <div class="overflow-x-auto">
                <table class="roster-table mx-auto text-[10px] sm:text-[11px] font-sans table-fixed whitespace-nowrap w-full">
                    <!-- Explicit Column Widths -->
                    <colgroup>
                        <col style="width: 3%;"><!-- S.no -->
                        <col style="width: 35%;"><!-- Student Full Name -->
                        @if(!$isSem)
                        <col style="width: 3%;"><!-- Gender -->
                        @endif
                        @if($isSem || $isYearly)
                        <col style="width: 6%;"><!-- Quarter/Term -->
                        @endif
                        @foreach($subjects as $subject)
                        <col style="width: {{ $subjectWidth }}%;"><!-- Subject -->
                        @endforeach
                        <col style="width: 5%;"><!-- Total -->
                        <col style="width: 5%;"><!-- Average -->
                        <col style="width: 3%;"><!-- Conduct -->
                        <col style="width: 3%;"><!-- Absence -->
                        <col style="width: 4%;"><!-- Rank -->
                    </colgroup>
                    <thead>
                        <!-- Spacer Row for Print Spacing -->
                        <tr class="print-spacer hidden print:table-row" style="height: 10mm; border: none !important;">
                            <td colspan="{{ 8 + $subjects->count() }}" style="border: none !important; border-bottom: 1.5pt solid black !important; padding: 0;"></td>
                        </tr>
                        <!-- Column Headers -->
                        <tr class="header-row">
                            <th class="p-0 w-[3%] h-24 relative text-center">
                                <div class="vertical-text">S.no</div>
                            </th>
                            <th class="p-1 text-center font-bold text-sm bg-white" style="width: 35% !important;">
                                Student Full Name
                            </th>
                            @if(!$isSem)
                            <th class="p-0 w-[3%] h-24 relative text-center">
                                <div class="vertical-text">Gender</div>
                            </th>
                            @endif
                            @if($isSem || $isYearly)
                            <th class="p-0 w-[6%] h-24 relative text-center">
                                <div class="vertical-text">Term</div>
                            </th>
                            @endif
                            @foreach($subjects as $subject)
                                <th class="p-0 h-24 relative text-center align-bottom pb-2" style="width: {{ $subjectWidth }}%">
                                    <div class="vertical-text">
                                        {{ $subject->name }}
                                    </div>
                                </th>
                            @endforeach
                            <th class="p-0 w-[5%] h-24 relative text-center">
                                <div class="vertical-text">Total</div>
                            </th>
                            <th class="p-0 w-[5%] h-24 relative text-center">
                                <div class="vertical-text">Average</div>
                            </th>
                            <th class="p-0 w-[3%] h-24 relative text-center">
                                <div class="vertical-text">Conduct</div>
                            </th>
                            <th class="p-0 w-[3%] h-24 relative text-center">
                                <div class="vertical-text">Absence</div>
                            </th>
                            <th class="p-0 w-[4%] h-24 relative text-center rounded-tr-lg">
                                <div class="vertical-text">Rank</div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reports as $index => $report)
                            @if($isSem)
                                {{-- Semester Layout: 3 rows per student --}}
                                @php
                                    $rowTypes = ['q1', 'q2', 'avg'];
                                    $rowLabels = ['Quarter I', 'Quarter II', 'Sem Avg'];
                                @endphp
                                @foreach($rowTypes as $rowIndex => $type)
                                    <tr class="h-6">
                                        @if($rowIndex === 0)
                                            <td rowspan="3" class="p-0.5 text-center align-middle border-b-[1.5pt] border-black text-sm font-bold">{{ $index + 1 }}</td>
                                            <td rowspan="3" class="p-0.5 px-2 uppercase text-[11px] whitespace-normal leading-[1.1] break-words font-bold text-center align-middle border-b-[1.5pt] border-black">{{ $report['student']->full_name }}</td>
                                        @endif
                                        <td class="p-0.5 text-center text-[9px] font-semibold {{ $rowIndex === 2 ? 'border-b-[1.5pt] border-black bg-[#eee] font-bold' : '' }}">
                                            {{ $report['rows'][$type]['label'] ?? $rowLabels[$rowIndex] }}
                                        </td>
                                        
                                        @foreach($subjects as $subject)
                                            <td class="p-0.5 text-center {{ $rowIndex === 2 ? 'border-b-[1.5pt] border-black bg-[#eee] font-bold' : '' }}">
                                                @php $score = $report['rows'][$type]['marks'][$subject->id] ?? null; @endphp
                                                {{ $score !== null ? (is_numeric($score) ? number_format($score, 1) : $score) : '-' }}
                                            </td>
                                        @endforeach

                                        <td class="p-0.5 text-center font-bold {{ $rowIndex === 2 ? 'border-b-[1.5pt] border-black bg-[#eee]' : '' }}">
                                            {{ number_format($report['rows'][$type]['total'] ?? 0, 1) }}
                                        </td>
                                        <td class="p-0.5 text-center font-bold {{ $rowIndex === 2 ? 'border-b-[1.5pt] border-black bg-[#eee]' : '' }}">
                                            {{ number_format($report['rows'][$type]['average'] ?? 0, 2) }}
                                        </td>
                                        <td class="p-0.5 text-center {{ $rowIndex === 2 ? 'border-b-[1.5pt] border-black bg-[#eee]' : '' }}">
                                            {{ $report['rows'][$type]['conduct'] ?? '' }}
                                        </td>
                                        <td class="p-0.5 text-center {{ $rowIndex === 2 ? 'border-b-[1.5pt] border-black bg-[#eee]' : '' }}">
                                            {{ $report['rows'][$type]['absence'] ?? '' }}
                                        </td>
                                        <td class="p-0.5 text-center font-bold {{ $rowIndex === 2 ? 'border-b-[1.5pt] border-black bg-[#eee]' : '' }}">
                                            {{ $report['rows'][$type]['rank'] ?? '-' }}
                                        </td>
                                </tr>
                                @endforeach
                            @elseif($isYearly)
                                {{-- Yearly Layout: 7 rows per student (Q1, Q2, Sem1, Q3, Q4, Sem2, YearAvg) --}}
                                @php
                                    $rowTypes = ['q1', 'q2', 's1', 'q3', 'q4', 's2', 'avg'];
                                @endphp
                                @foreach($rowTypes as $rowIndex => $type)
                                    @php 
                                        $isSemAvgRow = in_array($type, ['s1', 's2']);
                                        $isYearAvgRow = ($type === 'avg');
                                    @endphp
                                    <tr class="h-5">
                                        @if($rowIndex === 0)
                                            <td rowspan="7" class="p-0.5 text-center align-middle border-b-[1.5pt] border-black text-sm font-bold">{{ $index + 1 }}</td>
                                            <td rowspan="7" class="p-0.5 px-2 uppercase text-[10px] whitespace-normal leading-[1.1] break-words font-bold text-center align-middle border-b-[1.5pt] border-black">{{ $report['student']->full_name }}</td>
                                            <td rowspan="7" class="p-0.5 text-center align-middle border-b-[1.5pt] border-black text-sm font-bold">{{ substr($report['student']->gender, 0, 1) }}</td>
                                        @endif
                                        <td class="p-0.5 text-center text-[8px] font-semibold {{ $isSemAvgRow ? 'bg-[#ddd]' : '' }} {{ $isYearAvgRow ? 'border-b-[1.5pt] border-black bg-[#bbb] font-bold' : '' }}">
                                            {{ $report['rows'][$type]['label'] ?? $type }}
                                        </td>
                                        
                                        @foreach($subjects as $subject)
                                            <td class="p-0.5 text-center {{ $isSemAvgRow ? 'bg-[#ddd]' : '' }} {{ $isYearAvgRow ? 'border-b-[1.5pt] border-black bg-[#bbb]' : '' }}">
                                                @php $score = $report['rows'][$type]['marks'][$subject->id] ?? null; @endphp
                                                {{ $score !== null ? (is_numeric($score) ? number_format($score, 1) : $score) : '-' }}
                                            </td>
                                        @endforeach
                                        <td class="p-0.5 text-center font-bold {{ $isSemAvgRow ? 'bg-[#ddd]' : '' }} {{ $isYearAvgRow ? 'border-b-[1.5pt] border-black bg-[#bbb]' : '' }}">
                                            {{ number_format($report['rows'][$type]['total'] ?? 0, 1) }}
                                        </td>
                                        <td class="p-0.5 text-center font-bold {{ $isSemAvgRow ? 'bg-[#ddd]' : '' }} {{ $isYearAvgRow ? 'border-b-[1.5pt] border-black bg-[#bbb]' : '' }}">
                                            {{ number_format($report['rows'][$type]['average'] ?? 0, 2) }}
                                        </td>
                                        <td class="p-0.5 text-center {{ $isSemAvgRow ? 'bg-[#ddd]' : '' }} {{ $isYearAvgRow ? 'border-b-[1.5pt] border-black bg-[#bbb]' : '' }}">
                                            {{ $report['rows'][$type]['conduct'] ?? '' }}
                                        </td>
                                        <td class="p-0.5 text-center {{ $isSemAvgRow ? 'bg-[#ddd]' : '' }} {{ $isYearAvgRow ? 'border-b-[1.5pt] border-black bg-[#bbb]' : '' }}">
                                            {{ $report['rows'][$type]['absence'] ?? '_' }}
                                        </td>
                                        <td class="p-0.5 text-center font-bold {{ $isSemAvgRow ? 'bg-[#ddd]' : '' }} {{ $isYearAvgRow ? 'border-b-[1.5pt] border-black bg-[#bbb]' : '' }}">
                                            {{ $report['rows'][$type]['rank'] ?? '-' }}
                                    </tr>
                                @endforeach
                                {{-- Page break logic: 3 students on first page, then 4 students per page --}}
                                @php
                                    $shouldBreak = false;
                                    $studentNum = $index + 1;
                                    if ($studentNum == 3) {
                                        $shouldBreak = true; // Break after 3rd student (end of page 1)
                                    } elseif ($studentNum > 3 && ($studentNum - 3) % 4 == 0) {
                                        $shouldBreak = true; // Break after every 4th student subsequently (3+4, 3+8, etc.)
                                    }
                                @endphp

                                @if($shouldBreak && $studentNum < count($reports))
                                    <tr class="page-break-after"><td colspan="{{ 7 + count($subjects) }}"></td></tr>
                                @endif
                            @else
                                {{-- Existing Quarter Layout --}}
                                <tr class="h-7">
                                    <td class="p-0.5 text-center">{{ $index + 1 }}</td>
                                    <td class="p-0.5 px-1 uppercase text-[10px] whitespace-normal leading-[1.1] break-words">{{ $report['student']->full_name }}</td>
                                    <td class="p-0.5 text-center">{{ substr($report['student']->gender, 0, 1) }}</td>
                                    @foreach($subjects as $subject)
                                        <td class="p-0.5 text-center">
                                            @php $score = $report['marks'][$subject->id] ?? null; @endphp
                                            {{ $score !== null ? (is_numeric($score) ? number_format($score, 1) : $score) : '-' }}
                                        </td>
                                    @endforeach
                                    <td class="p-0.5 text-center font-bold">{{ number_format($report['total'], 1) }}</td>
                                    <td class="p-0.5 text-center font-bold">{{ number_format($report['average'], 2) }}</td>
                                    <td class="p-0.5 text-center">{{ $report['conduct'] }}</td>
                                    <td class="p-0.5 text-center">{{ $report['absence'] }}</td>
                                    <td class="p-0.5 text-center font-bold">{{ $report['rank'] }}</td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>

                {{-- Signatures Section (After Table - End of Document) --}}
                <div id="roster-signatures" class="mt-4 flex justify-between font-serif text-[10px] italic print-footer w-full break-inside-avoid">
                    <div class="w-1/3 text-left whitespace-nowrap">
                        <p>Homeroom Teacher's Name:______________</p>
                        <p class="mt-1">Signature:____________</p>
                    </div>
                    <div class="w-1/3 text-center whitespace-nowrap">
                        <p>Principal's Name:______________</p>
                        <p class="mt-1">Signature:____________</p>
                    </div>
                    <div class="w-1/3 text-right whitespace-nowrap">
                        <p>Record Officer's Name:______________</p>
                        <p class="mt-1">Signature:____________</p>
                    </div>
                </div>
            </div>

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
        }
        /* SPECIFIC OVERRIDE FOR THE FOOTER AREA */
        #roster-footer-cell {
            border: none !important;
            padding: 0 !important;
            background-color: transparent !important;
        }
        #roster-signatures {
            border: none !important;
            width: 100% !important;
            display: flex !important;
            justify-content: space-between !important;
        }
        /* BORDERLESS MAIN HEADER ROW (School name, logo, info) */
        .roster-table thead tr.border-none,
        .roster-table thead tr.border-none td {
            border: none !important;
            background-color: white !important;
        }
        .header-row {
            background-color: #ccc !important;
        }
        .roster-table tr {
            min-height: 24px;
        }
        .roster-table th {
            height: 96px; /* 24 units */
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
            @page {
                size: {{ ($isSem || $isYearly) ? 'landscape' : 'portrait' }};
                margin: 0; /* Hides browser URLs and page numbers */
            }
            body { 
                background: white !important;
                -webkit-print-color-adjust: exact; 
            }
            nav, header, .no-print { display: none !important; }
            .py-12 { padding: 0 !important; }
            .mt-12, .mb-6 { margin: 0 !important; }
            .print-container {
                display: block !important;
                width: 100% !important;
                padding: 10mm 15mm !important; /* Restore padding for main page content */
                margin: 0 !important;
                box-sizing: border-box !important;
                background: white !important;
            }
            
            thead {
                display: table-header-group !important;
            }
            tfoot {
                display: table-footer-group !important;
            }

            .page-break-after td {
                page-break-after: always !important;
                height: 0 !important;
                padding: 0 !important;
                line-height: 0 !important;
                border: none !important;
                visibility: hidden !important;
            }

            .print-footer {
                padding-top: 0 !important;
                margin-top: 5mm !important;
                page-break-inside: avoid !important;
                font-size: 7pt !important;
                white-space: nowrap !important;
                border: none !important;
            }
            
            .logo-container { left: 2mm !important; top: -8mm !important; }
            
            .roster-info-line { 
                padding-left: 0 !important; 
                padding-right: 0 !important;
                font-size: 11pt !important;
            }
            
            table.roster-table {
                width: 100% !important;
                border: none !important;
                border-collapse: collapse !important;
            }
            
            .roster-table th, .roster-table td {
                padding: 1px !important;
                font-size: 8.5px !important;
                border: 0.75pt solid black !important;
                background-clip: padding-box !important;
                page-break-inside: avoid !important;
            }

            /* ENSURE FOOTER REMAINS BORDERLESS AND ALIGNED IN PRINT */
            #roster-footer-cell {
                border: none !important;
                background-color: transparent !important;
                display: table-cell !important;
                padding-top: 10mm !important;
            }

            #roster-signatures {
                border: none !important;
                background-color: transparent !important;
                display: flex !important;
                justify-content: space-between !important;
                width: 100% !important;
            }

            .print-tfoot, .print-tfoot tr {
                border: none !important;
                background-color: transparent !important;
            }
            
            tr {
                page-break-inside: avoid !important;
                page-break-after: auto !important;
            }
            
            .header-row {
                background-color: #ccc !important;
            }

            .vertical-text {
                font-size: 7.5px !important;
                max-height: 85px !important;
                white-space: nowrap !important;
            }

            .roster-table th {
                height: 90px !important;
            }

            .overflow-x-auto {
                overflow: visible !important;
            }
        }
    </style>
</x-app-layout>
