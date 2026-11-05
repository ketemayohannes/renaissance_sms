{{--
    Both pages of one yearly report card. Shared by yearly-pdf (single student) and
    bulk-yearly-pdf (whole section); each keeps its own <style> so they print as before.

    Expects: $student, $studentSubjects, $marks, $termRecord, $totalScore, $average,
    $rankValue, $totalStudents, $qMarks, $qStats, $qRecords, $sMarks, $sStats,
    $subTermAttendance, $attendance, $promotedToGrade, $promotionStatus,
    plus the view-level $q1..$q4, $s1, $s2, $fmt, $conf, $logoBase64, $settings,
    $section, $academicYear, $isKindergarten. $secondPageStyle is optional.
--}}
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
                @php
                    $isRetained = $promotionStatus === 'retained';
                    $gradeShort = $promotedToGrade ? str_replace('Grade ', '', $promotedToGrade) : null;
                @endphp
                <div class="checkbox-line">
                    <span class="checkbox" style="display: inline-block; vertical-align: middle; text-align: center; line-height: 12px; font-family: DejaVu Sans, sans-serif; font-weight: bold;">{{ (!$isRetained && $gradeShort) ? '✓' : '' }}</span> 
                    <span>Promoted to Grade: <span style="border-bottom: 1px solid black; display: inline-block; min-width: 150px; text-align: center;">{{ (!$isRetained && $gradeShort) ? $gradeShort : '' }}</span></span>
                </div>
                <div class="checkbox-line">
                    <span class="checkbox" style="display: inline-block; vertical-align: middle; text-align: center; line-height: 12px; font-family: DejaVu Sans, sans-serif; font-weight: bold;">{{ ($isRetained && $gradeShort) ? '✓' : '' }}</span> 
                    <span>Detained in Grade: <span style="border-bottom: 1px solid black; display: inline-block; min-width: 150px; text-align: center;">{{ ($isRetained && $gradeShort) ? $gradeShort : '' }}</span></span>
                </div>
                <div style="margin-top: 5px; font-size: 8pt;">Homeroom Teacher Name: <span style="border-bottom: 1px solid black; display: inline-block; min-width: 150px; text-align: center;">{{ ucwords(mb_strtolower($section->homeroomTeacher->name ?? '')) }}&nbsp;</span> &nbsp; Signature: <span style="border-bottom: 1px solid black; display: inline-block; width: 120px;">&nbsp;</span></div>
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
            @if(!($isKindergarten ?? false))
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
            @endif
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
                <td>{{ ($subTermAttendance[$q1->id]['absent'] ?? 0) > 0 ? $subTermAttendance[$q1->id]['absent'] : '_' }}</td>
                <td>{{ ($subTermAttendance[$q2->id]['absent'] ?? 0) > 0 ? $subTermAttendance[$q2->id]['absent'] : '_' }}</td>
                <td>-</td>
                <td>{{ ($subTermAttendance[$q3->id]['absent'] ?? 0) > 0 ? $subTermAttendance[$q3->id]['absent'] : '_' }}</td>
                <td>{{ ($subTermAttendance[$q4->id]['absent'] ?? 0) > 0 ? $subTermAttendance[$q4->id]['absent'] : '_' }}</td>
                <td>-</td>
                <td>-</td>
            </tr>
            @if(!($isKindergarten ?? false) && ($settings->template_config['show_rank'] ?? true))
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
            @endif
        </tbody>
    </table>
    
    <div style="margin-top: 25px; width: 100%;">
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
<div class="page"@isset($secondPageStyle) style="{{ $secondPageStyle }}"@endisset>
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
                    <td colspan="4">Homeroom Teacher Name: <span style="border-bottom: 1px solid black; display: inline-block; width: 400px; text-align: center;">{{ ucwords(mb_strtolower($section->homeroomTeacher->name ?? '')) }}&nbsp;</span></td>
                </tr>
                <tr>
                    <td colspan="2">Academic Year: <span class="underlined-val-new">{{ $academicYear->name }}G.C {{ \App\Helpers\EthiopianDateHelper::fromGregorian($academicYear->start_date)->format('Y') }}E.C</span></td>
                    <td colspan="2" style="text-align: right;">Grade & Section: <span class="underlined-val-new" style="min-width: 80px;">{{ str_replace('Grade ', '', $section->gradeLevel->name) }}{{ $section->name }}</span></td>
                </tr>
                <tr>
                    @if($promotionStatus === 'retained')
                        <td colspan="4">Detained in Grade: <span style="border-bottom: 1px solid black; display: inline-block; min-width: 450px; text-align: center;">{{ $promotedToGrade ?? '' }}</span></td>
                    @else
                        <td colspan="4">Promoted to Grade: <span style="border-bottom: 1px solid black; display: inline-block; min-width: 450px; text-align: center;">{{ $promotedToGrade ?? '' }}</span></td>
                    @endif
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
        <div class="signature-box-new" style="margin-top: 8px;">
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
