<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Student ID Card - {{ $student->full_name }}</title>
    <style>
        @page {
            margin: 5mm;
            size: 86mm 54mm landscape;
        }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 8pt;
            margin: 0;
            padding: 0;
        }
        .card {
            width: 76mm;
            height: 44mm;
            background-color: #1e3a8a;
            padding: 3mm;
            color: white;
            border: 2px solid #1e3a8a;
        }
        .card-inner {
            background-color: #3b82f6;
            border: 1px solid #60a5fa;
            padding: 2mm;
            height: 38mm;
        }
        .school-name {
            font-size: 10pt;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
            margin-bottom: 2mm;
            color: #ffffff;
        }
        .content-table {
            width: 100%;
        }
        .photo-cell {
            width: 22mm;
            vertical-align: top;
        }
        .photo {
            width: 20mm;
            height: 25mm;
            background-color: #93c5fd;
            border: 1px solid #ffffff;
            text-align: center;
        }
        .photo img {
            width: 20mm;
            height: 25mm;
        }
        .info-cell {
            vertical-align: top;
            padding-left: 2mm;
            color: #ffffff;
        }
        .student-name {
            font-size: 9pt;
            font-weight: bold;
            margin-bottom: 2mm;
            text-transform: uppercase;
            color: #ffffff;
        }
        .info-row {
            margin-bottom: 1mm;
            font-size: 7pt;
            color: #e0f2fe;
        }
        .label {
            font-weight: bold;
            color: #ffffff;
        }
        .footer {
            font-size: 6pt;
            text-align: center;
            margin-top: 2mm;
            color: #bfdbfe;
        }
        .year-badge {
            text-align: right;
            font-size: 7pt;
            font-weight: bold;
            color: #fef08a;
            margin-bottom: 1mm;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="card-inner">
            <div class="year-badge">{{ $academicYear->name ?? date('Y') }}</div>
            <div class="school-name">{{ $settings->school_name ?? 'Renaissance School' }}</div>
            
            <table class="content-table">
                <tr>
                    <td class="photo-cell">
                        <div class="photo">
                            @if($student->photo)
                                <img src="{{ public_path('storage/' . $student->photo) }}" alt="Photo">
                            @else
                                <div style="padding-top: 8mm; color: #1e3a8a; font-size: 12pt;">No Photo</div>
                            @endif
                        </div>
                    </td>
                    <td class="info-cell">
                        <div class="student-name">{{ $student->full_name }}</div>
                        <div class="info-row"><span class="label">ID:</span> {{ $student->student_id }}</div>
                        <div class="info-row"><span class="label">Grade:</span> {{ $student->currentEnrollment?->section?->gradeLevel?->name ?? '-' }} {{ $student->currentEnrollment?->section?->name ?? '' }}</div>
                        <div class="info-row"><span class="label">Gender:</span> {{ ucfirst($student->gender ?? '-') }}</div>
                        <div class="info-row"><span class="label">DOB:</span> {{ $student->date_of_birth?->format('d/m/Y') ?? '-' }}</div>
                    </td>
                </tr>
            </table>
            <div class="footer">
                {{ $settings->telephone ?? '' }} {{ $settings->website ? '| ' . $settings->website : '' }}
            </div>
        </div>
    </div>
</body>
</html>
