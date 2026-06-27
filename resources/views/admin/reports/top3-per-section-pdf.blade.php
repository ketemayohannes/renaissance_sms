<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Top 3 Ranked Students Summary Report</title>
    <style>
        @page {
            margin: 40px;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 9pt;
            color: #1e293b;
            margin: 0;
            padding: 0;
            line-height: 1.4;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 10px;
        }
        .header-table td {
            border: none;
            vertical-align: middle;
        }
        .logo-container {
            width: 70px;
        }
        .logo-img {
            height: 60px;
            width: auto;
        }
        .logo-fallback {
            height: 50px;
            width: 50px;
            border: 2px solid #4f46e5;
            border-radius: 50%;
            color: #4f46e5;
            font-weight: bold;
            font-size: 8pt;
            text-align: center;
            line-height: 50px;
        }
        .school-info {
            text-align: center;
        }
        .school-name {
            font-size: 18pt;
            font-weight: 900;
            color: #0f172a;
            text-transform: uppercase;
            margin: 0;
            letter-spacing: 0.5px;
        }
        .report-title {
            font-size: 11pt;
            font-weight: bold;
            color: #475569;
            margin: 4px 0 0 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .metadata-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
        }
        .metadata-table td {
            padding: 8px 12px;
            font-size: 9pt;
            font-weight: bold;
            color: #334155;
            border: 1px solid #e2e8f0;
        }
        .section-header {
            font-size: 11pt;
            font-weight: 800;
            color: #0f172a;
            background-color: #f1f5f9;
            padding: 6px 12px;
            margin-top: 25px;
            margin-bottom: 10px;
            border-left: 4px solid #4f46e5;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .data-table th {
            background-color: #f8fafc;
            color: #475569;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 8pt;
            letter-spacing: 0.5px;
            border: 1px solid #cbd5e1;
            padding: 8px 10px;
            text-align: left;
        }
        .data-table td {
            padding: 8px 10px;
            font-size: 9.5pt;
            border: 1px solid #cbd5e1;
        }
        .rank-badge {
            display: inline-block;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            text-align: center;
            line-height: 18px;
            font-weight: bold;
            font-size: 8pt;
            color: #ffffff;
        }
        .rank-1 { background-color: #d97706; } /* Gold */
        .rank-2 { background-color: #64748b; } /* Silver */
        .rank-3 { background-color: #c2410c; } /* Bronze */
        
        .row-rank-1 { background-color: #fef3c7; }
        .row-rank-2 { background-color: #f1f5f9; }
        .row-rank-3 { background-color: #ffedd5; }
        
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-black { font-weight: bold; }
        .page-break { page-break-after: always; }
        .avoid-break { page-break-inside: avoid; }
        
        /* Summary Table Styling */
        .summary-header {
            font-size: 13pt;
            font-weight: 900;
            color: #1e1b4b;
            background-color: #e0e7ff;
            padding: 10px 15px;
            margin-top: 30px;
            margin-bottom: 15px;
            border-left: 6px solid #4338ca;
            text-transform: uppercase;
            letter-spacing: 1px;
            text-align: center;
        }
    </style>
</head>
<body>

    <!-- Header Section -->
    <table class="header-table">
        <tr>
            <td class="logo-container">
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" class="logo-img" alt="School Logo">
                @else
                    <div class="logo-fallback">RSS</div>
                @endif
            </td>
            <td class="school-info">
                <h1 class="school-name">{{ $settings->school_name ?? 'RENAISSANCE SCHOOL' }}</h1>
                <h2 class="report-title">Top 3 Ranked Students Per Section</h2>
            </td>
            <td style="width: 70px;"></td> <!-- Balance spacing -->
        </tr>
    </table>

    <!-- Metadata Section -->
    <table class="metadata-table">
        <tr>
            <td>ACADEMIC YEAR: <span style="color: #0f172a;">{{ $academicYear->name }}</span></td>
            <td>TIMELINE: <span style="color: #0f172a; text-transform: uppercase;">{{ $term->name }}</span></td>
            <td>DIVISION: <span style="color: #0f172a; text-transform: uppercase;">{{ $division->name }}</span></td>
        </tr>
    </table>

    @if(empty($sectionsData))
        <div style="text-align: center; padding: 40px; color: #64748b; font-style: italic; border: 1px dashed #cbd5e1; border-radius: 12px; margin-top: 30px;">
            No student ranking records found for the selected timeline and division.
        </div>
    @else
        @php
            $currentGradeId = null;
        @endphp

        @foreach($sectionsData as $index => $data)
            @if($currentGradeId !== null && $currentGradeId !== $data['grade_level']->id)
                <div class="page-break"></div>
                <!-- Re-render header on new pages if needed, but since it's section by section, simple page-breaks between grade levels are very clean -->
                <table class="header-table" style="margin-top: 10px;">
                    <tr>
                        <td class="logo-container">
                            @if($logoBase64)
                                <img src="{{ $logoBase64 }}" class="logo-img" alt="School Logo">
                            @else
                                <div class="logo-fallback">RSS</div>
                            @endif
                        </td>
                        <td class="school-info">
                            <h1 class="school-name">{{ $settings->school_name ?? 'RENAISSANCE SCHOOL' }}</h1>
                            <h2 class="report-title">Top 3 Ranked Students Per Section</h2>
                        </td>
                        <td style="width: 70px;"></td>
                    </tr>
                </table>
            @endif

            @php
                $currentGradeId = $data['grade_level']->id;
            @endphp

            <div class="avoid-break">
                <div class="section-header">
                    {{ $data['grade_level']->name }} &mdash; {{ $data['section']->name }}
                </div>

                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 50px;">Rank</th>
                            <th>Student Name</th>
                            <th style="width: 100px;">Student ID</th>
                            <th class="text-center" style="width: 80px;">Gender</th>
                            <th class="text-right" style="width: 120px;">Average Score</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data['students'] as $sIndex => $student)
                            @php
                                $rank = $sIndex + 1;
                                $rowClass = 'row-rank-' . $rank;
                                $badgeClass = 'rank-' . $rank;
                            @endphp
                            <tr class="{{ $rowClass }}">
                                <td class="text-center">
                                    <span class="rank-badge {{ $badgeClass }}">{{ $rank }}</span>
                                </td>
                                <td class="font-black">
                                    {{ $student->first_name }} {{ $student->father_name }} {{ $student->grandfather_name }}
                                </td>
                                <td>{{ $student->student_id }}</td>
                                <td class="text-center">{{ $student->gender }}</td>
                                <td class="text-right font-black">{{ number_format($student->average_score, 2) }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach
    @endif

    <!-- Page Break before Summary Table -->
    @if(!empty($gradeSummaryData))
        <div class="page-break"></div>
        <table class="header-table" style="margin-top: 10px;">
            <tr>
                <td class="logo-container">
                    @if($logoBase64)
                        <img src="{{ $logoBase64 }}" class="logo-img" alt="School Logo">
                    @else
                        <div class="logo-fallback">RSS</div>
                    @endif
                </td>
                <td class="school-info">
                    <h1 class="school-name">{{ $settings->school_name ?? 'RENAISSANCE SCHOOL' }}</h1>
                    <h2 class="report-title">Academic Honor Roll</h2>
                </td>
                <td style="width: 70px;"></td>
            </tr>
        </table>

        <div class="summary-header">
            Grade Level Honor Roll Summary (Top 3 Per Grade)
        </div>

        @foreach($gradeSummaryData as $summary)
            <div class="avoid-break">
                <div class="section-header" style="border-left-color: #4338ca; background-color: #e0e7ff/30;">
                    {{ $summary['grade_level']->name }} &mdash; Top Achievers
                </div>

                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 50px;">Rank</th>
                            <th>Student Name</th>
                            <th style="width: 100px;">Student ID</th>
                            <th class="text-center" style="width: 100px;">Section</th>
                            <th class="text-center" style="width: 80px;">Gender</th>
                            <th class="text-right" style="width: 120px;">Average Score</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($summary['students'] as $sIndex => $student)
                            @php
                                $rank = $sIndex + 1;
                                $rowClass = 'row-rank-' . $rank;
                                $badgeClass = 'rank-' . $rank;
                            @endphp
                            <tr class="{{ $rowClass }}">
                                <td class="text-center">
                                    <span class="rank-badge {{ $badgeClass }}">{{ $rank }}</span>
                                </td>
                                <td class="font-black">
                                    {{ $student->first_name }} {{ $student->father_name }} {{ $student->grandfather_name }}
                                </td>
                                <td>{{ $student->student_id }}</td>
                                <td class="text-center">{{ $student->section_name }}</td>
                                <td class="text-center">{{ $student->gender }}</td>
                                <td class="text-right font-black">{{ number_format($student->average_score, 2) }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach
    @endif

</body>
</html>
