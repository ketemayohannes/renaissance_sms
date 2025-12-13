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
                <div class="p-6 bg-blue-50 border border-blue-200 rounded-lg shadow">
                    <h5 class="mb-4 text-2xl font-bold tracking-tight text-blue-900">Academic Structure</h5>
                    <div class="space-y-2">
                        <a href="{{ route('admin.divisions.index') }}" class="block text-blue-700 hover:underline">• Divisions</a>
                        <a href="{{ route('admin.grade-levels.index') }}" class="block text-blue-700 hover:underline">• Grade Levels</a>
                        <a href="{{ route('admin.sections.index') }}" class="block text-blue-700 hover:underline">• Sections</a>
                        <a href="{{ route('admin.subjects.index') }}" class="block text-blue-700 hover:underline">• Subjects</a>
                        <a href="{{ route('admin.subject-assignments.index') }}" class="block text-blue-700 hover:underline">• Subject Assignments</a>
                        <a href="{{ route('admin.academic-years.index') }}" class="block text-blue-700 hover:underline">• Academic Years</a>
                        <a href="{{ route('admin.terms.index') }}" class="block text-blue-700 hover:underline">• Terms</a>
                    </div>
                </div>

                <!-- Student Management -->
                <a href="{{ route('admin.students.index') }}" class="block p-6 bg-green-50 border border-green-200 rounded-lg shadow hover:bg-green-100">
                    <h5 class="mb-2 text-2xl font-bold tracking-tight text-green-900">Student Management</h5>
                    <p class="font-normal text-green-700 mb-2">Register students, manage enrollments and profiles.</p>
                    <div class="border-t border-green-200 pt-2 mt-2">
                         <object class="block text-green-700 hover:underline font-semibold" onclick="window.location='{{ route('admin.electives.bulk-assign') }}'; return false;">
                             • Bulk Assign Electives
                         </object>
                    </div>
                </a>

                <!-- Gradebook -->
                <div class="p-6 bg-purple-50 border border-purple-200 rounded-lg shadow">
                    <h5 class="mb-4 text-2xl font-bold tracking-tight text-purple-900">Gradebook & Assessments</h5>
                    <div class="space-y-2">
                        <a href="{{ route('admin.gradebook.index') }}" class="block text-purple-700 hover:underline">• Enter Grades (Subject-wise)</a>
                        <a href="{{ route('admin.section-grades.index') }}" class="block text-purple-700 hover:underline font-bold">• Section Grade Entry (Master Sheet)</a>
                        <a href="{{ route('admin.assessment-types.index') }}" class="block text-purple-700 hover:underline">• Assessment Types</a>
                        <a href="{{ route('admin.assessment-templates.index') }}" class="block text-purple-700 hover:underline font-semibold">• Assessment Templates (New!)</a>
                        <a href="{{ route('admin.report-cards.settings') }}" class="block text-indigo-700 hover:underline font-bold">+ Report Card Settings</a>
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
