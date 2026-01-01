<x-admin-layout>
    <x-slot name="header">Import Subjects</x-slot>

    <div class="space-y-6">
        <x-breadcrumb :items="[
            ['label' => 'Subjects', 'url' => route('admin.subjects.index')],
            ['label' => 'Import', 'url' => '#']
        ]" />

        <div class="max-w-2xl">
            <div class="card overflow-hidden">
                <div class="p-6">
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Instructions</h3>
                        <ul class="list-disc list-inside text-sm text-gray-600 space-y-1">
                            <li>Download the CSV template to see the required format.</li>
                            <li>The <strong>name</strong> and <strong>code</strong> columns are required.</li>
                            <li><strong>code</strong> must be unique across all subjects.</li>
                            <li><strong>is_elective</strong> should be 1 (for yes) or 0 (for no).</li>
                            <li><strong>sort_order</strong> should be a number.</li>
                        </ul>
                        <div class="mt-4">
                            <a href="{{ route('admin.subjects.template') }}" class="inline-flex items-center text-indigo-600 hover:text-indigo-900 font-medium">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                Download CSV Template
                            </a>
                        </div>
                    </div>

                    <form action="{{ route('admin.subjects.upload') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-4">
                            <label for="file" class="block text-sm font-medium text-gray-700 mb-2">Choose CSV File</label>
                            <input type="file" name="file" id="file" accept=".csv" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" required>
                            @error('file')<span class="text-red-600 text-sm mt-1">{{ $message }}</span>@enderror
                        </div>

                        <div class="flex justify-end mt-6">
                            <a href="{{ route('admin.subjects.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded mr-2">Cancel</a>
                            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">Upload and Import</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
