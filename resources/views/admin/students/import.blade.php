<x-admin-layout>
    <div class="min-h-screen bg-[#f8fafc] p-4 md:p-8">
        <!-- HEADER & BREADCRUMBS -->
        <div class="max-w-7xl mx-auto mb-10">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <div class="flex items-center gap-3 mb-4">
                        <a href="{{ route('admin.students.index') }}" class="w-10 h-10 rounded-xl bg-white shadow-sm border border-slate-200 flex items-center justify-center text-slate-400 hover:text-indigo-600 hover:border-indigo-100 transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                        </a>
                        <x-breadcrumb :items="[
                            ['label' => 'Students', 'url' => route('admin.students.index')],
                            ['label' => 'Bulk Import', 'url' => '#']
                        ]" />
                    </div>
                    <h1 class="text-4xl font-black text-slate-900 tracking-tight flex items-center gap-3">
                        <div class="w-12 h-12 rounded-2xl bg-indigo-600 flex items-center justify-center text-white shadow-lg shadow-indigo-200">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                        </div>
                        Import Students
                    </h1>
                    <p class="mt-2 text-slate-500 font-semibold flex items-center gap-2">
                        Upload your data to register multiple students at once.
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.students.download-template') }}" class="px-6 py-4 bg-white border border-slate-200 rounded-2xl font-black text-[10px] uppercase tracking-widest text-slate-600 hover:bg-slate-50 hover:border-slate-300 transition-all flex items-center gap-3 group shadow-sm">
                        <div class="w-8 h-8 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600 group-hover:scale-110 transition-transform">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path></svg>
                        </div>
                        Download Template
                    </a>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto space-y-8">
            <!-- INSTRUCTION STEPS -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Step 1 -->
                <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] p-8 border border-white shadow-xl shadow-slate-200/50 relative overflow-hidden group">
                    <div class="absolute -top-4 -right-4 w-24 h-24 bg-indigo-50 rounded-full opacity-50 group-hover:scale-150 transition-transform duration-700"></div>
                    <span class="relative z-10 w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center text-white font-black mb-6">01</span>
                    <h3 class="text-xl font-bold text-slate-900 mb-3">Prepare CSV</h3>
                    <p class="text-sm font-semibold text-slate-500 leading-relaxed mb-4">Download the official template and populate it with student records correctly.</p>
                    <ul class="space-y-2">
                        <li class="flex items-center gap-2 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Use CSV Format
                        </li>
                        <li class="flex items-center gap-2 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Follow Headers
                        </li>
                    </ul>
                </div>

                <!-- Step 2 -->
                <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] p-8 border border-white shadow-xl shadow-slate-200/50 relative overflow-hidden group">
                    <div class="absolute -top-4 -right-4 w-24 h-24 bg-amber-50 rounded-full opacity-50 group-hover:scale-150 transition-transform duration-700"></div>
                    <span class="relative z-10 w-10 h-10 rounded-xl bg-amber-500 flex items-center justify-center text-white font-black mb-6">02</span>
                    <h3 class="text-xl font-bold text-slate-900 mb-3">Rules & Formats</h3>
                    <p class="text-sm font-semibold text-slate-500 leading-relaxed mb-4">Ensure dates follow YYYY-MM-DD and gender is simply M or F.</p>
                    <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100">
                        <code class="text-[10px] font-black text-indigo-600 italic"> admission_date: 2024-09-01</code>
                    </div>
                </div>

                <!-- Step 3 -->
                <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] p-8 border border-white shadow-xl shadow-slate-200/50 relative overflow-hidden group">
                    <div class="absolute -top-4 -right-4 w-24 h-24 bg-emerald-50 rounded-full opacity-50 group-hover:scale-150 transition-transform duration-700"></div>
                    <span class="relative z-10 w-10 h-10 rounded-xl bg-emerald-500 flex items-center justify-center text-white font-black mb-6">03</span>
                    <h3 class="text-xl font-bold text-slate-900 mb-3">Upload & Verify</h3>
                    <p class="text-sm font-semibold text-slate-500 leading-relaxed mb-4">Upload the file and check for any errors reported in the results table.</p>
                </div>
            </div>

            <!-- UPLOAD ZONE SECTION -->
            <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-xl shadow-slate-200/50 p-8">
                @if(session('error'))
                    <div class="mb-8 p-4 bg-rose-50 border border-rose-100 rounded-2xl flex items-center gap-4 text-rose-600">
                        <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center shadow-sm">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        </div>
                        <span class="font-black text-[10px] uppercase tracking-widest">{{ session('error') }}</span>
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-8 p-6 bg-rose-50 border border-rose-100 rounded-[2rem]">
                        <h4 class="text-xs font-black text-rose-600 uppercase tracking-widest mb-4 flex items-center gap-2">
                             <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                             Validation Errors
                        </h4>
                        <ul class="space-y-2">
                            @foreach ($errors->all() as $error)
                                <li class="text-sm font-semibold text-rose-500 flex items-center gap-2">
                                    <div class="w-1 h-1 rounded-full bg-rose-400"></div>
                                    {{ $error }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('admin.students.upload') }}" method="POST" enctype="multipart/form-data" x-data="{ dragging: false }">
                    @csrf
                    <div 
                        class="relative group"
                        @dragover.prevent="dragging = true"
                        @dragleave.prevent="dragging = false"
                        @drop.prevent="dragging = false"
                    >
                        <input 
                            type="file" 
                            name="file" 
                            id="file" 
                            accept=".csv" 
                            required 
                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                            onchange="document.getElementById('file-name-display').innerText = this.files[0].name; document.getElementById('upload-instruction').classList.add('hidden'); document.getElementById('file-preview').classList.remove('hidden');"
                        >
                        
                        <div 
                            :class="dragging ? 'border-indigo-500 bg-indigo-50/50 scale-[0.99] shadow-inner' : 'border-slate-200 bg-slate-50/50'"
                            class="w-full h-64 border-4 border-dashed rounded-[3rem] transition-all duration-300 flex flex-col items-center justify-center p-8 text-center"
                        >
                            <div id="upload-instruction" class="flex flex-col items-center">
                                <div class="w-20 h-20 rounded-[2rem] bg-indigo-100 flex items-center justify-center text-indigo-600 mb-6 group-hover:scale-110 transition-transform">
                                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                                </div>
                                <h4 class="text-xl font-black text-slate-800 tracking-tight">Drop your CSV here</h4>
                                <p class="mt-2 text-sm font-bold text-slate-400 uppercase tracking-widest">or click to browse local files</p>
                            </div>

                            <div id="file-preview" class="hidden flex flex-col items-center animate-bounce-slow">
                                <div class="w-20 h-20 rounded-[2rem] bg-emerald-100 flex items-center justify-center text-emerald-600 mb-4 shadow-lg shadow-emerald-100">
                                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                                <span id="file-name-display" class="bg-indigo-600 text-white px-6 py-2 rounded-full text-xs font-black uppercase tracking-widest">Filename.csv</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-10 flex flex-col md:flex-row items-center justify-between gap-6">
                        <div class="flex items-center gap-3">
                            <div class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></div>
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Maximum file size: 2MB</span>
                        </div>
                        <div class="flex items-center gap-4 w-full md:w-auto">
                            <a href="{{ route('admin.students.index') }}" class="flex-1 md:flex-none px-8 py-4 bg-slate-100 text-slate-500 font-black text-[10px] uppercase tracking-widest rounded-2xl hover:bg-slate-200 transition-all text-center">Cancel</a>
                            <button type="submit" class="flex-1 md:flex-none px-12 py-4 bg-slate-900 text-white font-black text-[10px] uppercase tracking-widest rounded-2xl hover:bg-indigo-600 hover:shadow-xl hover:shadow-indigo-200 transition-all relative group overflow-hidden">
                                <span class="relative z-10">Process Import</span>
                                <div class="absolute inset-0 bg-gradient-to-r from-indigo-600 to-violet-600 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- TABLE LEGEND / REQUIRED COLUMNS -->
            <div class="bg-indigo-900/5 rounded-[2.5rem] p-8 border border-white">
                <h4 class="text-[10px] font-black text-indigo-900 uppercase tracking-[0.2em] mb-6 opacity-60">Required Column Mapping</h4>
                <div class="flex flex-wrap gap-2">
                    @foreach(['first_name', 'father_name', 'grandfather_name', 'gender', 'date_of_birth', 'admission_number', 'admission_date', 'grade_level', 'section_name', 'phone', 'primary_guardian_first_name', 'primary_guardian_phone', 'primary_guardian_relationship'] as $col)
                        <span class="px-4 py-2 bg-white border border-indigo-100 rounded-xl text-[10px] font-black text-indigo-600 shadow-sm">{{ $col }}</span>
                    @endforeach
                    <span class="px-4 py-2 bg-indigo-50 border border-indigo-100 rounded-xl text-[10px] font-black text-indigo-400 shadow-sm opacity-50 italic">+ 12 more optional fields</span>
                </div>
            </div>
        </div>
    </div>

    <style>
        @keyframes bounce-slow {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        .animate-bounce-slow {
            animation: bounce-slow 2s infinite;
        }
    </style>
</x-admin-layout>
