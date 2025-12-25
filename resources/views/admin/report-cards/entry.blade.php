<x-admin-layout>
    <x-slot name="header">Report Card Entry - {{ $section->name }}</x-slot>

    <div class="space-y-6">
        <x-breadcrumb :items="[
            ['label' => 'Sections', 'url' => route('admin.sections.index')],
            ['label' => $section->name, 'url' => '#'],
            ['label' => 'Report Card Entry', 'url' => '#']
        ]" />

        <div class="card overflow-hidden">
            <div class="p-6">

            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <p class="text-sm text-gray-500">Academic Year: <span class="font-bold">{{ $academicYear->name }}</span></p>
                            <p class="text-sm text-gray-500">Term: <span class="font-bold">{{ $term->name }}</span></p>
                        </div>
                            <a href="{{ route('admin.section-grades.bulk-print-report-cards', $section->id) }}?academic_year_id={{$academicYear->id}}&term_id={{$term->id}}" target="_blank" class="bg-gray-800 text-white font-bold py-2 px-4 rounded hover:bg-gray-700 mr-2">
                                Print All
                            </a>
                            <a href="{{ route('admin.section-grades.bulk-export-report-cards', $section->id) }}?academic_year_id={{$academicYear->id}}&term_id={{$term_id ?? $term->id}}&academic_year_id={{$academicYear->id}}" class="bg-indigo-600 text-white font-bold py-2 px-4 rounded hover:bg-indigo-700 mr-2 flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                Bulk Background Export ⚡
                            </a>
                            <a href="{{ route('admin.section-grades.index') }}" class="text-blue-600 hover:underline">Back to Grades</a>
                        </div>
                    </div>

                    <form action="{{ route('admin.section-grades.store-report-card-entry', $section) }}" method="POST">
                        @csrf
                        <input type="hidden" name="academic_year_id" value="{{ $academicYear->id }}">
                        <input type="hidden" name="term_id" value="{{ $term->id }}">

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 border">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-10">No</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-48">Student Name</th>
                                        <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-24">Conduct</th>
                                        <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-24">Absence</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Homeroom Teacher Comment</th>
                                        <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-24">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($students as $index => $student)
                                        @php
                                            $record = $records[$student->id] ?? null;
                                        @endphp
                                        <tr>
                                            <td class="px-4 py-2 text-center">{{ $index + 1 }}</td>
                                            <td class="px-4 py-2 font-medium">{{ $student->full_name }}</td>
                                            <td class="px-4 py-2">
                                                <input type="text" name="records[{{ $student->id }}][conduct]" 
                                                       value="{{ $record->conduct_grade ?? '' }}" 
                                                       class="w-full text-center border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
                                                       placeholder="A">
                                            </td>
                                            <td class="px-4 py-2">
                                                <input type="number" name="records[{{ $student->id }}][absent]" 
                                                       value="{{ $record->days_absent ?? 0 }}" 
                                                       class="w-full text-center border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            </td>
                                            <td class="px-4 py-2">
                                                <input type="text" name="records[{{ $student->id }}][comment]" 
                                                       value="{{ $record->homeroom_teacher_comment ?? '' }}" 
                                                       class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                       placeholder="Comment...">
                                            </td>
                                            <td class="px-4 py-2 text-center">
                                                <a href="{{ route('admin.report-cards.pdf', ['student' => $student->id, 'term_id' => $term->id]) }}" target="_blank" class="text-red-600 hover:text-red-800 text-sm font-bold flex items-center justify-center">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                                    PDF
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-6 rounded shadow">
                                Save Details
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    </div>
</x-admin-layout>
