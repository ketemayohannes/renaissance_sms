<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Yearly Report Card - {{ $student->full_name }}</title>
    <style>
        @page {
            margin: 0;
            size: A4;
        }
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 10pt;
            margin: 0;
            background: white;
        }
        .page {
            width: 100%;
            height: 100%;
            padding: 20px;
            box-sizing: border-box;
            page-break-after: always;
            position: relative;
        }
        .page:last-child {
            page-break-after: avoid;
        }
        
        /* General Utils */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }
        .w-full { width: 100%; }
        .border-all { border: 1px solid black; border-collapse: collapse; }
        .border-all th, .border-all td { border: 1px solid black; padding: 2px 4px; }
        
        /* Page 1 Specifics */
        .header-box {
            text-align: center;
            font-weight: bold;
            font-size: 14pt;
            text-decoration: underline;
            margin-bottom: 5px;
        }
        
        .comments-grid {
            width: 100%;
            border-collapse: collapse;
        }
        .comments-col {
            width: 60%;
            vertical-align: top;
            padding-right: 10px;
        }
        .info-col {
            width: 40%;
            vertical-align: top;
            border: 2px solid black;
            padding: 0;
        }
        
        .comment-box {
            border: 1px solid black;
            padding: 5px;
            margin-bottom: 5px;
            font-size: 9pt;
        }
        .comment-header {
            font-weight: bold;
            margin-bottom: 3px;
            border-bottom: 1px dotted #ccc;
        }
        .checkbox-line { margin-bottom: 2px; }
        .checkbox {
            display: inline-block;
            width: 10px;
            height: 10px;
            border: 1px solid black;
            margin-right: 5px;
        }
        
        .eval-box, .remark-box {
            padding: 5px;
            border-bottom: 1px solid black;
        }
        .remark-box { border-bottom: none; height: 100%; }
        
        .student-strip {
            border: 2px solid black;
            padding: 5px;
            margin: 10px 0;
            font-weight: bold;
        }
        
        .grades-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
            border: 2px solid black;
        }
        .grades-table th, .grades-table td {
            border: 1px solid black;
            text-align: center;
            padding: 4px;
        }
        .grades-table th { background-color: #f0f0f0; }
        .grades-table td:first-child { text-align: left; font-weight: bold; }
        
        .footer-sig {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
        }
        
        /* Page 2 Specifics */
        .school-header {
            text-align: center;
            margin-bottom: 20px;
        }
        .school-name {
            font-size: 24pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .logo-container {
            text-align: center;
            margin: 10px 0;
        }
        .contact-info {
            font-size: 9pt;
            margin-top: 10px;
        }
        
        .card-title {
            text-align: center;
            font-weight: bold;
            text-decoration: underline;
            margin: 10px 0;
            font-size: 12pt;
        }
        
        .student-details {
            border: 2px solid black;
            padding: 10px;
            margin-bottom: 20px;
            line-height: 1.6;
            font-size: 11pt;
        }
        .detail-row { margin-bottom: 5px; }
        .detail-label { font-weight: normal; }
        .detail-value { font-weight: bold; border-bottom: 1px solid black; display: inline-block; min-width: 50px; text-align: center;}
        
        .parent-sig-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 10pt;
        }
        .parent-sig-table th, .parent-sig-table td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }
        
        .footer-note {
            margin-top: 10px;
            font-size: 9pt;
            text-align: justify;
        }
        
        /* Print Fixes */
        .no-break { page-break-inside: avoid; }
    </style>
