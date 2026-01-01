<x-admin-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <span class="font-black text-xl tracking-tight text-slate-800">Attendance Report</span>
                <span class="block text-sm font-medium text-slate-400 mt-0.5">
                    {{ $section->gradeLevel->name }}{{ $section->name }} • {{ date('F Y', mktime(0, 0, 0, $month, 1, $year)) }}
                </span>
            </div>
            <div class="flex items-center gap-3 no-print">
                <button onclick="window.print()" class="vibrant-btn-blue flex items-center gap-2 py-2.5 px-6 rounded-xl text-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    PRINT REPORT
                </button>
                <a href="{{ route('admin.attendance.index') }}" class="premium-card bg-white hover:bg-slate-50 text-slate-500 py-2.5 px-6 rounded-xl text-xs font-black flex items-center gap-2 group transition-all">
                    <svg class="w-4 h-4 transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    EXIT
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <x-breadcrumb :items="[
            ['label' => 'Attendance Hub', 'url' => route('admin.attendance.index')],
            ['label' => 'Performance Analysis', 'url' => '#']
        ]" />

        <div class="premium-card p-8">
            <!-- Summary Statistics Logic -->
            @php
                $daysInMonth = date('t', mktime(0, 0, 0, $month, 1, $year));
                $schoolDays = 0;
                $totalPresent = 0;
                $totalAbsent = 0;
                $totalLate = 0;
                $totalExcused = 0;
                
                for ($d = 1; $d <= $daysInMonth; $d++) {
                    $dayOfWeek = date('N', strtotime("$year-$month-$d"));
                    if ($dayOfWeek < 6) $schoolDays++;
                }
                
                foreach ($students as $student) {
                    $studentAttendance = $attendanceData->get($student->id, collect());
                    $totalPresent += $studentAttendance->where('status', 'present')->count();
                    $totalAbsent += $studentAttendance->where('status', 'absent')->count();
                    $totalLate += $studentAttendance->where('status', 'late')->count();
                    $totalExcused += $studentAttendance->where('status', 'excused')->count();
                }
                
                $totalPossible = $schoolDays * $students->count();
                $overallRate = $totalPossible > 0 ? (($totalPresent + $totalLate) / $totalPossible) * 100 : 0;
            @endphp

            <!-- KPI Cards Grid -->
            <div class="grid grid-cols-2 md:grid-cols-6 gap-6 mb-10">
                <div class="glass-card p-6 text-center group bg-white/40">
                    <p class="text-[10px] text-slate-400 font-black uppercase tracking-widest mb-2">Section</p>
                    <p class="text-2xl font-black text-slate-800 tracking-tight">{{ $section->gradeLevel->name }}{{ $section->name }}</p>
                    <div class="mt-2 text-[9px] font-bold text-slate-400 italic">Academic Unit</div>
                </div>
                <div class="glass-card p-6 text-center group bg-white/40">
                    <p class="text-[10px] text-slate-400 font-black uppercase tracking-widest mb-2">Cohort</p>
                    <p class="text-2xl font-black text-slate-800 tracking-tight">{{ $students->count() }}</p>
                    <div class="mt-2 text-[9px] font-bold text-slate-400 italic">Total Entities</div>
                </div>
                <div class="glass-card p-6 text-center group bg-white/40">
                    <p class="text-[10px] text-slate-400 font-black uppercase tracking-widest mb-2">Term Days</p>
                    <p class="text-2xl font-black text-slate-800 tracking-tight">{{ $schoolDays }}</p>
                    <div class="mt-2 text-[9px] font-bold text-slate-400 italic">Working Window</div>
                </div>
                <div class="glass-card p-6 text-center group border-emerald-100 bg-emerald-50/20">
                    <p class="text-[10px] text-emerald-600 font-black uppercase tracking-widest mb-2">Presence</p>
                    <p class="text-2xl font-black text-emerald-600 tracking-tight">{{ $totalPresent }}</p>
                    <div class="mt-2 text-[9px] font-bold text-emerald-400 italic">Marked "Present"</div>
                </div>
                <div class="glass-card p-6 text-center group border-rose-100 bg-rose-50/20">
                    <p class="text-[10px] text-rose-600 font-black uppercase tracking-widest mb-2">Absence</p>
                    <p class="text-2xl font-black text-rose-500 tracking-tight">{{ $totalAbsent }}</p>
                    <div class="mt-2 text-[9px] font-bold text-rose-400 italic">Marked "Absent"</div>
                </div>
                <div class="premium-card p-6 text-center border-none {{ $overallRate >= 90 ? 'bg-emerald-600 shadow-emerald-200' : ($overallRate >= 70 ? 'bg-amber-500 shadow-amber-200' : 'bg-rose-500 shadow-rose-200') }} shadow-xl">
                    <p class="text-[10px] text-white/60 font-black uppercase tracking-widest mb-2">Net Score</p>
                    <p class="text-2xl font-black text-white tracking-tight">{{ number_format($overallRate, 1) }}%</p>
                    <div class="mt-2 text-[9px] font-bold text-white/50 italic capitalize">{{ $overallRate >= 90 ? 'Optimal' : ($overallRate >= 70 ? 'Standard' : 'Critical') }}</div>
                </div>
            </div>

            <!-- Attendance Heatmap Table -->
            <div class="overflow-x-auto rounded-[2rem] border border-slate-100 shadow-xl bg-white/40 mb-10">
                <table class="min-w-full border-collapse">
                    <thead>
                        <tr class="bg-slate-900">
                            <th class="px-4 py-6 text-left text-[10px] font-black text-slate-500 uppercase tracking-widest sticky left-0 bg-slate-900 z-20 w-12">#</th>
                            <th class="px-6 py-6 text-left text-[10px] font-black text-slate-500 uppercase tracking-widest sticky left-12 bg-slate-900 z-20 min-w-[220px]">Candidate Identity</th>
                            @foreach(range(1, $daysInMonth) as $day)
                                @php
                                    $dateString = sprintf('%04d-%02d-%02d', $year, $month, $day);
                                    $dayOfWeek = date('N', strtotime($dateString));
                                    $isWeekend = ($dayOfWeek == 6 || $dayOfWeek == 7);
                                    $dayName = date('D', strtotime($dateString));
                                @endphp
                                <th class="px-1 py-4 text-center border-l border-slate-800/50 {{ $isWeekend ? 'bg-slate-800' : '' }}">
                                    <div class="text-[11px] font-black text-slate-400 leading-none">{{ $day }}</div>
                                    <div class="text-[8px] text-slate-600 font-bold uppercase mt-1">{{ substr($dayName, 0, 1) }}</div>
                                </th>
                            @endforeach
                            <th class="px-4 py-6 text-center text-[10px] font-black text-emerald-400 uppercase bg-emerald-900/40 border-l border-slate-800">P</th>
                            <th class="px-4 py-6 text-center text-[10px] font-black text-rose-400 uppercase bg-rose-900/40 border-l border-slate-800">A</th>
                            <th class="px-4 py-6 text-center text-[10px] font-black text-amber-400 uppercase bg-amber-900/40 border-l border-slate-800">L</th>
                            <th class="px-4 py-6 text-center text-[10px] font-black text-blue-400 uppercase bg-blue-900/40 border-l border-slate-800">E</th>
                            <th class="px-6 py-6 text-center text-[10px] font-black text-slate-400 uppercase bg-slate-800/80 border-l border-slate-800">Rate</th>
                            <th class="px-4 py-6 text-center text-[10px] font-black text-slate-400 uppercase bg-slate-800">Trend</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100/50">
                        @foreach($students as $index => $student)
                            @php
                                $studentAttendance = $attendanceData->get($student->id, collect());
                                $present = $studentAttendance->where('status', 'present')->count();
                                $absent = $studentAttendance->where('status', 'absent')->count();
                                $late = $studentAttendance->where('status', 'late')->count();
                                $excused = $studentAttendance->where('status', 'excused')->count();
                                $totalMarked = $present + $absent + $late + $excused;
                                $attendanceRate = $totalMarked > 0 ? (($present + $late) / $totalMarked) * 100 : 0;
                                
                                $midMonth = ceil($daysInMonth / 2);
                                $firstHalf = $studentAttendance->filter(fn($att) => $att->attendance_date->day <= $midMonth);
                                $secondHalf = $studentAttendance->filter(fn($att) => $att->attendance_date->day > $midMonth);
                                
                                $fRate = $firstHalf->count() > 0 ? ($firstHalf->whereIn('status', ['present', 'late'])->count() / $firstHalf->count()) * 100 : 0;
                                $sRate = $secondHalf->count() > 0 ? ($secondHalf->whereIn('status', ['present', 'late'])->count() / $secondHalf->count()) * 100 : 0;
                                
                                $trend = 'stable';
                                if ($sRate > $fRate + 5) $trend = 'improving';
                                elseif ($sRate < $fRate - 5) $trend = 'declining';
                            @endphp
                            <tr class="group hover:bg-white transition-all duration-300">
                                <td class="px-4 py-3 text-[10px] font-black text-slate-400 italic sticky left-0 bg-white group-hover:bg-slate-50 transition-colors z-10">
                                    {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                                </td>
                                <td class="px-6 py-3 whitespace-nowrap sticky left-12 bg-white group-hover:bg-slate-50 transition-colors z-10 border-r border-slate-100 shadow-[4px_0_8px_-4px_rgba(0,0,0,0.05)]">
                                    <div class="flex flex-col">
                                        <span class="text-xs font-black text-slate-800 tracking-tight leading-none group-hover:text-blue-600 transition-colors uppercase">{{ $student->full_name }}</span>
                                        <span class="text-[8px] text-slate-400 font-bold uppercase mt-1 tracking-wider">{{ $student->student_id }}</span>
                                    </div>
                                </td>
                                @foreach(range(1, $daysInMonth) as $day)
                                    @php
                                        $dateString = sprintf('%04d-%02d-%02d', $year, $month, $day);
                                        $record = $studentAttendance->first(fn($att) => $att->attendance_date->format('Y-m-d') === $dateString);
                                        $char = '-'; $color = 'text-slate-200'; $bg = '';
                                        if ($record) {
                                            switch($record->status) {
                                                case 'present': $char = 'P'; $color = 'text-emerald-500'; $bg = 'bg-emerald-50/30'; break;
                                                case 'absent': $char = 'A'; $color = 'text-rose-500 font-black'; $bg = 'bg-rose-50/30'; break;
                                                case 'late': $char = 'L'; $color = 'text-amber-500'; $bg = 'bg-amber-50/30'; break;
                                                case 'excused': $char = 'E'; $color = 'text-blue-500'; $bg = 'bg-blue-50/30'; break;
                                            }
                                        }
                                        $dayOfWeek = date('N', strtotime($dateString));
                                        $isWeekend = ($dayOfWeek == 6 || $dayOfWeek == 7);
                                    @endphp
                                    <td class="text-center text-[10px] font-black border-l border-slate-50/50 {{ $isWeekend ? 'bg-slate-50' : $bg }} {{ $color }} py-3">
                                        {{ $isWeekend ? '•' : $char }}
                                    </td>
                                @endforeach
                                <td class="text-center text-[11px] font-bold bg-emerald-50/30 text-emerald-700 border-l border-slate-100">{{ $present }}</td>
                                <td class="text-center text-[11px] font-bold bg-rose-50/30 text-rose-700 border-l border-slate-100">{{ $absent }}</td>
                                <td class="text-center text-[11px] font-bold bg-amber-50/30 text-amber-700 border-l border-slate-100">{{ $late }}</td>
                                <td class="text-center text-[11px] font-bold bg-blue-50/30 text-blue-700 border-l border-slate-100">{{ $excused }}</td>
                                <td class="text-center text-[11px] font-black px-4 border-l border-slate-100
                                    {{ $attendanceRate >= 90 ? 'text-emerald-600' : ($attendanceRate >= 70 ? 'text-amber-600' : 'text-rose-600') }}">
                                    {{ number_format($attendanceRate, 0) }}%
                                </td>
                                <td class="text-center px-4 border-l border-slate-100">
                                    @if($trend == 'improving')
                                        <div class="w-6 h-6 bg-emerald-100 rounded-full flex items-center justify-center mx-auto text-emerald-600">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                                        </div>
                                    @elseif($trend == 'declining')
                                        <div class="w-6 h-6 bg-rose-100 rounded-full flex items-center justify-center mx-auto text-rose-600">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 17h8m0 0v-8m0 8l-8-8-4 4-6-6"/></svg>
                                        </div>
                                    @else
                                        <div class="w-2 h-2 bg-slate-200 rounded-full mx-auto"></div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Legend Section -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-10">
                <div class="flex flex-wrap items-center gap-6 p-6 bg-slate-50/50 rounded-[2rem] border border-slate-100">
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-2">Legacy Matrix:</span>
                    <div class="flex items-center gap-4">
                        <span class="flex items-center gap-2"><span class="w-6 h-6 rounded-lg bg-emerald-500 flex items-center justify-center text-[10px] text-white font-black">P</span> <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Present</span></span>
                        <span class="flex items-center gap-2"><span class="w-6 h-6 rounded-lg bg-rose-500 flex items-center justify-center text-[10px] text-white font-black">A</span> <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Absent</span></span>
                        <span class="flex items-center gap-2"><span class="w-6 h-6 rounded-lg bg-amber-500 flex items-center justify-center text-[10px] text-white font-black">L</span> <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Late</span></span>
                        <span class="flex items-center gap-2"><span class="w-6 h-6 rounded-lg bg-blue-500 flex items-center justify-center text-[10px] text-white font-black">E</span> <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Excused</span></span>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-6 p-6 bg-slate-900 rounded-[2rem] border border-slate-800 shadow-2xl">
                    <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-2">Intelligence Feed:</span>
                    <div class="flex items-center gap-6">
                        <span class="flex items-center gap-3">
                            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Improving Curve</span>
                        </span>
                        <span class="flex items-center gap-3">
                            <svg class="w-4 h-4 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 17h8m0 0v-8m0 8l-8-8-4 4-6-6"/></svg>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Declining Curve</span>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Custom Print Overrides -->
        <style>
            @media print {
                .no-print { display: none !important; }
                body { background: white !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
                .premium-card { border: none !important; box-shadow: none !important; padding: 0 !important; }
                table { width: 100% !important; border-collapse: collapse !important; }
                th, td { border: 1px solid #e2e8f0 !important; padding: 4px 2px !important; font-size: 8px !important; }
                .sticky { position: static !important; }
                .glass-card { background: white !important; border: 1px solid #e2e8f0 !important; }
                .bg-slate-900 th { color: #475569 !important; background: #f8fafc !important; }
            }
        </style>
    </div>
</x-admin-layout>
