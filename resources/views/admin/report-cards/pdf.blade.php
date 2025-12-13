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
            min-height: 130px; /* Increased for larger logo */
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
            font-size: 24pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #000;
            margin-bottom: 10px;
            padding-left: 100px; /* Offset to ensure no overlap if text is long, though centering usually handles it */
            padding-right: 100px;
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
            border: 2px solid black;
            font-family: sans-serif;
        }
        .subjects-table th, .subjects-table td {
            border: 1px solid black;
            padding: 5px;
            text-align: center;
        }
        .subjects-table th {
            font-weight: bold;
            background-color: #f0f0f0; /* Slight gray header */
        }
        .subjects-table td:first-child {
            text-align: left;
            padding-left: 5px;
        }
        .total-row td {
            font-weight: bold;
            border-top: 2px solid black !important;
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
            border: 2px solid black; /* Thicker border */
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
        
        .footer {
            margin-top: 50px;
            width: 100%;
            font-family: sans-serif;
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
            margin-top: 50px;
            display: block;
            width: 100%;
        }
        /* Print Styles */
        @media print {
            .no-print-bar, .spacer {
                display: none !important;
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

    <div class="no-print-bar">
        <button onclick="window.print()" class="print-btn" style="background-color: #4CAF50; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;">Print Report Card</button>
        <span style="margin-left: 20px; font-weight: bold; color: #333;">Press Ctrl+P to save as PDF</span>
    </div>
    <!-- Spacer for fixed header -->
    <div class="spacer"></div>

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
            <td>{{ $academicYear->name }}</td>
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
                            @foreach($quarters as $quarter)
                                <th style="width: 20%">{{ str_replace('Quarter ', 'Q', $quarter->name) }}</th>
                            @endforeach
                            <th style="width: 20%">Avg</th>
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
                                    <td>{{ $score !== null ? round($score) : '-' }}</td>
                                @endforeach
                                <td>{{ $subjCount > 0 ? round($subjTotal / $subjCount) : '-' }}</td>
                            @else
                                <td>{{ isset($marks[$subject->id]) ? round($marks[$subject->id]) : '-' }}</td>
                            @endif
                        </tr>
                    @endforeach
                    
                    <tr class="total-row">
                        <td style="text-align: center">Total</td>
                        @if($isSemester)
                            @foreach($quarters as $quarter)
                                <td>{{ round($quarterTotals[$quarter->id] ?? 0) }}</td>
                            @endforeach
                            <td>{{ round($totalScore) }}</td>
                        @else
                            <td>{{ round($totalScore) }}</td>
                        @endif
                    </tr>
                    <tr class="total-row">
                        <td style="text-align: center">Average</td>
                        @if($isSemester)
                            @foreach($quarters as $quarter)
                                <td>{{ number_format($quarterAverages[$quarter->id] ?? 0, 2) }}</td>
                            @endforeach
                            <td>{{ number_format($average, 2) }}</td>
                        @else
                            <td>{{ number_format($average, 2) }}</td>
                        @endif
                    </tr>
                    <tr class="total-row">
                        <td style="text-align: center">Conduct</td>
                        @if($isSemester)
                            @foreach($quarters as $quarter)
                                <td>{{ $quarterRecords[$quarter->id]->conduct_grade ?? 'A' }}</td>
                            @endforeach
                            <td>{{ $termRecord->conduct_grade ?? 'A' }}</td>
                        @else
                            <td>{{ $termRecord->conduct_grade ?? 'A' }}</td>
                        @endif
                    </tr>
                    <tr class="total-row">
                        <td style="text-align: center">Absence</td>
                        @if($isSemester)
                            @foreach($quarters as $quarter)
                                <td>{{ $quarterRecords[$quarter->id]->days_absent ?? '_' }}</td>
                            @endforeach
                            <td>{{ $termRecord->days_absent ?? '_' }}</td>
                        @else
                            <td>{{ $termRecord->days_absent ?? '_' }}</td>
                        @endif
                    </tr>
                    @if($settings->template_config['show_rank'] ?? true)
                    <tr class="total-row">
                        <td style="text-align: center">Rank</td>
                        @if($isSemester)
                            @foreach($quarters as $quarter)
                                <td>{{ $quarterRanks[$quarter->id] ?? '-' }}</td>
                            @endforeach
                        @endif
                        <td>{{ $rank }} / {{ $totalStudents }}</td>
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
                <strong>Homeroom Teacher Name:</strong> _______________
            </div>

             <div style="margin-top: 15px;">
                <strong>Signature:</strong> ______________________
            </div>

        </div>

    </div>

    <div class="footer clearfix">
        <div class="signature-box">
             <span class="signature-line">Principal Name</span>
        </div>
        <div class="signature-box right">
             <span class="signature-line">Principal Signature</span>
        </div>
    </div>

    <div style="clear: both; text-align: center; margin-top: 30px; padding-top: 15px; border-top: 1px solid #ccc; font-size: 10pt; color: #333;">
        @if($settings->school_address)
            <div>{{ $settings->school_address }}</div>
        @endif
        <div style="margin-top: 5px;">
            @if($settings->telephone)
                Tel: {{ $settings->telephone }}
            @endif
            @if($settings->telephone && $settings->website)
                &nbsp;|&nbsp;
            @endif
            @if($settings->website)
                {{ $settings->website }}
            @endif
        </div>
    </div>

</body>
</html>
