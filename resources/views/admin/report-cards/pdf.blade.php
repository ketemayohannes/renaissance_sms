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

    <!-- No-Print Bar -->
    <div class="no-print-bar">
        <button onclick="window.print()" class="print-btn">
            Print Report Card
        </button>
    </div>

    <!-- Spacer for fixed header -->
    <div class="spacer"></div>

    <div class="report-container">
        <div class="header">
        @if($settings->logo_path)
            <img class="logo" src="{{ asset('storage/' . $settings->logo_path) }}" alt="Logo">
        @endif
        <div class="school-name">{{ $settings->school_name }}</div>
        <div class="report-title">Progress Report Card</div>
    </div>

    <!-- Student Info -->
    <table class="student-info">
        <tr>
            <td class="label">Student Full Name:</td>
            <td>{{ strtoupper($student->full_name) }}</td>
        </tr>
        <tr>
            <td class="label">Gender:</td>
            <td>{{ $student->gender ?? 'M' }}</td>
        </tr>
        <tr>
            <td class="label">Grade & Section:</td>
            <td>{{ str_replace('Grade ', '', $section->gradeLevel->name) }}{{ $section->name }}</td>
        </tr>
        <tr>
            <td class="label">Quarter:</td>
            <td>{{ str_replace('Quarter ', '', $term->name) }}</td>
        </tr>
        <tr>
            <td class="label">Academic Year:</td>
            <td>{{ $academicYear->name }} G.C {{ \App\Helpers\EthiopianDateHelper::fromGregorian($academicYear->start_date)->format('Y') }} E.C</td>
        </tr>
    </table>

    <div class="main-container clearfix">
        
        <!-- Left Column: Grades -->
        <div class="col-left">
            <table class="subjects-table">
                <thead>
                    <tr>
                        <th style="width: 40%">Subject</th>
                        @if($isSemester)
                            @php $qLabels = [1 => 'First Quarter', 2 => 'Second Quarter', 3 => 'Third Quarter', 4 => 'Fourth Quarter']; @endphp
                            @foreach($quarters as $quarter)
                                <th style="width: 20%">{{ $qLabels[$quarter->term_number] ?? $quarter->name }}</th>
                            @endforeach
                            <th style="width: 20%">Semester Average</th>
                        @elseif($isYearly)
                            @foreach($semesters as $semester)
                                <th style="width: 20%">{{ $semester->name }}</th>
                            @endforeach
                            <th style="width: 20%">Yearly Average</th>
                        @else
                            <th style="width: 40%">Result</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($subjects as $subject)
                        <tr>
                            <td>{{ $subject->name }}</td>
                            @if($isSemester)
                                @php 
                                    $subjTotal = 0; 
                                    $subjCount = 0;
                                @endphp
                                @foreach($quarters as $quarter)
                                    @php
                                        $score = $quarterMarks[$subject->id][$quarter->id] ?? null;
                                        if($score !== null) { $subjTotal += $score; $subjCount++; }
                                    @endphp
                                    <td>{{ $score !== null ? \App\Helpers\NumberFormatter::format($score) : '-' }}</td>
                                @endforeach
                                <td>{{ $subjCount > 0 ? \App\Helpers\NumberFormatter::format($subjTotal / $subjCount) : '-' }}</td>
                            @elseif($isYearly)
                                @php 
                                    $subjTotal = 0; 
                                    $subjCount = 0;
                                @endphp
                                @foreach($semesters as $semester)
                                    @php
                                        $score = $semesterMarks[$subject->id][$semester->id] ?? null;
                                        if($score !== null) { $subjTotal += $score; $subjCount++; }
                                    @endphp
                                    <td>{{ $score !== null ? \App\Helpers\NumberFormatter::format($score) : '-' }}</td>
                                @endforeach
                                <td>{{ $subjCount > 0 ? \App\Helpers\NumberFormatter::format($subjTotal / $subjCount) : '-' }}</td>
                            @else
                                <td>{{ isset($marks[$subject->id]) ? \App\Helpers\NumberFormatter::format($marks[$subject->id]) : '-' }}</td>
                            @endif
                        </tr>
                    @endforeach
                    
                    <tr class="total-row">
                        <td style="text-align: center">Total</td>
                        @if($isSemester)
                            @foreach($quarters as $quarter)
                                <td>{{ \App\Helpers\NumberFormatter::format($quarterTotals[$quarter->id] ?? 0) }}</td>
                            @endforeach
                            <td>{{ \App\Helpers\NumberFormatter::format($totalScore) }}</td>
                        @elseif($isYearly)
                            @foreach($semesters as $semester)
                                <td>{{ \App\Helpers\NumberFormatter::format($semesterTotals[$semester->id] ?? 0) }}</td>
                            @endforeach
                            <td>{{ \App\Helpers\NumberFormatter::format($totalScore) }}</td>
                        @else
                            <td>{{ \App\Helpers\NumberFormatter::format($totalScore) }}</td>
                        @endif

                    </tr>
                    <tr class="total-row">
                        <td style="text-align: center">Average</td>
                        @if($isSemester)
                            @foreach($quarters as $quarter)
                                <td>{{ \App\Helpers\NumberFormatter::format($quarterAverages[$quarter->id] ?? 0) }}</td>
                            @endforeach
                            <td>{{ \App\Helpers\NumberFormatter::format($average) }}</td>
                        @elseif($isYearly)
                            @foreach($semesters as $semester)
                                <td>{{ \App\Helpers\NumberFormatter::format($semesterAverages[$semester->id] ?? 0) }}</td>
                            @endforeach
                            <td>{{ \App\Helpers\NumberFormatter::format($average) }}</td>
                        @else
                            <td>{{ \App\Helpers\NumberFormatter::format($average) }}</td>
                        @endif

                    </tr>
                    <tr class="total-row">
                        <td style="text-align: center">Conduct</td>
                        @if($isSemester)
                            @foreach($quarters as $quarter)
                                <td>{{ $quarterRecords[$quarter->id]->conduct_grade ?? 'A' }}</td>
                            @endforeach
                            <td>-</td>
                        @elseif($isYearly)
                            @foreach($semesters as $semester)
                                <td>-</td>
                            @endforeach
                            <td>-</td>
                        @else
                            <td>{{ $termRecord->conduct_grade ?? 'A' }}</td>
                        @endif

                    </tr>
                    <tr class="total-row">
                        <td style="text-align: center">Absence</td>
                        @if($isSemester)
                            @foreach($quarters as $quarter)
                                @php 
                                    $absCount = $subTermAttendance[$quarter->id]['absent'] ?? 0;
                                    $display = $absCount > 0 ? $absCount : '_';
                                @endphp
                                <td>{{ $display }}</td>
                            @endforeach
                            <td>{{ ($attendance['absent'] ?? 0) > 0 ? $attendance['absent'] : '_' }}</td>
                        @elseif($isYearly)
                            @foreach($semesters as $semester)
                                @php 
                                    $absCount = $subTermAttendance[$semester->id]['absent'] ?? 0;
                                    $display = $absCount > 0 ? $absCount : '_';
                                @endphp
                                <td>{{ $display }}</td>
                            @endforeach
                            <td>{{ ($attendance['absent'] ?? 0) > 0 ? $attendance['absent'] : '_' }}</td>
                        @else
                            <td>{{ ($attendance['absent'] ?? 0) > 0 ? $attendance['absent'] : '_' }}</td>
                        @endif


                    </tr>
                    @if($settings->template_config['show_rank'] ?? true)
                    <tr class="total-row">
                        <td style="text-align: center">Rank</td>
                        @if($isSemester)
                            @foreach($quarters as $quarter)
                                <td>{{ $quarterRanks[$quarter->id] ?? '-' }}</td>
                            @endforeach
                            <td>{{ $rank }} / {{ $totalStudents }}</td>
                        @elseif($isYearly)
                            @foreach($semesters as $semester)
                                <td>{{ $semesterRanks[$semester->id] ?? '-' }}</td>
                            @endforeach
                            <td>{{ $rank }} / {{ $totalStudents }}</td>
                        @else
                            <td>{{ $rank }} / {{ $totalStudents }}</td>
                        @endif

                    </tr>
                    @endif
                </tbody>
            </table>
        </div>

    <!-- Right Column: Traits Details -->
    <div class="col-right">
            @php
                $traits = $settings->template_config['traits'] ?? [];
            @endphp
            
            @if(count($traits) > 0)
                @foreach($traits as $trait)
                    @if(!empty($trait))
                    <div class="trait-row">
                        <div class="checkbox"></div>
                        <div class="trait-text">{{ $trait }}</div>
                    </div>
                    @endif
                @endforeach
            @else
                <!-- Fallback defaults if not configured -->
                 <div class="trait-row">
                    <div class="checkbox"></div>
                    <div class="trait-text">More effort and attention are needed in ____________, ____________ and ____________ Subject.</div>
                </div>
                <div class="trait-row">
                    <div class="checkbox"></div>
                    <div class="trait-text">His/her work is excellent so keep it up.</div>
                </div>
                <div class="trait-row">
                    <div class="checkbox"></div>
                    <div class="trait-text">We appreciated his/her desirable behaviour, but he/she...</div>
                </div>
                 <div class="trait-row">
                    <div class="checkbox"></div>
                    <div class="trait-text">Needs to be encouraged to listen and pay attention to his/her lesson.</div>
                </div>
            @endif

            <div class="comments-section">
                <strong>Additional comment</strong>
                <div class="line"></div>
                <div class="line"></div>
                <div class="line"></div>
                <div class="line"></div>
            </div>
            
            <div style="margin-top: 30px;">
                <strong>Date:</strong> ______________________
            </div>
            
             <div style="margin-top: 15px;">
                <strong>Homeroom Teacher Name:</strong> <span style="border-bottom: 1px solid black; padding-right: 20px;">{{ ucwords(mb_strtolower($section->homeroomTeacher->employee->first_name ?? '')) }} {{ ucwords(mb_strtolower($section->homeroomTeacher->employee->middle_name ?? '')) }}</span>
            </div>

             <div style="margin-top: 15px;">
                <strong>Signature:</strong> ______________________
            </div>

        </div>

    </div>

        <div class="signature-section clearfix">
            <div class="signature-box">
                <span class="signature-line">Principal Name</span>
            </div>
            <div class="signature-box right">
                <span class="signature-line">Principal Signature</span>
            </div>
        </div>

        <div class="contact-footer">
            <table class="contact-footer-content">
                <tr>
                    <td>
                        <div>Telephone:-{{ $settings->telephone ?? '+251-11-349-5462' }}</div>
                        <div>Email:- {{ $settings->email ?? 'renaissanceschool589@gmail.com' }}</div>
                    </td>
                    <td class="contact-right">
                        <div>PoBox:- {{ $settings->po_box ?? '404/1056' }}</div>
                        <div>Website:-{{ $settings->website ?? 'www.risethiopia.com' }}</div>
                    </td>
                </tr>
            </table>
        </div>
    </div>

</body>
</html>
