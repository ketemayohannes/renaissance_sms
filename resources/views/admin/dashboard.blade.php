<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Super Admin Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                
                <!-- Role Management -->
                <a href="{{ route('admin.roles.index') }}" class="block p-6 bg-white border border-gray-200 rounded-lg shadow hover:bg-gray-100">
                    <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900">Role Management</h5>
                    <p class="font-normal text-gray-700">Manage user roles and permissions</p>
                </a>

                <!-- Academic Structure -->
                <div class="bg-indigo-50 p-6 rounded-xl border border-indigo-100">
                    <h3 class="text-lg font-semibold text-indigo-900 mb-4">Academic Structure</h3>
                    <div class="space-y-3">
                        <a href="{{ route('admin.audit-logs.index') }}" class="flex items-center text-indigo-700 hover:text-indigo-900 transition-colors group">
                            <span class="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center mr-3 group-hover:bg-indigo-200">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            </span>
                            System Audit Trail
                        </a>
                        <a href="{{ route('admin.divisions.index') }}" class="block text-blue-700 hover:underline">• Divisions</a>
                        <a href="{{ route('admin.grade-levels.index') }}" class="block text-blue-700 hover:underline">• Grade Levels</a>
                        <a href="{{ route('admin.sections.index') }}" class="block text-blue-700 hover:underline">• Sections</a>
                        <a href="{{ route('admin.subjects.index') }}" class="block text-blue-700 hover:underline">• Subjects</a>
                        <a href="{{ route('admin.subjects.reorder') }}" class="block text-blue-700 hover:underline font-semibold text-sm">+ Subject Ordering</a>
                        <a href="{{ route('admin.subject-assignments.index') }}" class="block text-blue-700 hover:underline">• Subject Assignments</a>
                        <a href="{{ route('admin.academic-years.index') }}" class="block text-blue-700 hover:underline">• Academic Years</a>
                        <a href="{{ route('admin.terms.index') }}" class="block text-blue-700 hover:underline">• Terms</a>
                    </div>
                </div>

                <!-- Student Management -->
                <div class="p-6 bg-green-50 border border-green-200 rounded-lg shadow">
                    <a href="{{ route('admin.students.index') }}" class="block mb-2">
                        <h5 class="text-2xl font-bold tracking-tight text-green-900 hover:underline">Student Management</h5>
                    </a>
                    <p class="font-normal text-green-700 mb-2">Register students, manage enrollments and profiles.</p>
                    <div class="border-t border-green-200 pt-2 mt-2 space-y-1">
                         <a href="{{ route('admin.electives.bulk-assign') }}" class="block text-green-700 hover:underline font-semibold">• Bulk Assign Electives</a>
                         <a href="{{ route('admin.promotions.index') }}" class="block text-emerald-700 hover:underline font-bold">🎓 Promotions & Graduation</a>
                         <a href="{{ route('admin.disciplinary.index') }}" class="block text-red-700 hover:underline font-bold">⚠️ Disciplinary Records</a>
                         <a href="{{ route('admin.id-cards.index') }}" class="block text-blue-700 hover:underline font-bold">🪪 ID Card Generation</a>
                    </div>
                </div>

                <!-- Gradebook -->
                <div class="p-6 bg-purple-50 border border-purple-200 rounded-lg shadow">
                    <h5 class="mb-4 text-2xl font-bold tracking-tight text-purple-900">Gradebook & Assessments</h5>
                    <div class="space-y-2">
                        <a href="{{ route('admin.gradebook.index') }}" class="block text-purple-700 hover:underline">• Enter Grades (Subject-wise)</a>
                        <a href="{{ route('admin.section-grades.index') }}" class="block text-purple-700 hover:underline font-bold">• Section Grade Entry (Master Sheet)</a>
                        <a href="{{ route('admin.assessment-types.index') }}" class="block text-purple-700 hover:underline">• Assessment Types</a>
                        <a href="{{ route('admin.assessment-templates.index') }}" class="block text-purple-700 hover:underline font-semibold">• Assessment Templates (New!)</a>
                        <a href="{{ route('admin.report-cards.settings') }}" class="block text-indigo-700 hover:underline font-bold">+ Report Card Settings</a>
                        <a href="{{ route('admin.academic-reports.settings') }}" class="block text-orange-700 hover:underline font-bold">+ Roster Settings</a>
                        <a href="{{ route('admin.academic-reports.index') }}" class="block text-red-700 hover:underline font-bold">📊 Academic Reports (Result Sheet)</a>
                        <a href="{{ route('admin.attendance.index') }}" class="block text-teal-700 hover:underline font-bold">📅 Student Attendance</a>
                        <a href="{{ route('admin.grade-components.index') }}" class="block text-purple-700 hover:underline text-sm opacity-60">• Grade Components (Old)</a>
                    </div>
                </div>

                <!-- Staff Management -->
                <a href="#" class="block p-6 bg-white border border-gray-200 rounded-lg shadow hover:bg-gray-100 opacity-50 cursor-not-allowed">
                    <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900">Staff Management</h5>
                    <p class="font-normal text-gray-700">Coming soon...</p>
                </a>

                <!-- Finance -->
                <a href="#" class="block p-6 bg-white border border-gray-200 rounded-lg shadow hover:bg-gray-100 opacity-50 cursor-not-allowed">
                    <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900">Finance & Fees</h5>
                    <p class="font-normal text-gray-700">Coming soon...</p>
                </a>

                <!-- Library -->
                <a href="#" class="block p-6 bg-white border border-gray-200 rounded-lg shadow hover:bg-gray-100 opacity-50 cursor-not-allowed">
                    <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900">Library Management</h5>
                    <p class="font-normal text-gray-700">Coming soon...</p>
                </a>

            </div>
        </div>
    </div>
</x-app-layout>
