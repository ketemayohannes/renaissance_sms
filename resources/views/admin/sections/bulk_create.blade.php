<x-admin-layout>
    <x-slot name="header">Bulk Create Sections</x-slot>

    <div class="space-y-6">
        <!-- Breadcrumb -->
        <x-breadcrumb :items="[
            ['label' => 'Sections', 'url' => route('admin.sections.index')],
            ['label' => 'Bulk Create', 'url' => '#']
        ]" />

        <div class="card overflow-hidden">
            <div class="p-6">
                    <form method="POST" action="{{ route('admin.sections.bulk-store') }}">
                        @csrf

                        <!-- Academic Year -->
                        <div class="mb-4">
                            <x-input-label for="academic_year_id" :value="__('Academic Year')" />
                            <select id="academic_year_id" name="academic_year_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                                @foreach($academicYears as $year)
                                    <option value="{{ $year->id }}" {{ $year->is_active ? 'selected' : '' }}>{{ $year->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('academic_year_id')" class="mt-2" />
                        </div>

                        <!-- Target Grade Levels -->
                        <div class="mb-4">
                            <x-input-label :value="__('Target Grade Levels')" />
                            <div class="mt-2 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach($gradeLevels->groupBy('division.name') as $division => $grades)
                                    <div class="border rounded p-3">
                                        <h3 class="font-bold text-gray-700 mb-2">{{ $division }}</h3>
                                        @foreach($grades as $grade)
                                            <div class="flex items-center mb-1">
                                                <input id="grade_{{ $grade->id }}" name="grade_level_ids[]" type="checkbox" value="{{ $grade->id }}" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                                <label for="grade_{{ $grade->id }}" class="ml-2 text-sm text-gray-600">{{ $grade->name }}</label>
                                            </div>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                            <x-input-error :messages="$errors->get('grade_level_ids')" class="mt-2" />
                            <p class="text-sm text-gray-500 mt-1">Select all grade levels you want to create these sections for.</p>
                        </div>

                        <!-- Section Names -->
                        <div class="mb-4">
                            <x-input-label for="section_names" :value="__('Section Names / Codes')" />
                            <x-text-input id="section_names" class="block mt-1 w-full" type="text" name="section_names" :value="old('section_names')" required placeholder="A, B, C, D" />
                            <p class="text-sm text-gray-500 mt-1">Enter section codes separated by commas (e.g., "A, B, C" or "Red, Blue, Green").</p>
                            <x-input-error :messages="$errors->get('section_names')" class="mt-2" />
                        </div>

                        <!-- Capacity -->
                        <div class="mb-4">
                            <x-input-label for="capacity" :value="__('Capacity (per section)')" />
                            <x-text-input id="capacity" class="block mt-1 w-full" type="number" name="capacity" :value="old('capacity', 30)" required />
                            <x-input-error :messages="$errors->get('capacity')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('admin.sections.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">Cancel</a>
                            <x-primary-button>
                                {{ __('Identify and Create Sections') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        </div>
</x-admin-layout>
