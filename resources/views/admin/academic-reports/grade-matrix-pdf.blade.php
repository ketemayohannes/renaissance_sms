<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>School Result Analysis</title>
    <style>
        @page {
            margin: 0.5cm;
            size: a3 landscape;
        }
        body {
            font-family: 'Helvetica', sans-serif;
            color: #1e293b;
            line-height: 1.2;
            font-size: 9pt;
        }
        .header {
            margin-bottom: 20px;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 10px;
        }
        .school-name {
            font-size: 24pt;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
        }
        .report-title {
            font-size: 14pt;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        th, td {
            border: 1px solid #e2e8f0;
            padding: 8px 4px;
            text-align: center;
        }
        th {
            background-color: #f8fafc;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 7pt;
        }
        .subject-header {
            height: 180px;
            vertical-align: bottom;
            padding: 0;
            position: relative;
        }
        .rotate {
            transform: rotate(-90deg);
            transform-origin: center;
            width: 180px;
            position: absolute;
            bottom: 80px;
            left: 50%;
            margin-left: -90px;
            text-align: left;
            font-size: 8pt;
            line-height: 1;
        }
        .total-cell {
            background-color: #f8fafc;
            font-weight: 900;
            font-size: 11pt;
            color: #0f172a;
        }
        .footer {
            margin-top: 50px;
            font-size: 8pt;
            color: #94a3b8;
            text-align: right;
        }
    </style>
</head>
<body>
    @foreach($termData as $termId => $data)
    <div class="header">
        <table style="border: none; margin: 0;">
            <tr style="border: none;">
                <td style="border: none; text-align: left; width: 15%; vertical-align: middle;">
                    @if($logoBase64)
                        <img src="{{ $logoBase64 }}" style="height: 60px; width: auto; object-fit: contain;">
                    @endif
                </td>
                <td style="border: none; text-align: left; width: 55%; vertical-align: middle;">
                    <div class="school-name">{{ $settings->school_name ?? 'RENAISSANCE SCHOOL' }}</div>
                    <div class="report-title">{{ $data['term']->name }} RESULT ANALYSIS &mdash; {{ $academicYear->name }}</div>
                </td>
                <td style="border: none; text-align: right; width: 30%; vertical-align: middle;">
                    <div style="font-size: 8pt; color: #64748b;">GENERATED ON</div>
                    <div style="font-weight: bold;">{{ now()->format('M d, Y') }}</div>
                </td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 150px; text-align: left; padding-left: 15px;">GRADE / TERM AVG</th>
                @foreach($allSubjects as $subject)
                    <th class="subject-header">
                        <div class="rotate">{{ $subject->name }}</div>
                    </th>
                @endforeach
                <th style="background-color: #f1f5f9; width: 100px;">{{ $data['term']->name }} AVERAGE</th>
            </tr>
        </thead>
        <tbody>
            @foreach($gradeLevels as $grade)
                <tr>
                    <td style="text-align: left; font-weight: bold; padding-left: 15px;">{{ $grade->name }}</td>
                    @foreach($allSubjects as $subject)
                        <td>
                            {{ isset($data['matrix'][$grade->id][$subject->id]) ? number_format($data['matrix'][$grade->id][$subject->id], 2) : '-' }}
                        </td>
                    @endforeach
                    <td class="total-cell">
                        {{ isset($data['gradeAverages'][$grade->id]) ? number_format($data['gradeAverages'][$grade->id], 2) : '-' }}
                    </td>
                </tr>
            @endforeach
            <tr style="background-color: #f8fafc; font-weight: bold;">
                <td style="text-align: left; padding-left: 15px;">SUBJECT AVERAGE</td>
                @foreach($allSubjects as $subject)
                    <td>
                        {{ isset($data['subjectAverages'][$subject->id]) ? number_format($data['subjectAverages'][$subject->id], 2) : '-' }}
                    </td>
                @endforeach
                <td style="font-size: 14pt; color: #4f46e5;">
                    {{ isset($data['overallAverage']) ? number_format($data['overallAverage'], 2) : '-' }}
                </td>
            </tr>
        </tbody>
    </table>
    @if(!$loop->last)
        <div style="page-break-after: always;"></div>
    @endif
    @endforeach

    <div class="footer">
        Official Academic Records | Renaissance Student Management System
    </div>
</body>
</html>
