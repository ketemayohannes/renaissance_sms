<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Attendance Report') }}: {{ $section->gradeLevel->name }}{{ $section->name }} ({{ date('F Y', mktime(0, 0, 0, $month, 1, $year)) }})
            </h2>
            <a href="{{ route('admin.attendance.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded transition">
                Back
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Breadcrumb -->
            <x-breadcrumb :items="[
                ['label' => 'Attendance', 'url' => route('admin.attendance.index')],
                ['label' => 'Report', 'url' => '#']
            ]" />
            
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <div class="mb-6 grid grid-cols-1 md:grid-cols-4 gap-4 bg-gray-50 p-4 rounded-lg">
                        <div class="text-sm">
                            <span class="text-gray-500">Section:</span>
                            <span class="font-bold block">{{ $section->gradeLevel->name }}{{ $section->name }}</span>
                        </div>
                        <div class="text-sm">
                            <span class="text-gray-500">Month:</span>
                            <span class="font-bold block">{{ date('F', mktime(0, 0, 0, $month, 1)) }}</span>
                        </div>
                        <div class="text-sm">
                            <span class="text-gray-500">Year:</span>
                            <span class="font-bold block">{{ $year }}</span>
                        </div>
                        <div class="text-sm">
                            <span class="text-gray-500">Total Students:</span>
                            <span class="font-bold block">{{ $students->count() }}</span>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        @php
                            $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
                        @endphp
                        <table class="min-w-full border-collapse border border-gray-200">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="border border-gray-200 px-2 py-1 sticky left-0 bg-gray-100 z-10">Student</th>
                                    @foreach(range(1, $daysInMonth) as $day)
                                        <th class="border border-gray-200 px-1 py-1 text-xs w-6">{{ $day }}</th>
                                    @endforeach
                                    <th class="border border-gray-200 px-2 py-1 text-xs bg-green-50">P</th>
                                    <th class="border border-gray-200 px-2 py-1 text-xs bg-red-50">A</th>
                                    <th class="border border-gray-200 px-2 py-1 text-xs bg-yellow-50">L</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($students as $student)
                                    @php
                                        $studentAttendance = $attendanceData->get($student->id, collect());
                                        $present = $studentAttendance->where('status', 'present')->count();
                                        $absent = $studentAttendance->where('status', 'absent')->count();
                                        $late = $studentAttendance->where('status', 'late')->count();
                                    @endphp
                                    <tr class="hover:bg-gray-50">
                                        <td class="border border-gray-200 px-2 py-1 text-sm whitespace-nowrap sticky left-0 bg-white group-hover:bg-gray-50">
                                            {{ $student->full_name }}
                                        </td>
                                        @foreach(range(1, $daysInMonth) as $day)
                                            @php
                                                $dateString = sprintf('%04d-%02d-%02d', $year, $month, $day);
                                                $record = $studentAttendance->first(function($att) use ($dateString) {
                                                    return $att->attendance_date->format('Y-m-d') === $dateString;
                                                });
                                                $statusChar = '';
                                                $colorClass = '';
                                                if ($record) {
                                                    switch($record->status) {
                                                        case 'present': $statusChar = 'P'; $colorClass = 'text-green-600'; break;
                                                        case 'absent': $statusChar = 'A'; $colorClass = 'text-red-600 font-bold'; break;
                                                        case 'late': $statusChar = 'L'; $colorClass = 'text-yellow-600'; break;
                                                        case 'excused': $statusChar = 'E'; $colorClass = 'text-blue-600'; break;
                                                    }
                                                }
                                                // Check if it's a weekend hideously
                                                $dayOfWeek = date('N', strtotime($dateString));
                                                $isWeekend = ($dayOfWeek == 6 || $dayOfWeek == 7);
                                            @endphp
                                            <td class="border border-gray-200 text-center text-[10px] {{ $isWeekend ? 'bg-gray-100' : '' }} {{ $colorClass }}">
                                                {{ $statusChar }}
                                            </td>
                                        @endforeach
                                        <td class="border border-gray-200 text-center text-xs bg-green-50 font-bold">{{ $present }}</td>
                                        <td class="border border-gray-200 text-center text-xs bg-red-50 font-bold">{{ $absent }}</td>
                                        <td class="border border-gray-200 text-center text-xs bg-yellow-50 font-bold">{{ $late }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6 text-xs text-gray-500 italic">
                        Legend: P = Present, A = Absent, L = Late, E = Excused. Shaded columns represent weekends.
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
