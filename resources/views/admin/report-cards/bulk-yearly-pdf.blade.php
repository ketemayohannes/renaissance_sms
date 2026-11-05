<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Bulk Yearly Report Cards - {{ $section->name }}</title>
    <style>
        @page {
            margin: 0;
            size: A4;
        }
        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 10pt;
            margin: 0;
            background: #f0f0f0;
        }
        .no-print-bar {
            text-align: center;
            padding: 15px;
            background: #e0e0e0;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 2147483647;
            border-bottom: 1px solid #ccc;
        }
        @media print {
            .no-print-bar, .no-print, .spacer { 
                display: none !important; 
                height: 0 !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            body { background: white; }
            .page-container { margin: 0 !important; padding: 0 !important; border: none !important; }
            .page { 
                height: auto !important;
                margin-bottom: 0 !important; 
                padding-top: 8.7mm !important; 
            }
        }
        .page-container {
            background: white;
            width: 210mm;
            margin: 20px auto;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        @media print {
            .page-container {
                width: 100%;
                margin: 0;
                box-shadow: none;
            }
        }
        .page {
            width: 210mm;
            height: 296.5mm; /* Exact A4 height to prevent spillover */
            padding: 5mm 10mm;
            box-sizing: border-box;
            position: relative;
            margin: 0 auto;
            background: white;
            overflow: hidden;
        }
        
        /* Shared Styles */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }
        .w-full { width: 100%; }
        .border-all { border: 1px solid black; border-collapse: collapse; }
        .border-all th, .border-all td { border: 1px solid black; padding: 2px 4px; }
        
        .header-box {
            text-align: center;
            font-weight: bold;
            font-size: 11pt;
            text-decoration: underline;
            margin-bottom: 2px;
        }
        
        .comments-grid {
            width: 100%;
            border-collapse: collapse;
        }
        .comments-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid black;
        }
        .comments-col {
            width: 65%;
            vertical-align: top;
            border-right: 1px solid black;
        }
        .info-col {
            width: 35%;
            vertical-align: top;
        }
        
        .comment-box {
            padding: 4px 6px;
            border-bottom: 1px solid black;
            font-size: 8.5pt;
            line-height: 1.2;
        }
        .comment-box:last-child { border-bottom: none; }
        .comment-header {
            font-weight: bold;
            font-size: 9pt;
            margin-bottom: 2px;
            text-decoration: underline;
        }
        .checkbox-line { 
            margin-bottom: 5px; 
            display: flex;
            align-items: start;
            font-size: 8.5pt;
        }
        .checkbox {
            flex-shrink: 0;
            width: 14px;
            height: 14px;
            border: 1px solid black;
            margin-right: 8px;
            margin-top: 1px;
        }
        
        .eval-box, .remark-box {
            padding: 2px 4px;
            font-size: 8pt;
            line-height: 1.1;
        }
        .eval-box { border-bottom: 1px solid black; }
        .remark-box { border-bottom: none; height: 100%; }
        
        .student-strip {
            margin-top: 5px;
            margin-bottom: 5px;
            font-size: 10pt;
        }
        
        .grades-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10pt;
            border: 1px solid black;
            page-break-inside: avoid;
            line-height: 1.1;
        }
        .grades-table th, .grades-table td {
            border: 1px solid black;
            text-align: center;
            padding: 1px 2px;
        }
        .grades-table th { background-color: #d1d5db; padding: 2px 3px; }
        .grades-table td:first-child { text-align: left; font-weight: bold; padding-left: 4px; }
        .grades-table tr.footer-row { background-color: #d1d5db; font-weight: bold; }
        .footer-row td:first-child { text-align: center !important; }
        
        .footer-sig {
            margin-top: 5px;
            font-size: 9pt;
        }
        
        /* Page 2 Specifics (Back Page) */
        .back-page-container {
            padding: 0;
        }
        .school-header-box-new {
            border: 1px solid black;
            padding: 8px 15px;
            margin-bottom: 8px;
            text-align: center;
        }
        .school-name-large-new {
            font-size: 28pt;
            font-weight: bold;
            margin-bottom: 4px;
            letter-spacing: 1px;
        }
        .logo-img-new {
            height: 150px;
            width: auto;
            display: inline-block;
            margin: 2px 0;
        }
        .header-details-table {
            width: 100%;
            font-size: 8.5pt;
            margin-top: 8px;
        }
        
        .student-details-box-new {
            border: 1px solid black;
            padding: 10px 25px;
            margin-bottom: 6px;
        }
        .card-title-new {
            text-align: center;
            font-weight: bold;
            text-decoration: underline;
            font-size: 14pt;
            margin-bottom: 15px;
        }
        .details-grid-new {
            width: 100%;
            font-size: 11pt;
            line-height: 2.0;
        }
        .underlined-val-new {
            border-bottom: 1px solid black;
            padding: 0 8px;
            font-weight: normal;
        }
        
        .signature-box-new {
            border: 1px solid black;
            margin-top: 8px;
        }
        .sig-header-new {
            padding: 6px 12px;
            font-weight: normal;
            font-size: 11pt;
            border-bottom: 1px solid black;
        }
        .sig-table-new {
            width: 100%;
            border-collapse: collapse;
        }
        .sig-table-new th, .sig-table-new td {
            border: 1px solid black;
            padding: 5px 5px;
            text-align: center;
            font-size: 9.5pt;
        }
        .sig-table-new th { font-weight: bold; }
        .sig-table-new td:first-child { text-align: left; padding-left: 10px; }
        
        .footer-note-new {
            padding: 10px 12px;
            font-size: 8.5pt;
            text-align: justify;
            line-height: 1.3;
        }
    </style>
</head>
<body>

    @if(!($is_pdf ?? false))
    <div class="no-print-bar" style="display: flex; justify-content: center; gap: 15px; align-items: center;">
        <button onclick="window.print()" style="background-color: #4CAF50; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;">Print All Report Cards</button>
        <form action="{{ route('admin.academic-reports.recalculate') }}" method="POST" style="margin: 0;">
            @csrf
            <input type="hidden" name="academic_year_id" value="{{ $academicYear->id }}">
            <input type="hidden" name="term_id" value="yearly">
            <input type="hidden" name="section_id" value="{{ $section->id }}">
            <button type="submit" style="background-color: #4F46E5; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;">
                Sync Status
            </button>
        </form>
        <span style="font-weight: bold; color: #333;">Press Ctrl+P to save as PDF</span>
    </div>

    <div style="height: 60px;" class="no-print"></div>
    @endif

    @php
        $config = $settings->yearly_config ?? [];
        $conf = function($key, $default = '') use ($config) {
            return $config[$key] ?? $default;
        };
        
        $q1 = $quarters->where('term_number', 1)->first();
        $q2 = $quarters->where('term_number', 2)->first();
        $q3 = $quarters->where('term_number', 3)->first();
        $q4 = $quarters->where('term_number', 4)->first();
        
        $s1 = $semesters->first();
        $s2 = $semesters->last();
        
        $fmt = function($val) use ($isKindergarten) {
            if ($val === null || $val === '') {
                return '-';
            }
            return ($isKindergarten ?? false) 
                ? \App\Helpers\KindergartenGradeHelper::toGrade($val) 
                : \App\Helpers\NumberFormatter::format($val);
        };

        // Handle Logo Base64 for robust rendering
        $logoBase64 = null;
        if (isset($settings->logo_path) && $settings->logo_path) {
            $logoPath = storage_path('app/public/' . $settings->logo_path);
            if (file_exists($logoPath)) {
                $logoData = base64_encode(file_get_contents($logoPath));
                $logoBase64 = 'data:image/' . pathinfo($logoPath, PATHINFO_EXTENSION) . ';base64,' . $logoData;
            }
        }
    @endphp

    @foreach($reportCards as $data)
        @php
            $student = $data['student'];
            $studentSubjects = $data['studentSubjects'];
            $marks = $data['marks'];
            $termRecord = $data['termRecord'];
            $totalScore = $data['totalScore'];
            $average = $data['average'];
            $rankValue = $data['rank'];
            
            $qMarks = $data['studentQuarterMarks'];
            $qStats = $data['studentQuarterStats'];
            $qRecords = $data['studentQuarterRecords'];
            $sMarks = $data['studentSemesterMarks'];
            $sStats = $data['studentSemesterStats'];
            $attendance = $data['attendance'] ?? null;
            $promotedToGrade = $data['promotedToGrade'] ?? null;
            $promotionStatus = $data['promotionStatus'] ?? null;
        @endphp

        <div class="page-container" style="{{ $loop->last ? '' : 'page-break-after: always;' }}">
            @include('admin.report-cards.partials.yearly-card', [
                'subTermAttendance' => $data['subTermAttendance'] ?? [],
                'secondPageStyle' => 'clear: both;',
            ])
        </div>
    @endforeach

</body>
</html>
