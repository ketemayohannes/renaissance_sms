<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Report Card - {{ $student->full_name }}</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 11pt; /* Slightly smaller to fit */
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-top: 10px;
            position: relative; /* Fixes absolute logo positioning context */
            min-height: 110px; /* Increased for larger logo */
        }
        .logo {
            width: 180px; /* Increased size */
            height: auto;
            position: absolute;
            left: 0;
            top: 0;
        }
        .school-name {
            font-family: "Times New Roman", Times, serif;
            font-size: 20pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #000;
            margin-bottom: 10px;
            padding-left: 190px; /* Offset to ensure no overlap if text is long, though centering usually handles it */
            padding-right: 190px;
            white-space: nowrap;
        }
        .report-title {
            font-family: "Times New Roman", Times, serif;
            font-size: 16pt;
            margin-top: 5px;
            font-weight: bold;
        }
        .student-info {
            margin-bottom: 15px;
            width: 100%;
            border-collapse: collapse;
            font-family: sans-serif; /* Keep data sans-serif for readability? Image looks mixed. Let's keep data sans-serif unless requested. */
        }
        .student-info td {
            padding: 3px;
        }
        .label {
            font-weight: bold;
            text-align: right;
            padding-right: 10px;
            width: 150px;
        }
        
        .main-container {
            width: 100%;
        }
        .col-left {
            width: 48%;
            float: left;
        }
        .col-right {
            width: 48%;
            float: right;
        }
        
        /* Clearfix */
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }

        /* Subjects Table */
        .subjects-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid black;
            font-family: sans-serif;
        }
        .subjects-table th, .subjects-table td {
            border: 1px solid black;
            padding: 3px 5px;
            text-align: center;
        }
        .subjects-table th {
            font-weight: bold;
            background-color: #d1d5db;
        }
        .subjects-table td:first-child {
            text-align: left;
            padding-left: 5px;
        }
        .total-row {
            background-color: #d1d5db;
            font-weight: bold;
        }
        .total-row td {
            border-top: 1px solid black;
        }

        /* Traits Box (Right Side) */
        .trait-row {
            margin-bottom: 15px; /* More spacing */
            font-family: sans-serif;
        }
        .checkbox {
            display: inline-block;
            width: 25px; /* Larger checkbox */
            height: 25px;
            border: 1px solid black; /* Thicker border */
            margin-right: 10px;
            vertical-align: top;
            text-align: center;
            line-height: 25px;
        }
        .trait-text {
            display: inline-block;
            vertical-align: top;
            width: 85%;
            font-size: 10pt;
            line-height: 1.4;
        }

        .comments-section {
            margin-top: 25px;
            font-family: sans-serif;
        }
        .line {
            border-bottom: 1px solid black;
            width: 100%;
            margin-top: 25px; /* More spacing for handwriting */
            height: 1px;
        }
        
        .contact-footer {
            position: absolute;
            bottom: 15mm;
            left: 15mm;
            right: 15mm;
            width: auto;
            font-family: sans-serif;
        }
        .contact-footer-content {
            width: 100%;
            border-top: 1px solid black;
            padding-top: 10px;
            font-size: 10pt;
        }
        .contact-footer-content td {
            width: 50%;
            vertical-align: top;
        }
        .contact-right {
            text-align: right;
        }
        .signature-section {
            margin-top: 30px;
            width: 100%;
        }
        .signature-box {
            width: 45%;
            float: left;
            text-align: center;
        }
        .signature-box.right {
            float: right;
            text-align: center;
        }
        .signature-line {
            border-top: 1px solid black;
            margin-top: 40px;
            display: block;
            width: 100%;
            font-weight: bold;
        }
        .report-container {
            position: relative;
            height: 297mm;
            width: 210mm;
            margin: 0 auto;
            padding: 15mm;
            box-sizing: border-box;
            background: white;
        }

        /* Print Styles */
        @media print {
            .no-print, .no-print-bar, .spacer {
                display: none !important;
                height: 0 !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            body {
                margin: 0;
                padding: 0;
            }
            .page-break {
                page-break-after: always;
            }
            @page {
                size: A4;
                margin: 0;
            }
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
        .spacer {
             height: 80px;
        }
        .print-btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
    </style>
</head>
<body>

    @if(!($is_pdf ?? false))
    <!-- No-Print Bar -->
    <div class="no-print-bar" style="display: flex; justify-content: center; gap: 10px; align-items: center;">
        <button onclick="window.print()" class="print-btn">
            Print Report Card
        </button>
        <form action="{{ route('admin.academic-reports.recalculate') }}" method="POST" style="margin: 0;">
            @csrf
            <input type="hidden" name="academic_year_id" value="{{ $academicYear->id }}">
            <input type="hidden" name="term_id" value="{{ $term->id }}">
            <input type="hidden" name="section_id" value="{{ $section->id }}">
            <button type="submit" class="print-btn" style="background-color: #4F46E5;">
                Sync Status
            </button>
        </form>
    </div>

    <!-- Spacer for fixed header -->
    <div class="spacer"></div>
    @endif

    <div class="report-container">
        @php
            // The single-student params name things differently from the bulk ones; build
            // the same per-student $data array the shared card expects.
            $quarterStats = [];
            foreach (['total' => $quarterTotals ?? [], 'avg' => $quarterAverages ?? [], 'rank' => $quarterRanks ?? []] as $stat => $values) {
                foreach ($values as $termId => $value) {
                    $quarterStats[$termId][$stat] = $value;
                }
            }
            $data = [
                'studentSubjects' => $subjects,
                'studentQuarterMarks' => $quarterMarks ?? [],
                'studentQuarterStats' => $quarterStats,
                'studentQuarterRecords' => $quarterRecords ?? [],
                'subTermAttendance' => $subTermAttendance ?? [],
                'rank' => $rank,
            ];
            $attendance = $attendance ?? null;
        @endphp

        @include('admin.report-cards.partials.term-card')
    </div>

</body>
</html>
