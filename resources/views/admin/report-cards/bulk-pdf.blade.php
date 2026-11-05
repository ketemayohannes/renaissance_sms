<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Report Cards - {{ $section->name }}</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 11pt;
            margin: 0;
            padding: 0;
        }
        .report-card-container {
            position: relative;
            background: white;
        }
        
        /* Print Styles */
        @media print {
            .no-print {
                display: none;
            }
            .page-break {
                page-break-before: always;
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
        @media print {
            .no-print-bar, .spacer, .no-print {
                display: none !important;
                height: 0 !important;
                margin: 0 !important;
                padding: 0 !important;
            }
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

        /* Shared Styles from Single View */
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-top: 10px;
            position: relative; /* Fixes absolute logo positioning context */
            min-height: 110px; /* Increased for larger logo */
        }
        .logo {
            width: 180px;
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
            padding-left: 190px;
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
            font-family: sans-serif;
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
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
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
        .trait-row {
            margin-bottom: 15px;
            font-family: sans-serif;
        }
        .checkbox {
            display: inline-block;
            width: 25px;
            height: 25px;
            border: 1px solid black;
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
            margin-top: 25px;
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

        /* Print Spacing */
        .print-page {
            position: relative;
            height: 297mm;
            width: 210mm;
            padding: 15mm;
            box-sizing: border-box;
        }
    </style>
</head>
<body>

    @if(!($is_pdf ?? false))
    <div class="no-print-bar" style="display: flex; justify-content: center; gap: 15px; align-items: center;">
        <button onclick="window.print()" class="print-btn" style="background-color: #4CAF50; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;">Print All Report Cards</button>
        <form action="{{ route('admin.academic-reports.recalculate') }}" method="POST" style="margin: 0;">
            @csrf
            <input type="hidden" name="academic_year_id" value="{{ $academicYear->id }}">
            <input type="hidden" name="term_id" value="{{ $term->id }}">
            <input type="hidden" name="section_id" value="{{ $section->id }}">
            <button type="submit" class="print-btn" style="background-color: #4F46E5; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;">
                Sync Status
            </button>
        </form>
        <span style="font-weight: bold; color: #333;">Press Ctrl+P to save as PDF</span>
    </div>

    <!-- Spacer for fixed header -->
    <div class="spacer"></div>
    @endif

    @foreach($reportCards as $index => $data)
        @php
            $student = $data['student'];
            $marks = $data['marks'];
            $termRecord = $data['termRecord'];
            $totalScore = $data['totalScore'];
            $average = $data['average'];
            $attendance = $data['attendance'] ?? null;
        @endphp

        <div class="print-page {{ $loop->first ? '' : 'page-break' }}">
            
            @include('admin.report-cards.partials.term-card')
        </div>
    @endforeach

</body>
</html>
