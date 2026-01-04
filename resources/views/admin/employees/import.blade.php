<x-admin-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="font-heading font-bold text-2xl text-slate-900">Import Staff</h2>
                <p class="text-slate-500 text-sm mt-1">Bulk register teachers and administrators via CSV or Excel.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.employees.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-white border border-slate-200 text-slate-700 text-sm font-semibold rounded-xl hover:bg-slate-50 transition-all gap-2 shadow-sm border-b-2">
                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to Staff
                </a>
            </div>
        </div>
    </x-slot>

    <div class="max-w-5xl mx-auto space-y-6" x-data="{ activeTab: 'academic' }">
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

        <!-- Tab Navigation -->
        <div class="flex p-1.5 bg-slate-100 rounded-[2rem] w-fit mx-auto shadow-inner border border-slate-200/50">
            <button @click="activeTab = 'academic'" 
                :class="activeTab === 'academic' ? 'bg-white text-indigo-600 shadow-sm border border-slate-100' : 'text-slate-500 hover:text-slate-700'"
                class="px-8 py-3 rounded-[1.5rem] text-sm font-bold transition-all flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
                Academic Staff
            </button>
            <button @click="activeTab = 'administrative'" 
                :class="activeTab === 'administrative' ? 'bg-white text-emerald-600 shadow-sm border border-slate-100' : 'text-slate-500 hover:text-slate-700'"
                class="px-8 py-3 rounded-[1.5rem] text-sm font-bold transition-all flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
                Administrative Staff
            </button>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
            <!-- Instructions Column -->
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white/80 backdrop-blur-xl border border-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 p-8">
                    <h3 class="text-lg font-bold text-slate-900 mb-6">Import Guide</h3>
                    
                    <div class="space-y-8">
                        <div class="flex gap-4">
                            <div class="w-8 h-8 rounded-full bg-slate-50 text-slate-400 flex items-center justify-center font-bold text-sm shrink-0 border border-slate-100">1</div>
                            <div>
                                <p class="font-bold text-slate-800 text-sm">Download Template</p>
                                <p class="text-slate-500 text-xs mt-1">Get the specific template for your staff category. It includes only relevant fields and specific legends.</p>
                                
                                <div class="mt-4 space-y-2">
                                    <template x-if="activeTab === 'academic'">
                                        <a href="{{ route('admin.employees.academic.template') }}" class="flex items-center justify-center gap-2 px-4 py-3 bg-indigo-50 text-indigo-700 font-bold text-xs rounded-xl hover:bg-indigo-100 transition-all border border-indigo-100">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                            </svg>
                                            Download Academic Template
                                        </a>
                                    </template>
                                    <template x-if="activeTab === 'administrative'">
                                        <a href="{{ route('admin.employees.administrative.template') }}" class="flex items-center justify-center gap-2 px-4 py-3 bg-emerald-50 text-emerald-700 font-bold text-xs rounded-xl hover:bg-emerald-100 transition-all border border-emerald-100">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                            </svg>
                                            Download Admin Template
                                        </a>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <div class="flex gap-4">
                            <div class="w-8 h-8 rounded-full bg-slate-50 text-slate-400 flex items-center justify-center font-bold text-sm shrink-0 border border-slate-100">2</div>
                            <div>
                                <p class="font-bold text-slate-800 text-sm">Fill Local Data</p>
                                <p class="text-slate-500 text-xs mt-1 leading-relaxed">Refer to the <span class="font-bold text-slate-700 underline decoration-slate-200">Legends</span> sheet in the Excel file for valid IDs and role names.</p>
                            </div>
                        </div>

                        <div class="flex gap-4">
                            <div class="w-8 h-8 rounded-full bg-slate-50 text-slate-400 flex items-center justify-center font-bold text-sm shrink-0 border border-slate-100">3</div>
                            <div>
                                <p class="font-bold text-slate-800 text-sm">Upload File</p>
                                <p class="text-slate-500 text-xs mt-1 leading-relaxed">Upload the completed file. The system will create portal accounts automatically using default credentials.</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 pt-6 border-t border-slate-50">
                        <div class="bg-amber-50 rounded-2xl p-4 border border-amber-100">
                            <div class="flex gap-3">
                                <svg class="w-5 h-5 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <div class="text-[10px] text-amber-800 leading-relaxed">
                                    <span class="font-bold">Security Note:</span> Accounts are created with password <code class="bg-amber-100 px-1 py-0.5 rounded text-amber-900 font-mono font-bold mx-0.5">staff1234</code>.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upload Zone -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Academic Import Card -->
                <div x-show="activeTab === 'academic'" x-transition:enter="transition ease-out duration-300 transform" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
                    class="bg-white/80 backdrop-blur-xl border border-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 p-8 lg:p-12 relative overflow-hidden">
                    
                    <div class="absolute -top-12 -right-12 w-48 h-48 bg-indigo-50/50 rounded-full blur-3xl"></div>
                    
                    <div class="relative z-10 space-y-8">
                        <div class="text-center md:text-left">
                            <h3 class="text-2xl font-black text-slate-900 tracking-tight">Academic Staff Import</h3>
                            <p class="text-slate-500 text-sm mt-2">Use this to bulk register Teachers, Supervisors, and Principals.</p>
                        </div>

                        <form action="{{ route('admin.employees.upload') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                            @csrf
                            <input type="hidden" name="staff_category" value="academic">
                            
                            <div class="space-y-4" x-data="{ fileName: '', fileSize: '' }">
                                <div class="relative group">
                                    <input type="file" name="file" accept=".csv,.xlsx,.xls" required
                                           class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                                           @change="
                                               if ($event.target.files.length > 0) {
                                                   fileName = $event.target.files[0].name;
                                                   const bytes = $event.target.files[0].size;
                                                   if (bytes < 1024) fileSize = bytes + ' B';
                                                   else if (bytes < 1048576) fileSize = (bytes / 1024).toFixed(1) + ' KB';
                                                   else fileSize = (bytes / 1048576).toFixed(2) + ' MB';
                                               }
                                           ">
                                    <div class="border-2 border-dashed rounded-[2.5rem] p-12 transition-all text-center space-y-4"
                                         :class="fileName ? 'border-indigo-400 bg-indigo-50/20' : 'border-slate-100 group-hover:border-indigo-300 group-hover:bg-slate-50/50'">
                                        
                                        <div class="w-20 h-20 bg-indigo-50 rounded-3xl flex items-center justify-center mx-auto transition-all group-hover:scale-110 shadow-sm"
                                             :class="fileName ? 'bg-indigo-100' : ''">
                                            <svg :class="fileName ? 'text-indigo-600' : 'text-indigo-400'" class="w-10 h-10 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                            </svg>
                                        </div>

                                        <div class="space-y-1">
                                            <p class="text-slate-900 font-bold text-lg" x-text="fileName ? fileName : 'Select Academic Template'"></p>
                                            <p class="text-slate-500 text-xs font-medium" x-text="fileName ? fileSize + ' • Ready to import' : 'Drag & drop your Excel/CSV here or click to browse'"></p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" 
                                    class="w-full flex items-center justify-center gap-3 px-8 py-5 bg-indigo-600 text-white font-black rounded-3xl hover:bg-indigo-700 transition-all shadow-xl shadow-indigo-100 text-lg uppercase tracking-wider">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                                Process Academic Import
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Administrative Import Card -->
                <div x-show="activeTab === 'administrative'" x-transition:enter="transition ease-out duration-300 transform" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
                    class="bg-white/80 backdrop-blur-xl border border-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 p-8 lg:p-12 relative overflow-hidden">
                    
                    <div class="absolute -top-12 -right-12 w-48 h-48 bg-emerald-50/50 rounded-full blur-3xl"></div>
                    
                    <div class="relative z-10 space-y-8">
                        <div class="text-center md:text-left">
                            <h3 class="text-2xl font-black text-emerald-900 tracking-tight">Administrative Staff Import</h3>
                            <p class="text-slate-500 text-sm mt-2">Use this for HR, Finance, Secretaries, and other support staff.</p>
                        </div>

                        <form action="{{ route('admin.employees.upload') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                            @csrf
                            <input type="hidden" name="staff_category" value="administrative">
                            
                            <div class="space-y-4" x-data="{ fileName: '', fileSize: '' }">
                                <div class="relative group">
                                    <input type="file" name="file" accept=".csv,.xlsx,.xls" required
                                           class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                                           @change="
                                               if ($event.target.files.length > 0) {
                                                   fileName = $event.target.files[0].name;
                                                   const bytes = $event.target.files[0].size;
                                                   if (bytes < 1024) fileSize = bytes + ' B';
                                                   else if (bytes < 1048576) fileSize = (bytes / 1024).toFixed(1) + ' KB';
                                                   else fileSize = (bytes / 1048576).toFixed(2) + ' MB';
                                               }
                                           ">
                                    <div class="border-2 border-dashed rounded-[2.5rem] p-12 transition-all text-center space-y-4"
                                         :class="fileName ? 'border-emerald-400 bg-emerald-50/20' : 'border-slate-100 group-hover:border-emerald-300 group-hover:bg-slate-50/50'">
                                        
                                        <div class="w-20 h-20 bg-emerald-50 rounded-3xl flex items-center justify-center mx-auto transition-all group-hover:scale-110 shadow-sm"
                                             :class="fileName ? 'bg-emerald-100' : ''">
                                            <svg :class="fileName ? 'text-emerald-600' : 'text-emerald-400'" class="w-10 h-10 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                            </svg>
                                        </div>

                                        <div class="space-y-1">
                                            <p class="text-slate-900 font-bold text-lg" x-text="fileName ? fileName : 'Select Admin Template'"></p>
                                            <p class="text-slate-500 text-xs font-medium" x-text="fileName ? fileSize + ' • Ready to import' : 'Drag & drop your Excel/CSV here or click to browse'"></p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" 
                                    class="w-full flex items-center justify-center gap-3 px-8 py-5 bg-emerald-600 text-white font-black rounded-3xl hover:bg-emerald-700 transition-all shadow-xl shadow-emerald-100 text-lg uppercase tracking-wider">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                                Process Admin Import
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
