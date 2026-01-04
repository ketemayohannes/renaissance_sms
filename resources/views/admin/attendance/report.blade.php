<x-admin-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Attendance Report</h2>
                <p class="text-sm font-medium text-slate-500 mt-1">
                    {{ $section->gradeLevel->name }}{{ $section->name }} • {{ date('F Y', mktime(0, 0, 0, $month, 1, $year)) }}
                </p>
            </div>
            <div class="flex items-center gap-3 no-print">
                <button onclick="window.print()" class="inline-flex items-center px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition-all shadow-lg shadow-indigo-100 gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    PRINT REPORT
                </button>
                <a href="{{ route('admin.attendance.index') }}" class="inline-flex items-center px-6 py-2.5 bg-white border border-slate-200 text-slate-700 text-xs font-bold rounded-xl hover:bg-slate-50 transition-all gap-2 group">
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

        <!-- Summary Statistics -->
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

        <!-- KPI Cards Grid (Matched with Student List) -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            <div class="bg-white/60 backdrop-blur-md p-5 rounded-3xl border border-white shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                </div>
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Section</p>
                    <p class="text-xl font-bold text-slate-900">{{ $section->gradeLevel->name }}{{ $section->name }}</p>
                </div>
            </div>

            <div class="bg-white/60 backdrop-blur-md p-5 rounded-3xl border border-white shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Cohort</p>
                    <p class="text-xl font-bold text-slate-900">{{ $students->count() }}</p>
                </div>
            </div>

            <div class="bg-white/60 backdrop-blur-md p-5 rounded-3xl border border-white shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-amber-50 flex items-center justify-center text-amber-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </div>
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Term Days</p>
                    <p class="text-xl font-bold text-slate-900">{{ $schoolDays }}</p>
                </div>
            </div>

            <div class="bg-white/60 backdrop-blur-md p-5 rounded-3xl border border-white shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Present</p>
                    <p class="text-xl font-bold text-slate-900">{{ $totalPresent }}</p>
                </div>
            </div>

            <div class="bg-white/60 backdrop-blur-md p-5 rounded-3xl border border-white shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-rose-50 flex items-center justify-center text-rose-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Absent</p>
                    <p class="text-xl font-bold text-slate-900">{{ $totalAbsent }}</p>
                </div>
            </div>

            <div class="p-5 rounded-3xl border border-none shadow-xl flex items-center gap-4 
                {{ $overallRate >= 90 ? 'bg-emerald-600 shadow-emerald-200' : ($overallRate >= 70 ? 'bg-amber-500 shadow-amber-200' : 'bg-rose-500 shadow-rose-200') }}">
                <div class="w-12 h-12 rounded-2xl bg-white/10 flex items-center justify-center text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                </div>
                <div>
                    <p class="text-[10px] font-black text-white/60 uppercase tracking-widest">Net Rate</p>
                    <p class="text-xl font-bold text-white leading-tight">{{ number_format($overallRate, 1) }}%</p>
                </div>
            </div>
        </div>

        <!-- Attendance Heatmap Table -->
        <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-xl shadow-slate-200/50 overflow-hidden mt-8">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50 border-b border-slate-100">
                            <th class="p-4 text-xs font-bold text-slate-500 uppercase tracking-wider sticky left-0 bg-slate-50/50 z-20 w-12 text-center">#</th>
                            <th class="p-4 text-xs font-bold text-slate-500 uppercase tracking-wider sticky left-12 bg-slate-50/50 z-20 min-w-[220px]">Candidate Identity</th>
                            @foreach(range(1, $daysInMonth) as $day)
                                @php
                                    $dateString = sprintf('%04d-%02d-%02d', $year, $month, $day);
                                    $dayOfWeek = date('N', strtotime($dateString));
                                    $isWeekend = ($dayOfWeek == 6 || $dayOfWeek == 7);
                                    $dayName = date('D', strtotime($dateString));
                                @endphp
                                <th class="p-1 py-4 text-center border-l border-slate-100 {{ $isWeekend ? 'bg-slate-100/50' : '' }}">
                                    <div class="text-[10px] font-black text-slate-500 leading-none">{{ $day }}</div>
                                    <div class="text-[8px] text-slate-400 font-bold uppercase mt-1">{{ substr($dayName, 0, 1) }}</div>
                                </th>
                            @endforeach
                            <th class="p-4 text-center text-[10px] font-black text-emerald-600 uppercase bg-emerald-50/30 border-l border-slate-100 w-10">P</th>
                            <th class="p-4 text-center text-[10px] font-black text-rose-600 uppercase bg-rose-50/30 border-l border-slate-100 w-10">A</th>
                            <th class="p-4 text-center text-[10px] font-black text-amber-600 uppercase bg-amber-50/30 border-l border-slate-100 w-10">L</th>
                            <th class="p-4 text-center text-[10px] font-black text-blue-600 uppercase bg-blue-50/30 border-l border-slate-100 w-10">E</th>
                            <th class="p-4 text-center text-xs font-bold text-slate-500 uppercase tracking-wider border-l border-slate-100">Rate</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($students as $index => $student)
                            @php
                                $studentAttendance = $attendanceData->get($student->id, collect());
                                $present = $studentAttendance->where('status', 'present')->count();
                                $absent = $studentAttendance->where('status', 'absent')->count();
                                $late = $studentAttendance->where('status', 'late')->count();
                                $excused = $studentAttendance->where('status', 'excused')->count();
                                $totalMarked = $present + $absent + $late + $excused;
                                $attendanceRate = $totalMarked > 0 ? (($present + $late) / $totalMarked) * 100 : 0;
                            @endphp
                            <tr class="group hover:bg-slate-50/50 transition-all duration-200">
                                <td class="p-3 text-sm font-medium text-slate-400 text-center sticky left-0 bg-white group-hover:bg-slate-50 transition-colors z-10">
                                    {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                                </td>
                                <td class="p-4 whitespace-nowrap sticky left-12 bg-white group-hover:bg-slate-50 transition-colors z-10 border-r border-slate-100 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.05)]">
                                    <div class="flex items-center gap-3">
                                        <div class="h-9 w-9 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-[11px] ring-2 ring-white shadow-sm">
                                            {{ substr($student->first_name, 0, 1) }}{{ substr($student->father_name, 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="text-xs font-bold text-slate-900 group-hover:text-indigo-600 transition-colors">{{ $student->full_name }}</div>
                                            <div class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">{{ $student->student_id }}</div>
                                        </div>
                                    </div>
                                </td>
                                @foreach(range(1, $daysInMonth) as $day)
                                    @php
                                        $dateString = sprintf('%04d-%02d-%02d', $year, $month, $day);
                                        $record = $studentAttendance->first(fn($att) => $att->attendance_date->format('Y-m-d') === $dateString);
                                        $char = '-'; $color = 'text-slate-200'; $bg = ''; $weight = 'font-normal';
                                        if ($record) {
                                            $weight = 'font-black';
                                            switch($record->status) {
                                                case 'present': $char = 'P'; $color = 'text-emerald-500'; $bg = 'bg-emerald-50/20'; break;
                                                case 'absent': $char = 'A'; $color = 'text-rose-500'; $bg = 'bg-rose-50/20'; break;
                                                case 'late': $char = 'L'; $color = 'text-amber-500'; $bg = 'bg-amber-50/20'; break;
                                                case 'excused': $char = 'E'; $color = 'text-blue-500'; $bg = 'bg-blue-50/20'; break;
                                            }
                                        }
                                        $dayOfWeek = date('N', strtotime($dateString));
                                        $isWeekend = ($dayOfWeek == 6 || $dayOfWeek == 7);
                                    @endphp
                                    <td class="text-center text-[10px] {{ $weight }} border-l border-slate-50 {{ $isWeekend ? 'bg-slate-50' : $bg }} {{ $color }} p-2">
                                        {{ $isWeekend ? '•' : $char }}
                                    </td>
                                @endforeach
                                <td class="text-center text-xs font-bold bg-emerald-50/20 text-emerald-700 border-l border-slate-100">{{ $present }}</td>
                                <td class="text-center text-xs font-bold bg-rose-50/20 text-rose-700 border-l border-slate-100">{{ $absent }}</td>
                                <td class="text-center text-xs font-bold bg-amber-50/20 text-amber-700 border-l border-slate-100">{{ $late }}</td>
                                <td class="text-center text-xs font-bold bg-blue-50/20 text-blue-700 border-l border-slate-100">{{ $excused }}</td>
                                <td class="text-center text-xs font-black px-4 border-l border-slate-100
                                    {{ $attendanceRate >= 90 ? 'text-emerald-600' : ($attendanceRate >= 70 ? 'text-amber-600' : 'text-rose-600') }}">
                                    {{ number_format($attendanceRate, 0) }}%
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Legend -->
        <div class="flex flex-wrap items-center gap-6 p-6 bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-xl shadow-slate-200/50 mt-8 no-print">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-widest ml-2">Legacy Scale</span>
            <div class="flex flex-wrap gap-4">
                <span class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500 flex items-center justify-center text-[11px] text-white font-black shadow-sm shadow-emerald-100">P</span>
                    <span class="text-xs font-bold text-slate-500">Present</span>
                </span>
                <span class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-rose-500 flex items-center justify-center text-[11px] text-white font-black shadow-sm shadow-rose-100">A</span>
                    <span class="text-xs font-bold text-slate-500">Absent</span>
                </span>
                <span class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500 flex items-center justify-center text-[11px] text-white font-black shadow-sm shadow-amber-100">L</span>
                    <span class="text-xs font-bold text-slate-500">Late</span>
                </span>
                <span class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-blue-500 flex items-center justify-center text-[11px] text-white font-black shadow-sm shadow-blue-100">E</span>
                    <span class="text-xs font-bold text-slate-500">Excused</span>
                </span>
            </div>
        </div>

        <style>
            @media print {
                .no-print { display: none !important; }
                body { background: white !important; -webkit-print-color-adjust: exact; }
                div.bg-white\/80 { background: white !important; border: 1px solid #eee !important; box-shadow: none !important; border-radius: 0 !important; }
                .sticky { position: static !important; }
                th, td { border: 1px solid #eee !important; padding: 2px !important; }
            }
        </style>
    </div>
</x-admin-layout>
