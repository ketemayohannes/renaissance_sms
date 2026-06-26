<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Summary Marksheet - {{ $subject->name }} - {{ $section->name }}</title>
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
        .marks-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid black;
        }
        .marks-table th, .marks-table td {
            border: 1px solid black;
            padding: 6px 4px;
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
        .final-col {
            color: #0f766e;
            font-weight: bold;
        }
        .footer {
            margin-top: 50px;
            width: 100%;
        }
        .footer td {
            text-align: center;
        }
        .signature-line {
            margin-top: 40px;
            border-top: 1px solid black;
            padding-top: 5px;
            font-weight: bold;
        }
        @page {
            margin: 1.5cm 1cm 1cm 1cm;
        }
    </style>
</head>
<body>
    <div class="header">
        @if($settings->logo_path)
            <img class="logo" src="{{ public_path('storage/' . $settings->logo_path) }}" alt="Logo">
        @endif
        <div class="school-name">{{ $settings->school_name ?? 'RENAISSANCE SCHOOL' }}</div>
        <div class="report-title">SUBJECT SUMMARY MARKSHEET</div>
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

    @php
        // Filter out FINAL and calculate total CA Capacity
        $caComponents = $gradeComponents->filter(fn($c) => $c->assessmentType?->code !== 'FINAL');
        $caCapacity = $caComponents->sum('max_score');

        // Find FINAL template and capacity
        $finalComponent = $gradeComponents->first(fn($c) => $c->assessmentType?->code === 'FINAL');
        $finalCapacity = $finalComponent ? $finalComponent->max_score : 0;
        
        // If there's no FINAL explicitly set up, but we have components, adjust dynamically
        if ($finalCapacity == 0 && $caCapacity == 0) {
            // Safe fallback
            $caCapacity = 60;
            $finalCapacity = 40;
        } elseif ($finalCapacity == 0 && $caCapacity > 0) {
            // If only CA is present (for example if final is not configured yet or the total sum is 100)
            if ($caCapacity < 100) {
                $finalCapacity = 100 - $caCapacity;
            }
        }
    @endphp

    <table class="marks-table">
        <thead>
            <tr>
                <th style="width: 40px;">No</th>
                <th class="student-name-col">Student Full Name</th>
                <th style="width: 50px;">Gender</th>
                <th class="ca-col" style="width: 100px;">CA ({{ (int)$caCapacity }}%)</th>
                <th class="final-col" style="width: 120px;">Final Exam ({{ (int)$finalCapacity }}%)</th>
                <th class="total-col" style="width: 100px;">Total (100%)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $index => $student)
                @php
                    $studentMarks = $existingMarks->get($student->id);
                    $totalScore = 0;
                    $caScore = 0;
                    $finalScore = 0;
                    $hasAnyMark = $studentMarks && $studentMarks->count() > 0;

                    if ($hasAnyMark) {
                        foreach ($gradeComponents as $component) {
                            $mark = $studentMarks->firstWhere('assessment_template_id', $component->id);
                            if ($mark) {
                                $score = $mark->score;
                                $totalScore += $score;
                                if ($component->assessmentType?->code === 'FINAL') {
                                    $finalScore += $score;
                                } else {
                                    $caScore += $score;
                                }
                            }
                        }
                    }

                    $displayTotal = $totalScore;
                    if ($totalScore == 0 && isset($termTotalTemplate) && $termTotalTemplate) {
                        $termTotalMark = $studentMarks?->firstWhere('assessment_template_id', $termTotalTemplate->id);
                        if ($termTotalMark) {
                            $displayTotal = $termTotalMark->score;
                            $hasAnyMark = true;
                        }
                    }
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="student-name-col">{{ $student->full_name }}</td>
                    <td>{{ substr($student->gender ?? 'M', 0, 1) }}</td>
                    <td class="ca-col">{{ $hasAnyMark ? number_format($caScore, 1) : '' }}</td>
                    <td class="final-col">
                        @if($hasAnyMark && $finalComponent)
                            {{ $studentMarks->firstWhere('assessment_template_id', $finalComponent->id) ? number_format($finalScore, 1) : '' }}
                        @else
                            {{ ($hasAnyMark && $finalScore > 0) ? number_format($finalScore, 1) : '' }}
                        @endif
                    </td>
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
