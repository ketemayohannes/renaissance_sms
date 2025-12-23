<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Mark Attendance') }}: {{ $section->gradeLevel->name }}{{ $section->name }} ({{ $date }})
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
                ['label' => 'Mark Attendance', 'url' => '#']
            ]" />
            
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form action="{{ route('admin.attendance.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="section_id" value="{{ $section->id }}">
                        <input type="hidden" name="date" value="{{ $date }}">

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 border">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-16">No</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student Name</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-white uppercase tracking-wider bg-green-600 w-20">Present</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-white uppercase tracking-wider bg-red-600 w-20">Absent</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-white uppercase tracking-wider bg-yellow-500 w-20">Late</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-white uppercase tracking-wider bg-blue-600 w-20">Excused</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Remarks</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($students as $index => $student)
                                        @php
                                            $current = $existingAttendance[$student->id] ?? null;
                                            $currentStatus = $current ? $current->status : 'present';
                                        @endphp
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                                {{ $index + 1 }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $student->full_name }}
                                                <span class="text-xs text-gray-400 ml-1">({{ $student->student_id }})</span>
                                            </td>
                                            <td class="px-4 py-3 text-center bg-green-50">
                                                <input type="radio" 
                                                       name="attendance[{{ $student->id }}]" 
                                                       value="present" 
                                                       {{ $currentStatus == 'present' ? 'checked' : '' }}
                                                       class="w-5 h-5 text-green-600 border-gray-300 focus:ring-green-500">
                                            </td>
                                            <td class="px-4 py-3 text-center bg-red-50">
                                                <input type="radio" 
                                                       name="attendance[{{ $student->id }}]" 
                                                       value="absent" 
                                                       {{ $currentStatus == 'absent' ? 'checked' : '' }}
                                                       class="w-5 h-5 text-red-600 border-gray-300 focus:ring-red-500">
                                            </td>
                                            <td class="px-4 py-3 text-center bg-yellow-50">
                                                <input type="radio" 
                                                       name="attendance[{{ $student->id }}]" 
                                                       value="late" 
                                                       {{ $currentStatus == 'late' ? 'checked' : '' }}
                                                       class="w-5 h-5 text-yellow-600 border-gray-300 focus:ring-yellow-500">
                                            </td>
                                            <td class="px-4 py-3 text-center bg-blue-50">
                                                <input type="radio" 
                                                       name="attendance[{{ $student->id }}]" 
                                                       value="excused" 
                                                       {{ $currentStatus == 'excused' ? 'checked' : '' }}
                                                       class="w-5 h-5 text-blue-600 border-gray-300 focus:ring-blue-500">
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                                <input type="text" 
                                                       name="remarks[{{ $student->id }}]" 
                                                       value="{{ $current ? $current->remarks : '' }}"
                                                       placeholder="Optional..."
                                                       class="border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm w-full">
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-8 flex justify-between items-center">
                            <div class="text-sm text-gray-500">
                                Total Students: <strong>{{ $students->count() }}</strong>
                            </div>
                            <div class="flex gap-4">
                                <a href="{{ route('admin.attendance.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-6 rounded transition">
                                    Cancel
                                </a>
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-10 rounded shadow-lg transition">
                                    Save Attendance
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
