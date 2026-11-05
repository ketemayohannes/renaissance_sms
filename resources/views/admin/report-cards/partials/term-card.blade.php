{{--
    One quarter/semester report card. Shared by pdf (single student) and bulk-pdf (whole
    section); each keeps its own <style> so they print as before.

    Expects the per-student $data array (studentSubjects, studentQuarterMarks,
    studentQuarterStats, studentQuarterRecords, subTermAttendance, rank) plus $student,
    $marks, $termRecord, $totalScore, $average, $attendance, $totalStudents, $term,
    $quarters, $isSemester, $isKindergarten, $section, $academicYear, $settings.
--}}
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
                    @else
                        <th style="width: 40%">Result</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($data['studentSubjects'] as $subject)
                    <tr>
                        <td>{{ $subject->name }}</td>
                        @if($isSemester)
                            @php 
                                $subjTotal = 0; 
                                $subjCount = 0;
                                $qMarks = $data['studentQuarterMarks'][$subject->id] ?? [];
                            @endphp
                            @foreach($quarters as $quarter)
                                @php
                                    $score = $qMarks[$quarter->id] ?? null;
                                    if($score !== null) { $subjTotal += $score; $subjCount++; }
                                @endphp
                                <td>{{ $score !== null ? (($isKindergarten ?? false) ? \App\Helpers\KindergartenGradeHelper::toGrade($score) : \App\Helpers\NumberFormatter::format($score)) : '-' }}</td>
                            @endforeach
                            <td>{{ $subjCount > 0 ? (($isKindergarten ?? false) ? \App\Helpers\KindergartenGradeHelper::toGrade($subjTotal / $subjCount) : \App\Helpers\NumberFormatter::format($subjTotal / $subjCount)) : '-' }}</td>
                        @else
                            <td>{{ isset($marks[$subject->id]) ? (($isKindergarten ?? false) ? \App\Helpers\KindergartenGradeHelper::toGrade($marks[$subject->id]) : \App\Helpers\NumberFormatter::format($marks[$subject->id])) : '-' }}</td>
                        @endif
                    </tr>
                @endforeach
                
                @if(!($isKindergarten ?? false))
                <tr class="total-row">
                    <td style="text-align: center">Total</td>
                    @if($isSemester)
                        @foreach($quarters as $quarter)
                            <td>{{ \App\Helpers\NumberFormatter::format($data['studentQuarterStats'][$quarter->id]['total'] ?? 0) }}</td>
                        @endforeach
                    @endif
                    <td>{{ \App\Helpers\NumberFormatter::format($totalScore) }}</td>
                </tr>
                <tr class="total-row">
                    <td style="text-align: center">Average</td>
                    @if($isSemester)
                        @foreach($quarters as $quarter)
                            <td>{{ \App\Helpers\NumberFormatter::format($data['studentQuarterStats'][$quarter->id]['avg'] ?? 0) }}</td>
                        @endforeach
                    @endif
                    <td>{{ \App\Helpers\NumberFormatter::format($average) }}</td>
                </tr>
                @endif
                <tr class="total-row">
                    <td style="text-align: center">Conduct</td>
                    @if($isSemester)
                        @foreach($quarters as $quarter)
                            <td>{{ $data['studentQuarterRecords'][$quarter->id]->conduct_grade ?? 'A' }}</td>
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
                                // Access subTermAttendance from $data
                                $absCount = $data['subTermAttendance'][$quarter->id]['absent'] ?? 0;
                                $display = $absCount > 0 ? $absCount : '_';
                            @endphp
                            <td>{{ $display }}</td>
                        @endforeach
                        <td>{{ ($attendance['absent'] ?? 0) > 0 ? $attendance['absent'] : '_' }}</td>
                    @else
                        <td>{{ ($attendance['absent'] ?? 0) > 0 ? $attendance['absent'] : '_' }}</td>
                    @endif
                </tr>

                @if(!($isKindergarten ?? false) && ($settings->template_config['show_rank'] ?? true))
                <tr class="total-row">
                    <td style="text-align: center">Rank</td>
                    @if($isSemester)
                        @foreach($quarters as $quarter)
                            <td>{{ $data['studentQuarterStats'][$quarter->id]['rank'] ?? '-' }}</td>
                        @endforeach
                    @endif
                    <td>{{ $data['rank'] }} / {{ $totalStudents }}</td>
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
            <!-- Fallback defaults -->
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
