<x-admin-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="font-heading font-bold text-2xl text-slate-900">Import Staff</h2>
                <p class="text-slate-500 text-sm mt-1">Bulk register teachers and administrators via CSV.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.employees.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-white border border-slate-200 text-slate-700 text-sm font-semibold rounded-xl hover:bg-slate-50 transition-all gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to Staff
                </a>
            </div>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Breadcrumb -->
        <x-breadcrumb :items="[
            ['label' => 'Staff Management', 'url' => route('admin.employees.index')],
            ['label' => 'Import Staff', 'url' => '#']
        ]" />

        @if(session('import_errors'))
            <div class="bg-red-50 border border-red-100 rounded-3xl p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-red-100 rounded-xl flex items-center justify-center text-red-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-red-900 font-bold">Import Partially Failed</h4>
                        <p class="text-red-600 text-sm">The following rows could not be processed:</p>
                    </div>
                </div>
                <div class="bg-white/50 rounded-2xl overflow-hidden border border-red-100">
                    <div class="max-h-60 overflow-y-auto p-4 space-y-2">
                        @foreach(session('import_errors') as $error)
                            <div class="flex gap-2 text-sm text-red-700">
                                <span class="font-bold">•</span>
                                <span>{{ $error }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
            <div class="p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
                    <!-- Instructions -->
                    <div class="space-y-6">
                        <div>
                            <h3 class="text-lg font-bold text-slate-900 mb-2">Instructions</h3>
                            <p class="text-slate-600 text-sm leading-relaxed">
                                Follow these steps to ensure a successful bulk import of your staff records.
                            </p>
                        </div>

                        <div class="space-y-4">
                            <div class="flex gap-4">
                                <div class="w-8 h-8 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-sm shrink-0">1</div>
                                <div>
                                    <p class="font-semibold text-slate-800 text-sm">Download Template</p>
                                    <p class="text-slate-500 text-xs mt-1">Get the official staff import CSV template with all required columns.</p>
                                    <a href="{{ route('admin.employees.download-template') }}" class="inline-flex items-center gap-1.5 text-indigo-600 font-bold text-xs mt-2 hover:underline">
                                        Download CSV Template
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                        </svg>
                                    </a>
                                </div>
                            </div>

                            <div class="flex gap-4">
                                <div class="w-8 h-8 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-sm shrink-0">2</div>
                                <div>
                                    <p class="font-semibold text-slate-800 text-sm">Prepare Your Data</p>
                                    <p class="text-slate-500 text-xs mt-1">Fill in the employee details. Use "academic" or "administrative" for the Staff Category column.</p>
                                </div>
                            </div>

                            <div class="flex gap-4">
                                <div class="w-8 h-8 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-sm shrink-0">3</div>
                                <div>
                                    <p class="font-semibold text-slate-800 text-sm">Upload & Process</p>
                                    <p class="text-slate-500 text-xs mt-1">Upload your completed CSV file. The system will create user accounts and employee profiles automatically.</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-amber-50 rounded-2xl p-4 border border-amber-100">
                            <div class="flex gap-3">
                                <svg class="w-5 h-5 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <div class="text-xs text-amber-800 leading-relaxed">
                                    <span class="font-bold">Important:</span> User accounts will be created with a default password <code class="bg-amber-100 px-1.5 py-0.5 rounded text-amber-900 font-mono font-bold">staff1234</code>. Staff will be prompted to change this upon first login.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Upload Form -->
                    <div class="relative">
                        <form action="{{ route('admin.employees.upload') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                            @csrf
                            <div class="space-y-4">
                                <label class="block text-sm font-bold text-slate-700">Choose CSV File</label>
                                <div class="relative group">
                                    <input type="file" name="file" accept=".csv" required
                                           class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                                    <div class="border-2 border-dashed border-slate-200 group-hover:border-indigo-400 group-hover:bg-indigo-50/30 rounded-3xl p-10 transition-all text-center space-y-4">
                                        <div class="w-16 h-16 bg-slate-50 group-hover:bg-indigo-100 rounded-2xl flex items-center justify-center mx-auto transition-colors">
                                            <svg class="w-8 h-8 text-slate-400 group-hover:text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-slate-900 font-bold">Click to upload or drag & drop</p>
                                            <p class="text-slate-500 text-xs mt-1">Only .CSV files allowed (Max. 2MB)</p>
                                        </div>
                                    </div>
                                </div>
                                @error('file')
                                    <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                                @enderror
                            </div>

                            <button type="submit" 
                                    class="w-full flex items-center justify-center gap-2 px-6 py-4 bg-slate-900 text-white font-bold rounded-2xl hover:bg-slate-800 transition-all shadow-lg shadow-slate-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                                Start Import Process
                            </button>
                        </form>

                        <div class="mt-8 pt-8 border-t border-slate-100">
                            <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Required Columns</h4>
                            <div class="flex flex-wrap gap-2">
                                @php
                                    $required = ['first_name', 'last_name', 'email', 'gender', 'staff_category'];
                                @endphp
                                @foreach($required as $col)
                                    <span class="px-2.5 py-1 bg-slate-50 text-slate-600 text-[10px] font-bold rounded-lg border border-slate-100 font-mono">
                                        {{ $col }}
                                    </span>
                                @endforeach
                                <span class="px-2.5 py-1 text-slate-400 text-[10px] font-bold font-mono">+ 25 others</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
