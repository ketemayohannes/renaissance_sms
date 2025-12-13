<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Term') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form action="{{ route('admin.terms.update', $term) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Academic Year -->
                            <div>
                                <label for="academic_year_id" class="block text-sm font-medium text-gray-700">Academic Year</label>
                                <select name="academic_year_id" id="academic_year_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    @foreach($academicYears as $year)
                                        <option value="{{ $year->id }}" {{ $term->academic_year_id == $year->id ? 'selected' : '' }}>{{ $year->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Name -->
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Term Name</label>
                                <input type="text" name="name" id="name" value="{{ $term->name }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            </div>

                            <!-- Type -->
                            <div>
                                <label for="type" class="block text-sm font-medium text-gray-700">Type</label>
                                <select name="type" id="type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    <option value="semester" {{ $term->type == 'semester' ? 'selected' : '' }}>Semester</option>
                                    <option value="quarter" {{ $term->type == 'quarter' ? 'selected' : '' }}>Quarter</option>
                                </select>
                            </div>

                            <!-- Term Number -->
                            <div>
                                <label for="term_number" class="block text-sm font-medium text-gray-700">Term Number</label>
                                <input type="number" name="term_number" id="term_number" value="{{ $term->term_number }}" min="1" max="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            </div>

                            <!-- Start Date -->
                            <div>
                                <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                                <input type="date" name="start_date" id="start_date" value="{{ $term->start_date->format('Y-m-d') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            </div>

                            <!-- End Date -->
                            <div>
                                <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                                <input type="date" name="end_date" id="end_date" value="{{ $term->end_date->format('Y-m-d') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            </div>

                            <!-- Grading Locks -->
                            <div class="md:col-span-2 border-t pt-4 mt-2">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Grading Controls</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="block">
                                        <label for="is_grading_open" class="inline-flex items-center">
                                            <input id="is_grading_open" type="checkbox" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500" name="is_grading_open" value="1" {{ old('is_grading_open', $term->is_grading_open) ? 'checked' : '' }}>
                                            <span class="ml-2 text-sm text-gray-700 font-bold">{{ __('Subject Grading Open') }}</span>
                                        </label>
                                        <p class="text-xs text-gray-500 ml-6">Allow teachers to enter marks for subjects.</p>
                                    </div>
                                    <div class="block">
                                        <label for="is_master_grading_open" class="inline-flex items-center">
                                            <input id="is_master_grading_open" type="checkbox" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500" name="is_master_grading_open" value="1" {{ old('is_master_grading_open', $term->is_master_grading_open) ? 'checked' : '' }}>
                                            <span class="ml-2 text-sm text-gray-700 font-bold">{{ __('Master Sheet Grading Open') }}</span>
                                        </label>
                                        <p class="text-xs text-gray-500 ml-6">Allow admins to enter marks in Master Sheet.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <a href="{{ route('admin.terms.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded mr-2">Cancel</a>
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                                Update Term
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