</head>
<body>
    @php
        $config = $settings->yearly_config ?? [];
        // Helper to safely get data
        $conf = function($key, $default = '') use ($config) {
            return $config[$key] ?? $default;
        };
        
        // Quarters Data Helper
        $q1 = $quarters->where('term_number', 1)->first();
        $q2 = $quarters->where('term_number', 2)->first();
        $q3 = $quarters->where('term_number', 3)->first();
        $q4 = $quarters->where('term_number', 4)->first();
        
        $s1 = $semesters->first(); // Assuming 2 semesters, sorted by date
        $s2 = $semesters->last();
        
        // Helper for displaying scores nicely
        $fmt = function($val) {
            return \App\Helpers\NumberFormatter::format($val);
        };
        
        $com = function($q) use ($quarterRecords) {
             if(!$q) return '';
             return $quarterRecords[$q->id]->homeroom_teacher_comment ?? '';
        };
    @endphp

    <!-- PAGE 1: FRONT -->
    <div class="page">
        <div class="header-box">
            {{ strtoupper($settings->school_name ?? 'RENAISSANCE SCHOOL') }}<br>
            <span style="font-size: 12pt; text-decoration: none;">Teacher's Comment and Recommendations</span>
        </div>
        
        <table class="comments-grid">
            <tr>
                <td class="comments-col">
                    <!-- Q1 -->
                    @foreach(['First' => $q1, 'Second' => $q2, 'Third' => $q3, 'Fourth' => $q4] as $label => $q)
                    <div class="comment-box">
                        <div class="comment-header">{{ $label }} Quarter/{{ $q ? $q->end_date->format('M, Y') : '' }}</div>
                        <div class="checkbox-line"><span class="checkbox"></span> More effort and attention are needed in_______________________and</div>
                        <div class="checkbox-line" style="padding-left: 19px;">Subject.</div>
                        <div class="checkbox-line"><span class="checkbox"></span> His/her work is excellent so keep it up.</div>
                        <div class="checkbox-line"><span class="checkbox"></span> We appreciated his/her desirable behaviour, but he/she need advice to</div>
                        <div class="checkbox-line" style="padding-left: 19px;">improve_______________________</div>
                        <div class="checkbox-line"><span class="checkbox"></span> Other: {{ $com($q) }}</div>
                        <div class="checkbox-line"><span class="checkbox"></span> Needs to be encouraged to listen and pay attention to his/her lesson.</div>
                        @if($label === 'Fourth')
                            <div style="margin-top: 5px; border-top: 1px dotted #ccc; padding-top: 2px;">
                                <div class="checkbox-line"><span class="checkbox"></span> Promoted to Grade: ________________</div>
                                <div class="checkbox-line"><span class="checkbox"></span> Detained in Grade: ________________</div>
                                <div style="margin-top: 5px;">Homeroom Teacher Name: <u>{{ $section->homeroomTeacher->full_name ?? '________________' }}</u> &nbsp; Signature: ________</div>
                            </div>
                        @endif
                    </div>
                    @endforeach
                </td>
                <td class="info-col">
                    <div class="eval-box">
                        <div class="text-center font-bold" style="text-decoration: underline;">RS<br>Evaluation method</div>
                        <div style="white-space: pre-wrap; font-size: 9pt;">{{ $conf('evaluation_method', "100-90 - A .... Excellent\n89-80 - B .... Very Good\n79-70 - C .... Satisfactory\n69-60 - D .... Fair\n<60 .... Poor") }}</div>
                    </div>
                    <div class="remark-box">
                        <div class="text-center font-bold" style="text-decoration: underline;">Remark</div>
                        <div style="white-space: pre-wrap; font-size: 9pt; text-align: justify;">{{ $conf('remark', "A student who has a final yearly average mark of 50% or above in every subject is to be considered as a better achiever.\nAny mark below 50% needs more effort to improve his/her performance.\nConduct marks of C or below show that some behavioral problem. Which should be improved by close follow up and counselling of parents.") }}</div>
                    </div>
                </td>
            </tr>
        </table>
        
        <div class="student-strip">
            <table width="100%">
                <tr>
                    <td width="15%">Student Full Name:</td>
                    <td width="35%" style="border-bottom: 1px dotted black;">{{ strtoupper($student->full_name) }}</td>
                    <td width="20%" class="text-right">Grade & Section:</td>
                    <td width="30%" style="border-bottom: 1px dotted black; font-weight: bold;">{{ $section->gradeLevel->name }} {{ $section->name }}</td>
                </tr>
            </table>
        </div>
        
        <table class="grades-table">
            <thead>
                <tr>
                    <th rowspan="2">Subject</th>
                    <th>First<br>Quarter<br>(100%)</th>
                    <th>Second<br>Quarter<br>(100%)</th>
                    <th>First<br>Semester<br>(100%)</th>
                    <th>Third<br>Quarter<br>(100%)</th>
                    <th>Fourth<br>Quarter<br>(100%)</th>
                    <th>Second<br>Semester<br>(100%)</th>
                    <th>Final Yearly<br>Average</th>
                </tr>
            </thead>
            <tbody>
                @foreach($subjects as $subject)
                <tr>
                    <td>{{ $subject->name }}</td>
                    <!-- Q1 -->
                    <td>{{ $q1 && isset($quarterMarks[$subject->id][$q1->id]) ? $fmt($quarterMarks[$subject->id][$q1->id]) : '-' }}</td>
                    <!-- Q2 -->
                    <td>{{ $q2 && isset($quarterMarks[$subject->id][$q2->id]) ? $fmt($quarterMarks[$subject->id][$q2->id]) : '-' }}</td>
                    <!-- Sem1 -->
                    <td style="background-color: #f9f9f9; font-weight: bold;">{{ $s1 && isset($semesterMarks[$subject->id][$s1->id]) ? $fmt($semesterMarks[$subject->id][$s1->id]) : '-' }}</td>
                    <!-- Q3 -->
                    <td>{{ $q3 && isset($quarterMarks[$subject->id][$q3->id]) ? $fmt($quarterMarks[$subject->id][$q3->id]) : '-' }}</td>
                    <!-- Q4 -->
                    <td>{{ $q4 && isset($quarterMarks[$subject->id][$q4->id]) ? $fmt($quarterMarks[$subject->id][$q4->id]) : '-' }}</td>
                    <!-- Sem2 -->
                    <td style="background-color: #f9f9f9; font-weight: bold;">{{ $s2 && isset($semesterMarks[$subject->id][$s2->id]) ? $fmt($semesterMarks[$subject->id][$s2->id]) : '-' }}</td>
                    <!-- Yearly Avg -->
                    <td style="background-color: #eee; font-weight: bold;">{{ isset($marks[$subject->id]) ? $fmt($marks[$subject->id]) : '-' }}</td>
                </tr>
                @endforeach
                
                <!-- Totals -->
                <tr style="border-top: 2px solid black;">
                    <td>Total</td>
                    <td>{{ $q1 ? $fmt($quarterTotals[$q1->id] ?? 0) : '-' }}</td>
                    <td>{{ $q2 ? $fmt($quarterTotals[$q2->id] ?? 0) : '-' }}</td>
                    <td class="font-bold">{{ $s1 ? $fmt($semesterTotals[$s1->id] ?? 0) : '-' }}</td>
                    <td>{{ $q3 ? $fmt($quarterTotals[$q3->id] ?? 0) : '-' }}</td>
                    <td>{{ $q4 ? $fmt($quarterTotals[$q4->id] ?? 0) : '-' }}</td>
                    <td class="font-bold">{{ $s2 ? $fmt($semesterTotals[$s2->id] ?? 0) : '-' }}</td>
                    <td class="font-bold">{{ $fmt($totalScore) }}</td>
                </tr>
                 <tr>
                    <td>Average</td>
                    <td>{{ $q1 ? $fmt($quarterAverages[$q1->id] ?? 0) : '-' }}</td>
                    <td>{{ $q2 ? $fmt($quarterAverages[$q2->id] ?? 0) : '-' }}</td>
                    <td class="font-bold">{{ $s1 ? $fmt($semesterAverages[$s1->id] ?? 0) : '-' }}</td>
                    <td>{{ $q3 ? $fmt($quarterAverages[$q3->id] ?? 0) : '-' }}</td>
                    <td>{{ $q4 ? $fmt($quarterAverages[$q4->id] ?? 0) : '-' }}</td>
                    <td class="font-bold">{{ $s2 ? $fmt($semesterAverages[$s2->id] ?? 0) : '-' }}</td>
                    <td class="font-bold">{{ $fmt($average) }}</td>
                </tr>
                <tr>
                    <td>Conduct</td>
                    <td>{{ $quarterRecords[$q1->id]->conduct_grade ?? 'A' }}</td>
                    <td>{{ $quarterRecords[$q2->id]->conduct_grade ?? 'A' }}</td>
                    <td>-</td>
                    <td>{{ $quarterRecords[$q3->id]->conduct_grade ?? 'A' }}</td>
                    <td>{{ $quarterRecords[$q4->id]->conduct_grade ?? 'A' }}</td>
                    <td>-</td>
                    <td>-</td>
                </tr>
                <tr>
                    <td>Absence</td>
                    <td>{{ $quarterRecords[$q1->id]->days_absent ?? 0 }}</td>
                    <td>{{ $quarterRecords[$q2->id]->days_absent ?? 0 }}</td>
                    <td>-</td>
                    <td>{{ $quarterRecords[$q3->id]->days_absent ?? 0 }}</td>
                    <td>{{ $quarterRecords[$q4->id]->days_absent ?? 0 }}</td>
                    <td>-</td>
                    <td>-</td>
                </tr>
                <tr>
                    <td>Rank</td>
                    <td>{{ $quarterRanks[$q1->id] ?? '-' }}</td>
                    <td>{{ $quarterRanks[$q2->id] ?? '-' }}</td>
                    <td class="font-bold">{{ $semesterRanks[$s1->id] ?? '-' }}</td>
                    <td>{{ $quarterRanks[$q3->id] ?? '-' }}</td>
                    <td>{{ $quarterRanks[$q4->id] ?? '-' }}</td>
                    <td class="font-bold">{{ $semesterRanks[$s2->id] ?? '-' }}</td>
                    <td class="font-bold">{{ $rank }} / {{ $totalStudents }}</td>
                </tr>
            </tbody>
        </table>
        
        <table width="100%" style="margin-top: 30px;">
            <tr>
                <td width="50%" style="text-align: left;">
                    Principal Name: <u>&nbsp;&nbsp;{{ $conf('principal_name') }}&nbsp;&nbsp;</u>
                </td>
                <td width="50%" style="text-align: right;">
                    Principal Signature: ______________________
                </td>
            </tr>
        </table>
    </div>

    <!-- PAGE 2: BACK -->
    <div class="page">
        <div class="school-header">
            <div class="school-name">{{ $settings->school_name ?? 'RENAISSANCE SCHOOL' }}</div>
            <div class="logo-container">
                @if($settings->logo_path)
                    <img src="{{ public_path('storage/' . $settings->logo_path) }}" style="height: 80px; width: auto;">
                @else
                    <div style="height: 80px; width: 80px; border: 1px dashed #ccc; display: inline-block; line-height: 80px;">NO LOGO</div>
                @endif
            </div>
            <div class="contact-info">
                Tel: {{ $settings->telephone }}<br>
                Email: {{ $settings->email }} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Po. Box: {{ $settings->po_box }}<br>
                Website: {{ $settings->website }}
            </div>
        </div>
        
        <div class="card-title">STUDENTS REPORT CARD</div>
        
        <div class="student-details">
            <div class="detail-row">
                <span class="detail-label">Student Full Name:</span> <span class="detail-value" style="width: 300px;">{{ strtoupper($student->full_name) }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Gender:</span> <span class="detail-value" style="width: 50px;">{{ $student->gender ?? 'F' }}</span>
            </div>
             <div class="detail-row">
                <span class="detail-label">Region:</span> <span class="detail-value" style="width: 100px;">Addis Ababa</span> &nbsp; 
                <span class="detail-label">Sub City:</span> <span class="detail-value" style="width: 100px;">Kolfe Keraniyo</span> &nbsp;
                <span class="detail-label">Werda:</span> <span class="detail-value" style="width: 50px;">6</span> &nbsp;
                <span class="detail-label">H.No:</span> <span class="detail-value" style="width: 80px;">_______</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Homeroom Teacher Name:</span> <span class="detail-value" style="width: 250px;">{{ $section->homeroomTeacher->full_name ?? '_____________________' }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Academic Year:</span> <span class="detail-value" style="width: 100px;">{{ $academicYear->name }}</span> &nbsp;&nbsp;
                <span class="detail-label">Grade & Section:</span> <span class="detail-value" style="width: 150px;">{{ $section->gradeLevel->name }} {{ $section->name }}</span>
            </div>
             <div class="detail-row" style="margin-top: 10px;">
                <span class="detail-label">Promoted to Grade:</span> <span class="detail-value" style="width: 150px;">__________________</span>
            </div>
        </div>
        
        <!-- Middle Logo Divider -->
        <div class="school-header" style="margin: 40px 0;">
            <div class="school-name">RENAISSANCE SCHOOL</div>
             <div class="logo-container">
                @if($settings->logo_path)
                    <img src="{{ public_path('storage/' . $settings->logo_path) }}" style="height: 60px; width: auto;">
                @else
                    <div style="height: 60px; width: 60px; border: 1px dashed #ccc; display: inline-block;"></div>
                @endif
            </div>
             <div class="contact-info">
                Tel: {{ $settings->telephone }}<br>
                Email: {{ $settings->email }} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Po. Box: {{ $settings->po_box }}<br>
                Website: {{ $settings->website }}
            </div>
        </div>
        
        <table class="parent-sig-table">
            <tr>
                <td colspan="4" style="border: none; padding-bottom: 5px;">Parent's Signature</td>
            </tr>
            <tr style="background-color: #f0f0f0;">
                <th>Evaluation Period</th>
                <th>Parent's Comment</th>
                <th>Parent's Name</th>
                <th>Parent's Signature</th>
                <th>Date</th>
            </tr>
            <tr>
                <td>1st Quarter</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>2nd Quarter</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>3rd Quarter</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
        </table>
        
        <div class="footer-note">
            {{ $conf('parent_instructions', "Please sign the grade report after the first, second, and third quarters and return it back to school immediately after discussing the report with your child. After the fourth quarter the grade report card will be collected by parents. These and all school records should be kept in a safe place for permanent record.") }}
        </div>
    </div>
</body>
</html>
