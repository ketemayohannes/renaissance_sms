<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Subject Assignments') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-4">
                        <p class="text-sm text-gray-600">Assign subjects to grade levels for the active academic year.</p>
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
    </div>
</x-app-layout>
