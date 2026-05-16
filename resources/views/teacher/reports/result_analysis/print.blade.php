<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Subject Wise Result Analysis</title>
    <style>
        @page {
            margin: 0.5cm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #000;
            line-height: 1.2;
            margin: 0;
            padding: 0;
            font-size: 12px;
        }
        .container {
            padding: 0;
            position: relative;
            min-height: 18.5cm; /* Approximately A4 landscape height minus margins */
        }
        .header-table {
            width: 100%;
            margin-bottom: 10px;
        }
        .logo-cell {
            width: 130px;
            height: 70px;
            vertical-align: middle;
        }
        .logo-img {
            width: 110px;
            height: 60px;
            display: block;
        }
        .school-info-cell {
            text-align: center;
            vertical-align: middle;
            padding-right: 60px;
        }
        .school-name {
            font-size: 22px;
            font-weight: bold;
            text-transform: uppercase;
            margin: 0;
        }
        .school-address {
            font-size: 12px;
            margin: 0;
        }
        .report-title {
            font-size: 16px;
            font-weight: bold;
            text-decoration: underline;
            margin-top: 5px;
            text-transform: capitalize;
        }
        .meta-row {
            width: 100%;
            margin: 10px 0;
            border-collapse: collapse;
        }
        .meta-row td {
            padding: 2px 0;
            white-space: nowrap;
            font-size: 12px;
        }
        .meta-label {
            font-weight: bold;
        }
        .meta-value {
            border-bottom: 1px solid #000;
            padding: 0 8px;
            min-width: 80px;
            display: inline-block;
        }
        table.stats {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        table.stats th, table.stats td {
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
            font-size: 12px;
        }
        table.stats th {
            background-color: #ffffff;
            font-weight: bold;
        }
        .analysis-section {
            margin-top: 10px;
        }
        .analysis-item {
            margin-bottom: 8px;
        }
        .analysis-label {
            font-weight: bold;
            margin-bottom: 4px;
            display: block;
            font-size: 12px;
        }
        .analysis-lines {
            border-bottom: 1px solid #000;
            min-height: 22px;
            margin-bottom: 4px;
        }
        .analysis-content {
            padding: 0 10px;
            font-style: italic;
            font-size: 13px;
            line-height: 22px;
            display: block;
        }
        .signature-section {
            position: absolute;
            bottom: 40px;
            width: 100%;
            border-collapse: collapse;
        }
        .signature-section td {
            vertical-align: bottom;
            padding: 5px;
            font-size: 11px;
            white-space: nowrap;
        }
        .sig-label {
            font-weight: bold;
        }
        .sig-value-line {
            border-bottom: 1px solid #000;
            text-align: center;
            padding: 0 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <table class="header-table">
            <tr>
                <td class="logo-cell">
                    @if($settings && $settings->logo_path)
                        @php
                            $path = public_path('storage/' . $settings->logo_path);
                            $type = pathinfo($path, PATHINFO_EXTENSION);
                            $data = file_exists($path) ? file_get_contents($path) : null;
                            $base64 = $data ? 'data:image/' . $type . ';base64,' . base64_encode($data) : null;
                        @endphp
                        @if($base64)
                            <img src="{{ $base64 }}" class="logo-img">
                        @endif
                    @endif
                </td>
                <td class="school-info-cell">
                    <p class="school-name">{{ $settings->school_name ?? 'RENAISSANCE SCHOOL' }}</p>
                    <p class="school-address">{{ $settings->school_address ?? 'Addis Ababa' }}</p>
                    <p class="report-title">Subject Result Analysis</p>
                </td>
            </tr>
        </table>

        <table class="meta-row">
            <tr>
                <td><span class="meta-label">Subject:</span> <span class="meta-value">{{ $assignment->subject->name }}</span></td>
                <td><span class="meta-label">Grade:</span> <span class="meta-value">{{ $assignment->section->gradeLevel->name }}</span></td>
                <td><span class="meta-label">Semester:</span> <span class="meta-value">{{ $semester->name ?? 'N/A' }}</span></td>
                <td><span class="meta-label">Quarter:</span> <span class="meta-value">{{ $term->name }}</span></td>
                <td><span class="meta-label">Academic Year:</span> <span class="meta-value">{{ $academicYear->name }}</span></td>
            </tr>
        </table>

        <table class="stats">
            <thead>
                <tr>
                    <th rowspan="3">Sections</th>
                    <th colspan="9">Result range</th>
                    <th colspan="3" rowspan="2">Total Student</th>
                    <th rowspan="3">Remark</th>
                </tr>
                <tr>
                    <th colspan="3">0 - 49</th>
                    <th colspan="3">50 - 74</th>
                    <th colspan="3">75 - 100</th>
                </tr>
                <tr>
                    <th>Male</th><th>Female</th><th>Total</th>
                    <th>Male</th><th>Female</th><th>Total</th>
                    <th>Male</th><th>Female</th><th>Total</th>
                    <th>Male</th><th>Female</th><th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sectionData as $data)
                    <tr>
                        <td style="font-weight: bold;">{{ $data['assignment']->section->name }}</td>
                        <td>{{ $data['analysis']['0-49']['male'] }}</td>
                        <td>{{ $data['analysis']['0-49']['female'] }}</td>
                        <td style="font-weight:bold;">{{ $data['analysis']['0-49']['total'] }}</td>
                        <td>{{ $data['analysis']['50-74']['male'] }}</td>
                        <td>{{ $data['analysis']['50-74']['female'] }}</td>
                        <td style="font-weight:bold;">{{ $data['analysis']['50-74']['total'] }}</td>
                        <td>{{ $data['analysis']['75-100']['male'] }}</td>
                        <td>{{ $data['analysis']['75-100']['female'] }}</td>
                        <td style="font-weight:bold;">{{ $data['analysis']['75-100']['total'] }}</td>
                        <td>{{ $data['analysis']['total_students']['male'] }}</td>
                        <td>{{ $data['analysis']['total_students']['female'] }}</td>
                        <td style="font-weight:bold;">{{ $data['analysis']['total_students']['total'] }}</td>
                        <td style="text-align: left;">{{ $data['report']->section_remark ?? '' }}</td>
                    </tr>
                @endforeach
                <tr style="font-weight: bold; background-color: #eee;">
                    <td>Total</td>
                    <td>{{ $grandTotalAnalysis['0-49']['male'] }}</td>
                    <td>{{ $grandTotalAnalysis['0-49']['female'] }}</td>
                    <td>{{ $grandTotalAnalysis['0-49']['total'] }}</td>
                    <td>{{ $grandTotalAnalysis['50-74']['male'] }}</td>
                    <td>{{ $grandTotalAnalysis['50-74']['female'] }}</td>
                    <td>{{ $grandTotalAnalysis['50-74']['total'] }}</td>
                    <td>{{ $grandTotalAnalysis['75-100']['male'] }}</td>
                    <td>{{ $grandTotalAnalysis['75-100']['female'] }}</td>
                    <td>{{ $grandTotalAnalysis['75-100']['total'] }}</td>
                    <td>{{ $grandTotalAnalysis['total_students']['male'] }}</td>
                    <td>{{ $grandTotalAnalysis['total_students']['female'] }}</td>
                    <td>{{ $grandTotalAnalysis['total_students']['total'] }}</td>
                    <td></td>
                </tr>
            </tbody>
        </table>

        <div class="analysis-section">
            <div class="analysis-item">
                <span class="analysis-label">A. Subject Teacher’s Comment Based on a Comparison of the Current Results with Previous Quarters:</span>
                <div class="analysis-lines"><span class="analysis-content">{{ $globalReport->comparison_comment ?? '' }}</span></div>
                <div class="analysis-lines"></div>
            </div>
            <div class="analysis-item">
                <span class="analysis-label">B. Problems Encountered:</span>
                <div class="analysis-lines"><span class="analysis-content">{{ $globalReport->problems_encountered ?? '' }}</span></div>
                <div class="analysis-lines"></div>
            </div>
            <div class="analysis-item">
                <span class="analysis-label">C. Solutions Implemented:</span>
                <div class="analysis-lines"><span class="analysis-content">{{ $globalReport->solutions_implemented ?? '' }}</span></div>
                <div class="analysis-lines"></div>
            </div>
            <div class="analysis-item">
                <span class="analysis-label">D. Additional Comment:</span>
                <div class="analysis-lines"><span class="analysis-content">{{ $globalReport->additional_comment ?? '' }}</span></div>
                <div class="analysis-lines"></div>
            </div>
        </div>

        <table class="signature-section">
            <tr>
                <td class="sig-label">Teacher’s Name:</td>
                <td class="sig-value-line" style="width: 220px;text-align: left;">{{ $teacher->name }}</td>
                <td class="sig-label">Signature:</td>
                <td class="sig-value-line" style="width: 120px;"></td>
                <td class="sig-label" style="padding-left: 20px;">Principal’s Name:</td>
                <td class="sig-value-line" style="width: 220px;text-align: left;"></td>
                <td class="sig-label">Signature:</td>
                <td class="sig-value-line" style="width: 120px;"></td>
            </tr>
        </table>
    </div>
</body>
</html>
