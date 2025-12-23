<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Student Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Welcome Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <h3 class="text-2xl font-bold mb-2">Welcome back, {{ $student->first_name }}!</h3>
                    <p class="text-gray-600">
                        @if($student->currentEnrollment)
                            You are currently enrolled in <span class="font-semibold">{{ $student->currentEnrollment->section->gradeLevel->name }} - {{ $student->currentEnrollment->section->name }}</span>
                            for the <span class="font-semibold">{{ $student->currentEnrollment->academicYear->name }}</span> academic year.
                        @else
                            You are not currently enrolled in any active section.
                        @endif
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Quick Actions -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h4 class="font-bold text-lg mb-4 text-gray-800">Quick Actions</h4>
                        <div class="grid grid-cols-2 gap-4">
                            <a href="{{ route('student.grades.index') }}" class="block p-4 bg-indigo-50 rounded-lg border border-indigo-100 hover:bg-indigo-100 transition">
                                <span class="text-2xl mb-2 block">📊</span>
                                <span class="font-semibold text-indigo-700">View Grades</span>
                            </a>
                            <div class="block p-4 bg-gray-50 rounded-lg border border-gray-100 opacity-60 cursor-not-allowed" title="Coming Soon">
                                <span class="text-2xl mb-2 block">📅</span>
                                <span class="font-semibold text-gray-400">My Schedule</span>
                            </div>
                            <div class="block p-4 bg-gray-50 rounded-lg border border-gray-100 opacity-60 cursor-not-allowed" title="Coming Soon">
                                <span class="text-2xl mb-2 block">📝</span>
                                <span class="font-semibold text-gray-400">Homework</span>
                            </div>
                            <a href="{{ route('student.profile') }}" class="block p-4 bg-purple-50 rounded-lg border border-purple-100 hover:bg-purple-100 transition">
                                <span class="text-2xl mb-2 block">👤</span>
                                <span class="font-semibold text-purple-700">Profile</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity / Stats -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h4 class="font-bold text-lg mb-4 text-gray-800">Attendance Overview</h4>
                        <div class="flex items-center justify-between bg-gray-50 p-4 rounded-lg">
                            <div class="text-center">
                                <span class="block text-2xl font-bold text-green-600">--</span>
                                <span class="text-xs text-gray-500 uppercase">Present</span>
                            </div>
                            <div class="text-center">
                                <span class="block text-2xl font-bold text-red-600">--</span>
                                <span class="text-xs text-gray-500 uppercase">Absent</span>
                            </div>
                            <div class="text-center">
                                <span class="block text-2xl font-bold text-yellow-600">--</span>
                                <span class="text-xs text-gray-500 uppercase">Late</span>
                            </div>
                            <div class="text-center border-l pl-4">
                                <span class="block text-2xl font-bold text-gray-800">--%</span>
                                <span class="text-xs text-gray-500 uppercase">Rate</span>
                            </div>
                        </div>
                        <p class="text-sm text-gray-500 mt-4 text-center">Detailed attendance data coming soon.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
