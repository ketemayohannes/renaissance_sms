<x-admin-layout>
    <x-slot name="header">Subject Assignments</x-slot>

    <div class="space-y-6">
            <!-- Breadcrumb -->
            <x-breadcrumb :items="[
                ['label' => 'Subject Assignments', 'url' => '#']
            ]" />
            
        <div class="card overflow-hidden">
            <div class="p-6">
                    <div class="mb-4 flex justify-between items-center">
                        <p class="text-sm text-gray-600">Assign subjects to grade levels for the active academic year.</p>
                        <a href="{{ route('admin.subject-assignments.bulk-assign') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded shadow-sm text-sm">
                            Bulk Assign Subjects
                        </a>
                    </div>

                    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                        <table class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3">Grade Level</th>
                                    <th scope="col" class="px-6 py-3">Division</th>
                                    <th scope="col" class="px-6 py-3">Assigned Subjects</th>
                                    <th scope="col" class="px-6 py-3">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($gradeLevels as $grade)
                                    <tr class="bg-white border-b hover:bg-gray-50">
                                        <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                            {{ $grade->name }}
                                        </td>
                                        <td class="px-6 py-4">
                                            {{ $grade->division->name }}
                                        </td>
                                        <td class="px-6 py-4">
                                            @php
                                                $count = DB::table('grade_level_subjects')
                                                    ->where('grade_level_id', $grade->id)
                                                    ->where('academic_year_id', \App\Models\AcademicYear::where('is_active', true)->value('id'))
                                                    ->count();
                                            @endphp
                                            <span class="bg-blue-100 text-blue-800 text-xs font-medium mr-2 px-2.5 py-0.5 rounded">{{ $count }} Subjects</span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <a href="{{ route('admin.subject-assignments.edit', $grade) }}" class="font-medium text-blue-600 hover:underline">Manage Subjects</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
    </div>
</x-admin-layout>
