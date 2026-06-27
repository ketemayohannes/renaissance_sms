<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academic Excellence Report</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 9pt;
            color: #1e293b;
            background: #ffffff;
            padding: 20px;
        }

        /* ── Header ── */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .logo-container { width: 70px; vertical-align: middle; }
        .logo-img       { width: 60px; height: 60px; object-fit: contain; }
        .logo-fallback  {
            width: 60px; height: 60px;
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            color: white; font-weight: 900; font-size: 14pt; text-align: center;
            line-height: 60px;
        }
        .school-info    { vertical-align: middle; text-align: center; padding: 0 10px; }
        .school-name    { font-size: 14pt; font-weight: 900; color: #0f172a; text-transform: uppercase; letter-spacing: 1px; }
        .report-title   { font-size: 10pt; font-weight: 700; color: #10b981; margin-top: 4px; text-transform: uppercase; letter-spacing: 0.5px; }

        /* ── Metadata bar ── */
        .metadata-table {
            width: 100%;
            border-collapse: collapse;
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 8pt;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .metadata-table td { padding: 8px 14px; text-align: center; }

        /* ── Section block ── */
        .section-header {
            font-size: 10pt;
            font-weight: 900;
            color: #064e3b;
            background: #d1fae5;
            padding: 8px 14px;
            margin-top: 18px;
            margin-bottom: 6px;
            border-left: 5px solid #10b981;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* ── Data table ── */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5pt;
        }
        .data-table thead tr {
            background: #0f172a;
            color: #ffffff;
        }
        .data-table th {
            padding: 7px 10px;
            text-align: left;
            font-size: 7.5pt;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .data-table td {
            padding: 6px 10px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: middle;
        }
        .data-table tbody tr:last-child td { border-bottom: none; }

        /* ── Row colours ── */
        .row-gold    { background-color: #fef9c3; }
        .row-silver  { background-color: #f1f5f9; }
        .row-even    { background-color: #f8fafc; }

        /* ── Score badge ── */
        .score-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 20px;
            font-weight: 900;
            font-size: 8pt;
            color: #fff;
            background: #10b981;
        }
        .score-high   { background: #f59e0b; }  /* >= 95 */
        .score-great  { background: #10b981; }  /* 90-94  */

        /* ── Section count badge ── */
        .count-badge {
            display: inline-block;
            background: #e0fdf4;
            color: #065f46;
            border: 1px solid #6ee7b7;
            padding: 1px 7px;
            border-radius: 10px;
            font-size: 7.5pt;
            font-weight: 700;
            margin-left: 8px;
        }

        /* ── Utilities ── */
        .text-center { text-align: center; }
        .text-right  { text-align: right; }
        .font-black  { font-weight: bold; }
        .page-break  { page-break-after: always; }
        .avoid-break { page-break-inside: avoid; }

        /* ── Empty state ── */
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #64748b;
            font-style: italic;
            border: 1px dashed #cbd5e1;
            border-radius: 12px;
            margin-top: 30px;
        }
    </style>
</head>
<body>

    {{-- ── Page header ── --}}
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
                <h2 class="report-title">Academic Excellence Report &mdash; Average &ge; 90%</h2>
            </td>
            <td style="width: 70px;"></td>
        </tr>
    </table>

    {{-- ── Metadata bar ── --}}
    <table class="metadata-table">
        <tr>
            <td>ACADEMIC YEAR: <span style="color:#0f172a;">{{ $academicYear->name }}</span></td>
            <td>TIMELINE: <span style="color:#0f172a; text-transform:uppercase;">{{ $term->name }}</span></td>
            <td>DIVISION: <span style="color:#0f172a; text-transform:uppercase;">{{ $division->name }}</span></td>
        </tr>
    </table>

    @if(empty($sectionsData))
        <div class="empty-state">
            No students with an average of 90% or above were found for the selected timeline and division.
        </div>
    @else
        @php $currentGradeId = null; @endphp

        @foreach($sectionsData as $data)
            {{-- Page break between grade levels --}}
            @if($currentGradeId !== null && $currentGradeId !== $data['grade_level']->id)
                <div class="page-break"></div>
                <table class="header-table" style="margin-top:10px;">
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
                            <h2 class="report-title">Academic Excellence Report &mdash; Average &ge; 90%</h2>
                        </td>
                        <td style="width: 70px;"></td>
                    </tr>
                </table>
            @endif

            @php $currentGradeId = $data['grade_level']->id; @endphp

            <div class="avoid-break">
                <div class="section-header">
                    {{ $data['grade_level']->name }} &mdash; Section {{ $data['section']->name }}
                    <span class="count-badge">{{ $data['students']->count() }} student{{ $data['students']->count() !== 1 ? 's' : '' }}</span>
                </div>

                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="text-center" style="width:40px;">#</th>
                            <th>Student Name</th>
                            <th style="width:110px;">Student ID</th>
                            <th class="text-center" style="width:70px;">Gender</th>
                            <th class="text-right" style="width:120px;">Average Score</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data['students'] as $i => $student)
                            @php
                                $rowClass = $i % 2 === 0 ? 'row-silver' : 'row-even';
                                $avg = $student->average_score;
                                $scoreClass = $avg >= 95 ? 'score-high' : 'score-great';
                            @endphp
                            <tr class="{{ $rowClass }}">
                                <td class="text-center" style="color:#94a3b8; font-weight:700;">{{ $i + 1 }}</td>
                                <td class="font-black">
                                    {{ $student->first_name }} {{ $student->father_name }} {{ $student->grandfather_name }}
                                </td>
                                <td>{{ $student->student_id }}</td>
                                <td class="text-center">{{ $student->gender }}</td>
                                <td class="text-right">
                                    <span class="score-badge {{ $scoreClass }}">{{ number_format($avg, 2) }}%</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach
    @endif

</body>
</html>
