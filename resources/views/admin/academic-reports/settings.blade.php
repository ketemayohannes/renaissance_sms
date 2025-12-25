<x-admin-layout>
    <x-slot name="header">Academic Report Settings</x-slot>

    <div class="space-y-6">
        <x-breadcrumb :items="[
            ['label' => 'Academic Reports', 'url' => route('admin.academic-reports.index')],
            ['label' => 'Roster Settings', 'url' => '#']
        ]" />

        <div class="card overflow-hidden">
            <div class="p-6">
                    @if (session('success'))
                        <div class="mb-4 font-medium text-sm text-green-600">
                            {{ session('success') }}
                        </div>
                    @endif



                    <form action="{{ route('admin.academic-reports.settings.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('POST')

                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Roster (Mark Sheet) Settings</h3>
                            
                            <!-- School Name -->
                            <div class="mb-4">
                                <x-input-label for="school_name" :value="__('School Name (Optional - defaults to general setting)')" />
                                <x-text-input id="school_name" name="school_name" type="text" class="mt-1 block w-full" :value="old('school_name', $settings->school_name)" />
                                <x-input-error :messages="$errors->get('school_name')" class="mt-2" />
                            </div>

                            <!-- Logo -->
                            <div class="mb-4">
                                <x-input-label for="roster_logo" :value="__('Roster Logo')" />
                                @if($settings->roster_logo_path)
                                    <div class="mt-2 mb-4">
                                        <p class="text-sm text-gray-600 mb-2">Current Logo:</p>
                                        <div class="rounded-full border-[6px] border-[#fb198d] p-0.5 bg-white inline-block shadow-md">
                                            <div class="rounded-full bg-[#82b1ff] p-2 flex items-center justify-center">
                                                <img src="{{ asset('storage/' . $settings->roster_logo_path) }}" alt="Roster Logo" class="h-16 w-16 object-contain bg-white border border-[#0d47a1] p-1">
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                <input id="roster_logo" name="roster_logo" type="file" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" accept="image/*" />
                                <x-input-error :messages="$errors->get('roster_logo')" class="mt-2" />
                                <p class="mt-2 text-sm text-gray-500">Recommended: Square image with transparent or white background.</p>
                            </div>

                            <!-- Subject Ordering -->
                            <div class="mt-8 border-t pt-8">
                                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                                    <div>
                                        <h3 class="text-lg font-medium text-gray-900">Subject Display Order</h3>
                                        <p class="text-sm text-gray-600">Set the order in which subjects appear in the Roster. Lower numbers appear first.</p>
                                    </div>
                                    
                                    <!-- Grade Level Filter -->
                                    <div class="flex items-center gap-2 bg-indigo-50 p-2 rounded-lg border border-indigo-100">
                                        <label for="grade_filter" class="text-sm font-semibold text-indigo-900 whitespace-nowrap">Filter by Grade:</label>
                                        <select id="grade_filter" class="rounded-md border-indigo-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-1">
                                            <option value="">All Subjects</option>
                                            @foreach($gradeLevels as $grade)
                                                <option value="{{ $grade->id }}">{{ $grade->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                
                                <div id="subject_grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    @foreach($subjects as $subject)
                                        <div class="subject-card flex items-center space-x-3 bg-gray-50 p-3 rounded-lg border border-gray-200 transition-all duration-200" 
                                             data-grade-levels="{{ json_encode($subject->gradeLevels->pluck('id')) }}">
                                            <div class="flex-shrink-0 w-16">
                                                <x-text-input 
                                                    name="subject_order[{{ $subject->id }}]" 
                                                    type="number" 
                                                    class="w-full text-center px-1 py-1 text-sm" 
                                                    :value="old('subject_order.' . $subject->id, $settings->display_options['subject_order'][$subject->id] ?? '')"
                                                    placeholder="--"
                                                />
                                            </div>
                                            <div class="flex-grow">
                                                <span class="text-sm font-medium text-gray-700 leading-tight block">{{ $subject->name }}</span>
                                                <span class="text-xs text-gray-500 block">{{ $subject->code }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        @push('scripts')
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const filter = document.getElementById('grade_filter');
                                const cards = document.querySelectorAll('.subject-card');
                                
                                filter.addEventListener('change', function() {
                                    const gradeId = this.value;
                                    
                                    cards.forEach(card => {
                                        if (!gradeId) {
                                            card.style.display = 'flex';
                                        } else {
                                            const gradeLevels = JSON.parse(card.dataset.gradeLevels);
                                            if (gradeLevels.includes(parseInt(gradeId))) {
                                                card.style.display = 'flex';
                                            } else {
                                                card.style.display = 'none';
                                            }
                                        }
                                    });
                                });
                            });
                        </script>
                        @endpush

                        <div class="flex items-center gap-4">
                            <x-primary-button>{{ __('Save Settings') }}</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    </div>
</x-admin-layout>
