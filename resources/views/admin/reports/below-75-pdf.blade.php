<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Student Average Below 75</title>
    <style>
        @page {
            margin: 40px;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10pt;
            color: #000000;
            margin: 0;
            padding: 0;
            line-height: 1.4;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }
        .header-table td {
            border: none;
            vertical-align: middle;
            text-align: center;
        }
        .logo-img {
            height: 45px;
            width: auto;
            vertical-align: middle;
            margin-right: 10px;
        }
        .logo-fallback {
            display: inline-block;
            height: 40px;
            width: 40px;
            border-radius: 50%;
            border: 2px solid #000;
            text-align: center;
            line-height: 40px;
            font-weight: bold;
            vertical-align: middle;
            margin-right: 10px;
        }
        .school-name {
            font-size: 20pt;
            font-weight: bold;
            margin: 0;
            display: inline-block;
            vertical-align: middle;
        }
        .report-subtitle {
            font-size: 12pt;
            font-weight: bold;
            margin: 5px 0 15px 0;
            text-align: center;
        }
        .metadata-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0px;
        }
        .metadata-table td {
            padding: 6px 10px;
            font-size: 10pt;
            font-weight: bold;
            border: 1px solid #000000;
            width: 33.33%;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: -1px; /* collapse with metadata-table border */
        }
        .data-table th {
            font-weight: bold;
            font-size: 10pt;
            border: 1px solid #000000;
            padding: 6px 10px;
            text-align: left;
            background-color: #ffffff;
        }
        .data-table td {
            padding: 6px 10px;
            font-size: 9.5pt;
            border: 1px solid #000000;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .page-break { page-break-after: always; }
        .avoid-break { page-break-inside: avoid; }
    </style>
</head>
<body>

    @if(empty($sectionsData))
        <!-- Header for empty state -->
        <table class="header-table">
            <tr>
                <td>
                    @if($logoBase64)
                        <img src="{{ $logoBase64 }}" class="logo-img" alt="School Logo">
                    @else
                        <div class="logo-fallback">RSS</div>
                    @endif
                    <h1 class="school-name">{{ $settings->school_name ?? 'Renaissance School' }}</h1>
                </td>
            </tr>
        </table>
        <div class="report-subtitle">Student average Below 75</div>

        <div style="text-align: center; padding: 40px; color: #64748b; font-style: italic; border: 1px dashed #cbd5e1; border-radius: 12px; margin-top: 30px;">
            No student records found with an average below 75 for the selected timeline and division.
        </div>
    @else
        @foreach($sectionsData as $index => $data)
            @if($index > 0)
                <div class="page-break"></div>
            @endif

            <div class="avoid-break">
                <!-- Header Section -->
                <table class="header-table">
                    <tr>
                        <td>
                            @if($logoBase64)
                                <img src="{{ $logoBase64 }}" class="logo-img" alt="School Logo">
                            @else
                                <div class="logo-fallback">RSS</div>
                            @endif
                            <h1 class="school-name">{{ $settings->school_name ?? 'Renaissance School' }}</h1>
                        </td>
                    </tr>
                </table>
                <div class="report-subtitle">Student average Below 75</div>

                <!-- Metadata Section -->
                <table class="metadata-table">
                    <tr>
                        <td>Grade:{{ $data['grade_level']->name }}</td>
                        <td>Section:{{ $data['section']->name }}</td>
                        <td>
                            @if($term->id === 'yearly')
                                Yearly
                            @elseif($term->type === 'quarter')
                                Quarter:{{ $term->term_number }}
                            @else
                                Term:{{ $term->name }}
                            @endif
                        </td>
                    </tr>
                </table>

                <!-- Data Table -->
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 50%;">Student Name</th>
                            <th style="width: 10%;">Gender</th>
                            <th style="width: 15%;">Average Score (%)</th>
                            <th style="width: 15%;">Signature</th>
                            <th style="width: 10%;">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data['students'] as $student)
                            <tr>
                                <td style="font-weight: normal; text-transform: uppercase;">
                                    {{ $student->first_name }} {{ $student->father_name }} {{ $student->grandfather_name }}
                                </td>
                                <td class="text-center">{{ $student->gender }}</td>
                                <td class="text-center">{{ number_format($student->average_score, 2) }}</td>
                                <td></td>
                                <td></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach
    @endif

</body>
</html>
