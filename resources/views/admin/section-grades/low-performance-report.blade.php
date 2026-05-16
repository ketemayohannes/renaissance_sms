<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Academic Performance Alert Report</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 9pt;
            color: #000;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 2px;
        }
        .header-content {
            display: inline-block;
            text-align: center;
        }
        .logo-img {
            height: 45px;
            width: auto;
            vertical-align: middle;
            margin-right: 8px;
        }
        .school-name {
            font-size: 16pt;
            font-weight: bold;
            vertical-align: middle;
            display: inline;
        }
        .subtitle {
            font-size: 10pt;
            margin-top: 2px;
            margin-bottom: 4px;
            text-align: center;
        }
        .metadata {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
        }
        .metadata td {
            border: 1px solid #000;
            padding: 3px 8px;
            font-size: 9pt;
        }
        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 0;
        }
        .report-table th, .report-table td {
            border: 1px solid #000;
            padding: 3px 6px;
            text-align: left;
            font-size: 9pt;
            line-height: 1.2;
        }
        .report-table th {
            font-weight: bold;
            background-color: #fff;
        }
        .text-center {
            text-align: center;
        }
        .all-caps {
            text-transform: uppercase;
        }

        /* Footer Signatures */
        .signature-section {
            position: absolute;
            bottom: 80px;
            left: 0;
            width: 100%;
            margin-top: 25px;
            border-collapse: collapse;
        }
        .signature-section td {
            width: 50%;
            padding: 0 5px;
            vertical-align: top;
        }
        .signature-label {
            font-size: 9pt;
            font-weight: bold;
        }
       
        .sig-right {
            text-align: right;
        }
        .sig-right .signature-line {
            margin-left: auto;
        }

        @page {
            margin: 0.8cm;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            @if($logoBase64)
                <img src="{{ $logoBase64 }}" class="logo-img">
            @endif
            <span class="school-name">Renaissance School</span>
        </div>
        <div class="subtitle">Student average Below 75</div>
    </div>

    <table class="metadata">
        <tr>
            <td width="40%"><strong>Grade:</strong>{{ $section->gradeLevel->name }}</td>
            <td width="30%"><strong>Section:</strong>{{ $section->name }}</td>
            <td width="30%"><strong>Quarter:</strong>{{ $term->term_number ?? '' }}</td>
        </tr>
    </table>

    <table class="report-table">
        <thead>
            <tr>
                <th width="45%">Student Name</th>
                <th width="20%">Average Score (%)</th>
                <th width="20%">Signature</th>
                <th width="15%">Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($lowPerformers as $student)
                <tr>
                    <td class="all-caps">{{ $student->full_name }}</td>
                    <td class="text-center">{{ number_format($stats[$student->id]['average'] ?? 0, 2) }}</td>
                    <td></td>
                    <td></td>
                </tr>
            @endforeach
            @if($lowPerformers->isEmpty())
                <tr>
                    <td colspan="4" class="text-center">No students found below the threshold.</td>
                </tr>
            @endif
        </tbody>
    </table>

    <!-- Signature Footer -->
    <table class="signature-section">
        <tr>
            <td>
                <div class="signature-label">Homeroom Teacher Signature: ___________________</div>
                
            </td>
            <td class="sig-right">
                <div class="signature-label">Principal Signature: ___________________</div>
    
            </td>
        </tr>
    </table>
</body>
</html>
