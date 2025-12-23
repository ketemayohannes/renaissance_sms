<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Student ID Card Generation') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Breadcrumb -->
            <x-breadcrumb :items="[
                ['label' => 'ID Card Generation', 'url' => '#']
            ]" />
            
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
                        <h3 class="font-bold text-blue-800 mb-2">📋 Instructions</h3>
                        <p class="text-blue-700 text-sm">
                            Select a section below to generate ID cards for all students in that section. 
                            Cards are formatted for standard ID card size (86mm × 54mm).
                        </p>
                    </div>

                    <h3 class="text-lg font-medium text-gray-900 mb-4">Select Section</h3>
                    
                    <div class="space-y-4">
                        @foreach($gradeLevels as $gradeLevel)
                            <div class="border rounded-lg p-4">
                                <h4 class="font-bold text-gray-800 mb-3">{{ $gradeLevel->name }}</h4>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                                    @forelse($gradeLevel->sections as $section)
                                        <a href="{{ route('admin.sections.bulk-id-cards', $section) }}" 
                                           class="block p-3 bg-green-50 hover:bg-green-100 border border-green-200 rounded-lg text-center transition">
                                            <span class="font-semibold text-green-800">{{ $section->name }}</span>
                                            <span class="block text-xs text-green-600 mt-1">Generate Cards</span>
                                        </a>
                                    @empty
                                        <p class="text-gray-500 italic text-sm col-span-4">No sections for this grade</p>
                                    @endforelse
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
