<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Bulk ID Cards - {{ $section->name }}</title>
    <style>
        @page { margin: 0; size: 85.6mm 54mm landscape; }
        html, body {
            margin: 0; padding: 0; width: 85.6mm; height: 54mm;
            overflow: hidden; font-family: 'DejaVu Sans', sans-serif;
            background-color: white; line-height: 1;
        }
        .card { width: 85.6mm; height: 54mm; position: relative; overflow: hidden; box-sizing: border-box; page-break-after: always; }
        .card-front { background: linear-gradient(135deg, {{ $idSettings->primary_color ?? '#ffffff' }}, {{ $idSettings->secondary_color ?? '#f8fafc' }}); }
        .card-back { background-color: #ffffff; }
        .abs { position: absolute; overflow: hidden; }
        
        /* Front Side */
        .school-logo { top: 3mm; left: 4mm; width: 9mm; height: 9mm; }
        .school-logo img { width: 9mm; height: 9mm; object-fit: contain; }
        .school-header-text { top: 3.2mm; left: 15mm; width: 55mm; }
        .school-name { font-size: 8.5pt; font-weight: bold; text-transform: uppercase; color: {{ ($idSettings->text_color == '#ffffff' || $idSettings->text_color == '#FFFFFF') ? '#1e293b' : ($idSettings->text_color ?? '#1e293b') }}; margin-bottom: 0.2mm; }
        .id-label { font-size: 5pt; letter-spacing: 0.3mm; opacity: 0.6; font-weight: bold; color: {{ ($idSettings->text_color == '#ffffff' || $idSettings->text_color == '#FFFFFF') ? '#1e293b' : ($idSettings->text_color ?? '#1e293b') }}; }
        .year-badge-box { top: 3mm; right: 4mm; font-size: 6pt; font-weight: bold; background: #f1f5f9; padding: 0.5mm 1.5mm; border-radius: 0.8mm; border: 0.1mm solid #e2e8f0; color: #1e293b; }
        .student-photo-box { top: 14.5mm; left: 4mm; width: 22mm; height: 28mm; background: #f1f5f9; border: 0.1mm solid #e2e8f0; }
        .student-photo-box.rounded { border-radius: 2mm; }
        .student-photo-box.circle { border-radius: 11mm; }
        .student-photo-box img { width: 22mm; height: 28mm; object-fit: cover; }
        .student-details-box { top: 14.5mm; left: 30mm; width: 52mm; }
        .student-display-name { font-size: 10pt; font-weight: bold; color: {{ ($idSettings->text_color == '#ffffff' || $idSettings->text_color == '#FFFFFF') ? '#1e293b' : ($idSettings->text_color ?? '#1e293b') }}; margin-bottom: 2mm; white-space: nowrap; }
        .field-row { margin-bottom: 1mm; }
        .field-label { font-size: 4pt; font-weight: bold; opacity: 0.5; text-transform: uppercase; margin-bottom: 0.1mm; display: block; }
        .field-value { font-size: 8pt; font-weight: bold; color: {{ ($idSettings->text_color == '#ffffff' || $idSettings->text_color == '#FFFFFF') ? '#334155' : ($idSettings->text_color ?? '#1e293b') }}; display: block; }
        .card-footer { bottom: 3mm; left: 4mm; right: 4mm; border-top: 0.15mm solid #e2e8f0; padding-top: 1.2mm; font-size: 5pt; color: #64748b; }

        /* Back Side - Recalibrated */
        .back-title { top: 2.5mm; left: 4mm; right: 4mm; font-size: 7.5pt; font-weight: bold; border-bottom: 0.15mm solid #e2e8f0; padding-bottom: 1mm; text-transform: uppercase; color: {{ ($idSettings->text_color ?? '#1e293b') }}; }
        .back-info-box { top: 11mm; left: 4mm; width: 77mm; height: 16mm; }
        .back-field { margin-bottom: 1.2mm; }
        .back-rules-box { top: 28mm; left: 4mm; width: 77mm; height: 12mm; font-size: 5.5pt; opacity: 0.8; color: #334155; line-height: 1.2; overflow: hidden; }
        .barcode-box { bottom: 7.5mm; left: 4mm; padding: 0.5mm; background: white; border: 0.1mm solid #e2e8f0; }
        .back-school-identity { bottom: 7.5mm; right: 4mm; text-align: right; width: 45mm; }
    </style>
</head>
<body>
    @foreach($students as $student)
    <div class="card card-front">
        <div class="abs school-logo">
             @if($idSettings->logo_path && extension_loaded('gd'))<img src="{{ public_path('storage/' . $idSettings->logo_path) }}" alt="Logo">@else<div style="width: 9mm; height: 9mm; background: #f8fafc; border: 0.1mm dashed #cbd5e1; text-align: center; line-height: 9mm; font-size: 3pt;">LOGO</div>@endif
        </div>
        <div class="abs school-header-text">
            <div class="school-name">{{ $idSettings->school_name ?? 'Renaissance School' }}</div>
            <div class="id-label">STUDENT IDENTIFICATION CARD</div>
        </div>
        <div class="abs year-badge-box">{{ $academicYear->name ?? date('Y') }}</div>
        <div class="abs student-photo-box {{ $idSettings->photo_shape ?? 'rounded' }}">
            @if($student->photo && extension_loaded('gd'))<img src="{{ public_path('storage/' . $student->photo) }}" alt="Photo">@else<div style="padding-top: 11mm; font-size: 5pt; color: #94a3b8; text-align: center;">PHOTO</div>@endif
        </div>
        <div class="abs student-details-box"><div class="student-display-name">{{ strtoupper($student->full_name) }}</div>@php $frontFields = array_slice($idSettings->front_fields ?? ['student_id', 'grade'], 0, 4); @endphp @foreach($frontFields as $field) @if($field == 'full_name') @continue @endif<div class="field-row"><span class="field-label">@switch($field) @case('student_id') ID NUMBER @break @case('grade') GRADE @break @case('section') SECTION @break @case('gender') GENDER @break @case('date_of_birth') DATE OF BIRTH @break @case('blood_group') BLOOD GROUP @break @default {{ strtoupper(str_replace('_', ' ', $field)) }} @endswitch</span><span class="field-value">@switch($field) @case('student_id') {{ $student->student_id }} @break @case('grade') {{ str_replace('Grade ', '', $student->currentEnrollment?->section?->gradeLevel?->name ?? '-') }} @break @case('section') {{ $student->currentEnrollment?->section?->name ?? '-' }} @break @case('gender') {{ ucfirst($student->gender ?? '-') }} @break @case('date_of_birth') {{ $student->date_of_birth?->format('d/m/Y') ?? '-' }} @break @case('blood_group') {{ $student->medicalInfo?->blood_group ?? '-' }} @break @default - @endswitch</span></div>@endforeach</div>
        <div class="abs card-footer"><table width="100%" cellspacing="0" cellpadding="0"><tr><td>Valid for Academic Year {{ $academicYear->name }}</td><td style="text-align: right; font-weight: bold;">STUDENT ID</td></tr></table></div>
    </div>
    <div class="card card-back" style="@if($loop->last) page-break-after: avoid; @endif">
        <div class="abs back-title">Contact Information & Identity</div>
        <div class="abs back-info-box">
            @php $backFields = array_slice($idSettings->back_fields ?? ['guardian_phone', 'emergency_contact'], 0, 3); @endphp
            @foreach($backFields as $field)
                <div class="back-field">
                    <span style="font-weight: bold; opacity: 0.6; font-size: 3.8pt; text-transform: uppercase; display: block;">@switch($field) @case('emergency_contact') EMERGENCY CONTACT @break @case('address') PHYSICAL ADDRESS @break @case('guardian_name') PARENT/GUARDIAN @break @case('guardian_phone') PHONE NUMBER @break @case('blood_group') BLOOD GROUP @break @default {{ strtoupper(str_replace('_', ' ', $field)) }} @endswitch</span>
                    <span style="font-size: 7pt; font-weight: bold;">@switch($field) @case('emergency_contact') {{ $student->medicalInfo?->emergency_contact ?? '-' }} @break @case('address') {{ substr($student->address ?? '-', 0, 50) }} @break @case('guardian_name') {{ $student->primaryGuardian?->full_name ?? '-' }} @break @case('guardian_phone') {{ $student->primaryGuardian?->phone ?? '-' }} @break @case('blood_group') {{ $student->medicalInfo?->blood_group ?? '-' }} @break @default - @endswitch</span>
                </div>
            @endforeach
        </div>
        <div class="abs back-rules-box">{!! nl2br(e(substr($idSettings->back_content, 0, 160))) !!}</div>
        @if($idSettings->show_barcode)
            <div class="abs barcode-box">
                <div style="font-family: 'monospace'; font-size: 5pt; color: black; letter-spacing: 0.5mm;">||| || | | || |<div style="font-size: 3pt; text-align: center; letter-spacing: normal;">{{ $student->student_id }}</div></div>
            </div>
        @endif
        <div class="abs back-school-identity">
            <div style="font-size: 7pt; font-weight: bold;">{{ $idSettings->school_name }}</div>
            <div style="font-size: 5pt; opacity: 0.6;">www.renaissance.edu.et</div>
        </div>
        <div class="abs card-footer" style="text-align: center; border-top: 0.15mm solid #e2e8f0;">Non-Transferable. If found, please return to school office.</div>
    </div>
    @endforeach
</body>
</html>
