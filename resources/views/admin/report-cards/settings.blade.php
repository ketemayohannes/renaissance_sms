<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Report Card Settings') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-breadcrumb :items="[
                ['label' => 'Report Card Settings', 'url' => '#']
            ]" />

            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <strong class="font-bold">Success!</strong>
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form action="{{ route('admin.report-cards.update-settings') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- School Info -->
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 border-b pb-2 mb-4">School Infomation</h3>
                                
                                <div class="mb-4">
                                    <label for="school_name" class="block text-sm font-medium text-gray-700">School Name</label>
                                    <input type="text" name="school_name" id="school_name" value="{{ old('school_name', $settings->school_name) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>

                                <div class="mb-4">
                                    <label for="school_address" class="block text-sm font-medium text-gray-700">Address / P.O.Box</label>
                                    <textarea name="school_address" id="school_address" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('school_address', $settings->school_address) }}</textarea>
                                </div>

                                <div class="mb-4">
                                    <label for="website" class="block text-sm font-medium text-gray-700">Website</label>
                                    <input type="text" name="website" id="website" value="{{ old('website', $settings->website) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>

                                <div class="mb-4">
                                    <label for="telephone" class="block text-sm font-medium text-gray-700">Telephone</label>
                                    <input type="text" name="telephone" id="telephone" value="{{ old('telephone', $settings->telephone) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                <div class="mb-4">
                                    <label for="email" class="block text-sm font-medium text-gray-700">School Email</label>
                                    <input type="email" name="email" id="email" value="{{ old('email', $settings->email) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                <div class="mb-4">
                                    <label for="po_box" class="block text-sm font-medium text-gray-700">P.O.Box</label>
                                    <input type="text" name="po_box" id="po_box" value="{{ old('po_box', $settings->po_box) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                            </div>

                            <!-- Logo & Config -->
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 border-b pb-2 mb-4">Logo & Appearance</h3>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700">School Logo</label>
                                    @if($settings->logo_path)
                                        <div class="mt-2 mb-2">
                                            <img src="/storage/{{ $settings->logo_path }}" alt="Current Logo" class="h-20 w-auto object-contain border p-1 rounded">
                                        </div>
                                    @endif
                                    <input type="file" name="logo" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                </div>

                                <h4 class="text-md font-medium text-gray-700 mt-6 mb-2">Display Options</h4>
                                <div class="space-y-2">
                                    <div class="flex items-center">
                                        <input type="checkbox" name="show_rank" id="show_rank" value="1" {{ ($settings->template_config['show_rank'] ?? true) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <label for="show_rank" class="ml-2 block text-sm text-gray-900">Show Class Rank</label>
                                    </div>

                                    <div class="flex items-center">
                                        <input type="checkbox" name="show_conduct" id="show_conduct" value="1" {{ ($settings->template_config['show_conduct'] ?? true) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <label for="show_conduct" class="ml-2 block text-sm text-gray-900">Show Conduct Grade</label>
                                    </div>
                                    
                                    <div class="flex items-center">
                                        <input type="checkbox" name="show_attendance" id="show_attendance" value="1" {{ ($settings->template_config['show_attendance'] ?? true) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <label for="show_attendance" class="ml-2 block text-sm text-gray-900">Show Attendance</label>
                                    </div>
                                </div>
                                
                                <h4 class="text-md font-medium text-gray-700 mt-6 mb-2">Behavior Traits (Text)</h4>
                                <div class="space-y-4">
                                    @foreach(range(1, 4) as $i)
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500">Trait {{ $i }}</label>
                                        <input type="text" name="traits[{{ $i }}]" value="{{ $settings->template_config['traits'][$i] ?? '' }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" placeholder="Trait description...">
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow">
                                Save Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
