<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Academic Excellence Report</title>
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
        .count-badge {
            display: inline-block;
            background: #e0e7ff;
            color: #4338ca;
            border: 1px solid #c7d2fe;
            padding: 1px 7px;
            border-radius: 10px;
            font-size: 7.5pt;
            font-weight: 700;
            margin-left: 8px;
            text-transform: none;
            vertical-align: middle;
        }
        .row-even {
            background-color: #f8fafc;
        }
        
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-black { font-weight: bold; }
        .page-break { page-break-after: always; }
        .avoid-break { page-break-inside: avoid; }
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
                <h2 class="report-title">Academic Excellence Summary Report</h2>
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
            No student records found with an average of 90% or above for the selected timeline and division.
        </div>
    @else
        @php
            $currentGradeId = null;
        @endphp

        @foreach($sectionsData as $index => $data)
            @if($currentGradeId !== null && $currentGradeId !== $data['grade_level']->id)
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
                            <h2 class="report-title">Academic Excellence Summary Report</h2>
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
                    <span class="count-badge">{{ $data['students']->count() }} student{{ $data['students']->count() !== 1 ? 's' : '' }}</span>
                </div>

                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 50px;">#</th>
                            <th>Student Name</th>
                            <th style="width: 100px;">Student ID</th>
                            <th class="text-center" style="width: 80px;">Gender</th>
                            <th class="text-right" style="width: 120px;">Average Score</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data['students'] as $sIndex => $student)
                            @php
                                $rowClass = ($sIndex % 2 === 0) ? 'row-even' : '';
                            @endphp
                            <tr class="{{ $rowClass }}">
                                <td class="text-center" style="color: #64748b; font-weight: bold;">
                                    {{ $sIndex + 1 }}
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

</body>
</html>
