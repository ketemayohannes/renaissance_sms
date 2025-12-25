<x-admin-layout>
    <x-slot name="header">Process Student Promotions</x-slot>

    <div class="space-y-6">
        <div class="flex justify-between items-center bg-white p-4 rounded-lg shadow-sm border border-slate-200">
            <div>
                <h2 class="text-lg font-bold text-slate-800">Process Student Promotions</h2>
                <p class="text-sm text-slate-500">Promote students to the next grade level based on performance rules.</p>
            </div>
            <a href="{{ route('admin.promotions.index') }}" class="btn-secondary">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Rules
            </a>
        </div>

        <x-breadcrumb :items="[
            ['label' => 'Promotions', 'url' => route('admin.promotions.index')],
            ['label' => 'Process', 'url' => '#']
        ]" />

        <div class="card overflow-hidden">
            <div class="p-6">
                    @if(!$nextAcademicYear)
                        <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-6">
                            <strong>Warning:</strong> No upcoming academic year found! Please create the next academic year before processing promotions hideously.
                        </div>
                    @endif

                    <h3 class="text-lg font-medium text-gray-900 mb-4">Select Section to Process</h3>
                    <form action="{{ route('admin.promotions.preview') }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4 border p-4 rounded-lg bg-gray-50">
                        @csrf
                        <div>
                            <label for="section_id" class="block text-sm font-medium text-gray-700">Section</label>
                            <select name="section_id" id="section_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                                <option value="">Select Section</option>
                                @foreach($gradeLevels as $gradeLevel)
                                    <optgroup label="{{ $gradeLevel->name }}">
                                        @foreach($gradeLevel->sections as $section)
                                            <option value="{{ $section->id }}">{{ $section->name }}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-2 flex items-end">
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded transition" {{ !$nextAcademicYear ? 'disabled' : '' }}>
                                Preview Promotions
                            </button>
                        </div>
                    </form>

                    <div class="mt-8 p-4 bg-blue-50 rounded-lg border border-blue-200">
                        <h4 class="font-bold text-blue-800 mb-2">Current Academic Year</h4>
                        <p class="text-blue-700">{{ $academicYear->name }}</p>
                        <h4 class="font-bold text-blue-800 mt-4 mb-2">Next Academic Year</h4>
                        <p class="text-blue-700">{{ $nextAcademicYear?->name ?? 'Not Created' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
