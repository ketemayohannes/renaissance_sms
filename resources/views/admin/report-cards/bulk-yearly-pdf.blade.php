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
            .no-print-bar, .no-print { display: none !important; }
            body { background: white; }
            .page-container { margin: 0 !important; padding: 0 !important; border: none !important; }
            .page { margin-bottom: 0 !important; padding-top: 2mm !important; }
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
            margin-bottom: 12px;
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
            margin-bottom: 10px;
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

    <div class="no-print-bar">
        <button onclick="window.print()" style="background-color: #4CAF50; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;">Print All Report Cards</button>
        <span style="margin-left: 20px; font-weight: bold; color: #333;">Press Ctrl+P to save as PDF</span>
    </div>

    <div style="height: 60px;" class="no-print"></div>

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
        
        $fmt = function($val) {
            return \App\Helpers\NumberFormatter::format($val);
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
        @endphp

        <div class="page-container" style="{{ $loop->last ? '' : 'page-break-after: always;' }}">
            <!-- PAGE 1: FRONT -->
            <div class="page" style="page-break-after: always;">
                <table class="comments-table">
                    <tr>
                        <td class="comments-col">
                    <div style="text-align: center; margin-bottom: 5px;">
                        <div style="font-weight: bold; font-size: 11pt; text-decoration: underline; line-height: 1.2;">RENAISSANCE SCHOOL</div>
                        <div style="font-weight: bold; font-size: 9pt; text-decoration: underline; line-height: 1.2;">Teacher's Comment and Recommendations</div>
                    </div>
                    @foreach(['First' => $q1, 'Second' => $q2, 'Third' => $q3, 'Fourth' => $q4] as $label => $q)
                    <div class="comment-box">
                        @php
                            $month = $q ? $q->start_date->format('F') : '____';
                            $startYear = $academicYear->start_date->format('Y');
                            $endYearShort = $academicYear->end_date->format('y');
                            $acYearDisplay = "{$startYear}/{$endYearShort}";
                        @endphp
                        <div class="comment-header">{{ $label }} Quarter/{{ $month }}, {{ $acYearDisplay }}</div>
                        
                        @if($label !== 'Fourth')
                        <div class="checkbox-line">
                            <span class="checkbox"></span> 
                            <span>More effort and attention are needed in <span style="border-bottom: 1px solid black; display: inline-block; width: 150px;">&nbsp;</span>,<br><span style="border-bottom: 1px solid black; display: inline-block; width: 150px;">&nbsp;</span> and <span style="border-bottom: 1px solid black; display: inline-block; width: 150px;">&nbsp;</span> Subject.</span>
                        </div>
                        <div class="checkbox-line"><span class="checkbox"></span> <span>His/her work is excellent so keep it up.</span></div>
                        <div class="checkbox-line">
                            <span class="checkbox"></span> 
                            <span>We appreciated his/her desirable behaviour, but he/she need advice to<br>improve <span style="border-bottom: 1px solid black; display: inline-block; width: 150px;">&nbsp;</span>, <span style="border-bottom: 1px solid black; display: inline-block; width: 150px;">&nbsp;</span></span>
                        </div>
                        <div class="checkbox-line"><span class="checkbox"></span> <span>others <span style="border-bottom: 1px solid black; display: inline-block; width: 250px; text-transform: lowercase;">{{ ($q && isset($qRecords[$q->id])) ? $qRecords[$q->id]->homeroom_teacher_comment : '' }}&nbsp;</span></span></div>
                        <div class="checkbox-line"><span class="checkbox"></span> <span>Needs to be encouraged to listen and pay attention to his/her lesson.</span></div>
                        @else
                            <div class="checkbox-line"><span class="checkbox"></span> <span>Promoted to Grade: <span style="border-bottom: 1px solid black; display: inline-block; width: 150px;">&nbsp;</span></span></div>
                            <div class="checkbox-line"><span class="checkbox"></span> <span>Detained in Grade: <span style="border-bottom: 1px solid black; display: inline-block; width: 150px;">&nbsp;</span></span></div>
                            <div style="margin-top: 5px; font-size: 8pt;">Homeroom Teacher Name: <span style="border-bottom: 1px solid black; display: inline-block; min-width: 150px;">{{ $section->homeroomTeacher->full_name ?? '' }}&nbsp;</span> &nbsp; Signature: <span style="border-bottom: 1px solid black; display: inline-block; width: 120px;">&nbsp;</span></div>
                        @endif
                    </div>
                    @endforeach
                </td>
                <td class="info-col">
                    <div class="eval-box">
                        <div style="text-align: center; font-weight: bold;">
                            <div style="font-size: 10pt;">RS</div>
                            <div style="font-size: 9pt;">Evaluation Method</div>
                        </div>
                        <div style="white-space: pre-wrap; font-size: 9pt; margin-top: 5px;">{{ $conf('evaluation_method', "100-90 = A .... Excellent\n89-80 = B .... Very Good\n79-70 = C .... Satisfactory\n69-60 = D .... Fair\n<60 .... Poor") }}</div>
                    </div>
                    <div class="remark-box" style="border-bottom: 1px solid black; height: auto;">
                        <div style="text-align: center; font-weight: bold; font-size: 9pt; text-decoration: underline;">Remark</div>
                        <div style="white-space: pre-wrap; font-size: 8pt; text-align: justify; margin-top: 3px;">{{ $conf('remark', "A student who has a final yearly average mark of 75% or above in every subject is to be considered as a better achiever. Any mark below 75% needs more effort to improve his/her performance. Conduct marks of C or below show that some behavioural problem. Which should be improved by close follow up and counselling of parents.") }}</div>
                    </div>
                </td>
                    </tr>
                </table>
                
                <div class="student-strip">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div style="font-weight: bold;">Student Full Name: <span style="text-decoration: underline; padding: 0 5px;">{{ strtoupper($student->full_name) }}</span></div>
                        <div style="font-weight: bold;">Grade & Section: <span style="text-decoration: underline; padding: 0 5px;">{{ str_replace('Grade ', '', $section->gradeLevel->name) }}{{ $section->name }}</span></div>
                    </div>
                </div>
                
                <table class="grades-table">
                    <thead>
                        <tr>
                            <th rowspan="2">Subject</th>
                            <th>First<br>Quarter<br>(100%)</th>
                            <th>Second<br>Quarter<br>(100%)</th>
                            <th>First Semester<br>(100%)</th>
                            <th>Third<br>Quarter<br>(100%)</th>
                            <th>Fourth<br>Quarter<br>(100%)</th>
                            <th>Second Semester<br>(100%)</th>
                            <th>Final Yearly<br>Average</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($studentSubjects as $subject)
                        <tr>
                            <td>{{ $subject->name }}</td>
                            <td>{{ $q1 && isset($qMarks[$subject->id][$q1->id]) ? $fmt($qMarks[$subject->id][$q1->id]) : '-' }}</td>
                            <td>{{ $q2 && isset($qMarks[$subject->id][$q2->id]) ? $fmt($qMarks[$subject->id][$q2->id]) : '-' }}</td>
                            <td style="background-color: #f9f9f9; font-weight: bold;">{{ $s1 && isset($sMarks[$subject->id][$s1->id]) ? $fmt($sMarks[$subject->id][$s1->id]) : '-' }}</td>
                            <td>{{ $q3 && isset($qMarks[$subject->id][$q3->id]) ? $fmt($qMarks[$subject->id][$q3->id]) : '-' }}</td>
                            <td>{{ $q4 && isset($qMarks[$subject->id][$q4->id]) ? $fmt($qMarks[$subject->id][$q4->id]) : '-' }}</td>
                            <td style="background-color: #f9f9f9; font-weight: bold;">{{ $s2 && isset($sMarks[$subject->id][$s2->id]) ? $fmt($sMarks[$subject->id][$s2->id]) : '-' }}</td>
                            <td style="background-color: #eee; font-weight: bold;">{{ isset($marks[$subject->id]) ? $fmt($marks[$subject->id]) : '-' }}</td>
                        </tr>
                        @endforeach
                        
                        <!-- Totals -->
                        <tr class="footer-row">
                            <td>Total</td>
                            <td>{{ $q1 ? $fmt($qStats[$q1->id]['total'] ?? 0) : '-' }}</td>
                            <td>{{ $q2 ? $fmt($qStats[$q2->id]['total'] ?? 0) : '-' }}</td>
                            <td>{{ $s1 ? $fmt($sStats[$s1->id]['total'] ?? 0) : '-' }}</td>
                            <td>{{ $q3 ? $fmt($qStats[$q3->id]['total'] ?? 0) : '-' }}</td>
                            <td>{{ $q4 ? $fmt($qStats[$q4->id]['total'] ?? 0) : '-' }}</td>
                            <td>{{ $s2 ? $fmt($sStats[$s2->id]['total'] ?? 0) : '-' }}</td>
                            <td>{{ $fmt($totalScore) }}</td>
                        </tr>
                         <tr class="footer-row">
                            <td>Average</td>
                            <td>{{ $q1 ? $fmt($qStats[$q1->id]['avg'] ?? 0) : '-' }}</td>
                            <td>{{ $q2 ? $fmt($qStats[$q2->id]['avg'] ?? 0) : '-' }}</td>
                            <td>{{ $s1 ? $fmt($sStats[$s1->id]['avg'] ?? 0) : '-' }}</td>
                            <td>{{ $q3 ? $fmt($qStats[$q3->id]['avg'] ?? 0) : '-' }}</td>
                            <td>{{ $q4 ? $fmt($qStats[$q4->id]['avg'] ?? 0) : '-' }}</td>
                            <td>{{ $s2 ? $fmt($sStats[$s2->id]['avg'] ?? 0) : '-' }}</td>
                            <td>{{ $fmt($average) }}</td>
                        </tr>
                        <tr class="footer-row">
                            <td>Conduct</td>
                            <td>{{ $q1 ? ($qRecords[$q1->id]->conduct_grade ?? 'A') : '-' }}</td>
                            <td>{{ $q2 ? ($qRecords[$q2->id]->conduct_grade ?? 'A') : '-' }}</td>
                            <td>-</td>
                            <td>{{ $q3 ? ($qRecords[$q3->id]->conduct_grade ?? 'A') : '-' }}</td>
                            <td>{{ $q4 ? ($qRecords[$q4->id]->conduct_grade ?? 'A') : '-' }}</td>
                            <td>-</td>
                            <td>-</td>
                        </tr>
                        <tr class="footer-row">
                            <td>Absence</td>
                            <td>{{ ($data['subTermAttendance'][$q1->id]['absent'] ?? 0) > 0 ? $data['subTermAttendance'][$q1->id]['absent'] : '_' }}</td>
                            <td>{{ ($data['subTermAttendance'][$q2->id]['absent'] ?? 0) > 0 ? $data['subTermAttendance'][$q2->id]['absent'] : '_' }}</td>
                            <td>-</td>
                            <td>{{ ($data['subTermAttendance'][$q3->id]['absent'] ?? 0) > 0 ? $data['subTermAttendance'][$q3->id]['absent'] : '_' }}</td>
                            <td>{{ ($data['subTermAttendance'][$q4->id]['absent'] ?? 0) > 0 ? $data['subTermAttendance'][$q4->id]['absent'] : '_' }}</td>
                            <td>-</td>
                            <td>{{ ($attendance['absent'] ?? 0) > 0 ? $attendance['absent'] : '_' }}</td>
                        </tr>
                        <tr class="footer-row">
                            <td>Rank</td>
                            <td>{{ $q1 ? ($qStats[$q1->id]['rank'] ?? '-') : '-' }}</td>
                            <td>{{ $q2 ? ($qStats[$q2->id]['rank'] ?? '-') : '-' }}</td>
                            <td>{{ $s1 ? ($sStats[$s1->id]['rank'] ?? '-') : '-' }}</td>
                            <td>{{ $q3 ? ($qStats[$q3->id]['rank'] ?? '-') : '-' }}</td>
                            <td>{{ $q4 ? ($qStats[$q4->id]['rank'] ?? '-') : '-' }}</td>
                            <td>{{ $s2 ? ($sStats[$s2->id]['rank'] ?? '-') : '-' }}</td>
                            <td>{{ $rankValue }} / {{ $totalStudents }}</td>
                        </tr>
                    </tbody>
                </table>
                
                <div style="margin-top: 35px; width: 100%;">
                    <table width="100%">
                        <tr>
                            <td width="50%" style="text-align: center;">
                                <div style="margin-bottom: 5px; border-bottom: 1px solid black; width: 220px; margin-left: auto; margin-right: auto; height: 2px;"></div>
                                <div style="font-size: 9pt; font-weight: bold;">Principal Name</div>
                            </td>
                            <td width="50%" style="text-align: center;">
                                <div style="margin-bottom: 5px; border-bottom: 1px solid black; width: 220px; margin-left: auto; margin-right: auto; height: 2px;"></div>
                                <div style="font-size: 9pt; font-weight: bold;">Principal Signature</div>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- PAGE 2: BACK -->
            <div class="page" style="clear: both;">
                <div class="back-page-container">
                    <!-- Box 1: School Header -->
                    <div class="school-header-box-new">
                        <div class="school-name-large-new">{{ strtoupper($settings->school_name ?? 'RENAISSANCE SCHOOL') }}</div>
                        <div style="text-align: center; margin: 10px 0;">
                             @if($logoBase64)
                                <img src="{{ $logoBase64 }}" class="logo-img-new">
                            @endif
                        </div>
                        <table class="header-details-table">
                            <tr>
                                <td width="50%" style="text-align: left;">
                                    Tel: {{ $settings->telephone }}<br>
                                    Email: {{ $settings->email }}
                                </td>
                                <td width="50%" style="text-align: right;">
                                    Po. Box: {{ $settings->po_box }}<br>
                                    Website: {{ $settings->website }}
                                </td>
                            </tr>
                        </table>
                    </div>

                    <!-- Box 2: Student Details -->
                    <div class="student-details-box-new">
                        <div class="card-title-new">STUDENTS REPORT CARD</div>
                        <table class="details-grid-new">
                            <tr>
                                <td colspan="4">Student Full Name: <span class="underlined-val-new" style="min-width: 350px;">{{ strtoupper($student->full_name) }}</span></td>
                            </tr>
                            <tr>
                                <td colspan="4">Gender: <span class="underlined-val-new" style="min-width: 60px;">{{ $student->gender ?? 'M' }}</span></td>
                            </tr>
                            <tr>
                                <td colspan="4">
                                    Region: <span class="underlined-val-new">Addis Ababa</span> &nbsp; &nbsp;
                                    Sub City: <span class="underlined-val-new">Kolfe Keraniyo</span> &nbsp; &nbsp;
                                    Werda: <span class="underlined-val-new">6</span> &nbsp; &nbsp;
                                    H.No: <span style="border-bottom: 1px solid black; display: inline-block; width: 180px;">&nbsp;</span>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="4">Homeroom Teacher Name: <span style="border-bottom: 1px solid black; display: inline-block; width: 400px;">{{ $section->homeroomTeacher->full_name ?? '' }}&nbsp;</span></td>
                            </tr>
                            <tr>
                                <td colspan="2">Academic Year: <span class="underlined-val-new">{{ $academicYear->name }}G.C 2017E.C</span></td>
                                <td colspan="2" style="text-align: right;">Grade & Section: <span class="underlined-val-new" style="min-width: 80px;">{{ str_replace('Grade ', '', $section->gradeLevel->name) }}{{ $section->name }}</span></td>
                            </tr>
                            <tr>
                                <td colspan="4">Promoted to Grade: <span style="border-bottom: 1px solid black; display: inline-block; width: 450px;">&nbsp;</span></td>
                            </tr>
                        </table>
                    </div>

                    <!-- Box 3: Repeated Header -->
                    <div class="school-header-box-new" style="margin-top: 8px;">
                        <div class="school-name-large-new">{{ strtoupper($settings->school_name ?? 'RENAISSANCE SCHOOL') }}</div>
                        <div style="text-align: center; margin: 10px 0;">
                             @if($logoBase64)
                                <img src="{{ $logoBase64 }}" class="logo-img-new">
                            @endif
                        </div>
                        <table class="header-details-table">
                            <tr>
                                <td width="50%" style="text-align: left;">
                                    Tel: {{ $settings->telephone }}<br>
                                    Email: {{ $settings->email }}
                                </td>
                                <td width="50%" style="text-align: right;">
                                    Po. Box: {{ $settings->po_box }}<br>
                                    Website: {{ $settings->website }}
                                </td>
                            </tr>
                        </table>
                    </div>

                    <!-- Box 4: Parent Signature -->
                    <div class="signature-box-new" style="margin-top: 15px;">
                        <div class="sig-header-new">Parent's Signature</div>
                        <table class="sig-table-new">
                            <thead>
                                <tr>
                                    <th width="20%">Evaluation Period</th>
                                    <th width="25%">Parent's Comment</th>
                                    <th width="25%">Parent's Name</th>
                                    <th width="20%">Parent's Signature</th>
                                    <th width="10%">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr><td>1st Quarter</td><td>&nbsp;</td><td></td><td></td><td></td></tr>
                                <tr><td>2nd Quarter</td><td>&nbsp;</td><td></td><td></td><td></td></tr>
                                <tr><td>3rd Quarter</td><td>&nbsp;</td><td></td><td></td><td></td></tr>
                            </tbody>
                        </table>
                        <div class="footer-note-new">
                            {{ $conf('parent_instructions', "Please sign the grade report after the first, second, and third quarters and return it back to school immediately after discussing the report with your child. After the fourth quarter the grade report card will be collected by parents. These and all school records should be kept in a safe place for permanent record.") }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

</body>
</html>
