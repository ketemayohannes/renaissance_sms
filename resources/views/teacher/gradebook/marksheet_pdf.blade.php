<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Marksheet - {{ $subject->name }} - {{ $section->name }}</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 10pt;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            position: relative;
            min-height: 80px;
        }
        .logo {
            width: 100px;
            height: auto;
            position: absolute;
            left: 0;
            top: 0;
        }
        .school-name {
            font-family: "Times New Roman", Times, serif;
            font-size: 18pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        .report-title {
            font-size: 14pt;
            font-weight: bold;
            text-decoration: underline;
            margin-top: 5px;
        }
        .info-table {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 3px 0;
            vertical-align: top;
        }
        .label {
            font-weight: bold;
        }
        .info-table .right-col {
            text-align: right;
        }
        .info-table .value-right {
            text-align: right;
            width: 1%;
            white-space: nowrap;
        }
        .info-table .label-right {
            text-align: right;
            font-weight: bold;
            padding-right: 8px;
        }
        .marks-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid black;
        }
        .marks-table th, .marks-table td {
            border: 1px solid black;
            padding: 4px;
            text-align: center;
        }
        .marks-table th {
            background-color: #f3f4f6;
            font-size: 9pt;
            font-weight: bold;
        }
        .marks-table td {
            font-size: 9pt;
        }
        .student-name-col {
            text-align: left !important;
            padding-left: 8px !important;
            white-space: nowrap;
        }
        .total-col {
            font-weight: bold;
            background-color: #f9fafb;
        }
        .ca-col {
            color: #4f46e5;
            font-weight: bold;
        }
        .footer {
            margin-top: 40px;
            width: 100%;
        }
        .footer td {
            text-align: center;
        }
        .signature-box {
            width: 30%;
            display: inline-block;
            vertical-align: top;
            text-align: center;
        }
        .signature-line {
            margin-top: 40px;
            border-top: 1px solid black;
            padding-top: 5px;
            font-weight: bold;
        }
        @page {
            margin: 1cm;
        }
    </style>
</head>
<body>
    <div class="header">
        @if($settings->logo_path)
            <img class="logo" src="{{ public_path('storage/' . $settings->logo_path) }}" alt="Logo">
        @endif
        <div class="school-name">{{ $settings->school_name ?? 'RENAISSANCE SCHOOL' }}</div>
        <div class="report-title">SUBJECT MARKSHEET</div>
    </div>

    <table class="info-table">
        <tr>
            <td><span class="label">Academic Year:</span> {{ $academicYear->name }}</td>
            <td style="text-align: right;"><span class="label">Term:</span> {{ $term->name }}</td>
        </tr>
        <tr>
            <td><span class="label">Grade & Section:</span> {{ $section->gradeLevel->name }} {{ $section->name }}</td>
            <td style="text-align: right;"><span class="label">Subject:</span> {{ $subject->name }} ({{ $subject->code }})</td>
        </tr>
        <tr>
            <td><span class="label">Teacher:</span> {{ $teacher->full_name ?? $teacher->name }}</td>
            <td style="text-align: right;"><span class="label">Date:</span> {{ now()->format('d/m/Y') }}</td>
        </tr>
    </table>

    <table class="marks-table">
        <thead>
            <tr>
                <th style="width: 30px;">No</th>
                <th class="student-name-col">Student Full Name</th>
                <th style="width: 40px;">Gender</th>
                @foreach($gradeComponents as $component)
                    <th>
                        {{ $component->name }}<br>
                        <span style="font-size: 8pt; font-weight: normal;">({{ (int)$component->max_score }})</span>
                    </th>
                @endforeach
                @php
                    $totalCaCapacity = $gradeComponents->filter(fn($c) => $c->assessmentType?->code !== 'FINAL')->sum('max_score');
                @endphp
                <th class="ca-col">CA ({{ (int)$totalCaCapacity }}%)</th>
                <th class="total-col">Total (100%)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $index => $student)
                @php
                    $studentMarks = $existingMarks->get($student->id);
                    $totalScore = 0;
                    $caScore = 0;
                    $hasAnyMark = $studentMarks && $studentMarks->count() > 0;
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="student-name-col">{{ $student->full_name }}</td>
                    <td>{{ substr($student->gender ?? 'M', 0, 1) }}</td>
                    @foreach($gradeComponents as $component)
                        @php
                            $mark = $studentMarks?->firstWhere('assessment_template_id', $component->id);
                            $score = $mark ? $mark->score : 0;
                            $isFinal = ($component->assessmentType?->code === 'FINAL');
                            
                            if ($mark) {
                                $totalScore += $score;
                                if (!$isFinal) {
                                    $caScore += $score;
                                }
                            }
                        @endphp
                        <td>{{ $mark ? number_format($score, 1) : '' }}</td>
                    @endforeach
                    <td class="ca-col">{{ $hasAnyMark ? number_format($caScore, 1) : '' }}</td>
                    @php
                        $displayTotal = $totalScore;
                        if ($totalScore == 0 && isset($termTotalTemplate) && $termTotalTemplate) {
                            $termTotalMark = $studentMarks?->firstWhere('assessment_template_id', $termTotalTemplate->id);
                            if ($termTotalMark) {
                                $displayTotal = $termTotalMark->score;
                                $hasAnyMark = true;
                            }
                        }
                    @endphp
                    <td class="total-col">{{ $hasAnyMark ? number_format($displayTotal, 1) : '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="footer">
        <tr>
            <td width="30%">
                <div class="signature-line">Teacher's Signature</div>
            </td>
            <td width="5%"></td>
            <td width="30%">
                <div class="signature-line">Department Head</div>
            </td>
            <td width="5%"></td>
            <td width="30%">
                <div class="signature-line">Principal's Signature</div>
            </td>
        </tr>
    </table>
</body>
</html>
