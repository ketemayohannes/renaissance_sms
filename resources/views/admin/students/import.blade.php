<x-admin-layout>
    <div class="min-h-screen bg-[#f8fafc] p-4 md:p-8">

        {{-- HEADER & BREADCRUMBS --}}
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
                    <p class="mt-2 text-slate-500 font-semibold">Upload your data to register multiple students at once.</p>
                </div>
            </div>
        </div>

        {{-- TAB SWITCHER --}}
        <div class="max-w-7xl mx-auto mb-8" x-data="{ tab: 'quick' }">
            <div class="inline-flex bg-white border border-slate-200 rounded-2xl p-1.5 shadow-sm gap-1">
                <button @click="tab = 'quick'"
                    :class="tab === 'quick'
                        ? 'bg-violet-600 text-white shadow-lg shadow-violet-200'
                        : 'text-slate-500 hover:text-slate-800'"
                    class="px-6 py-3 rounded-xl font-black text-[11px] uppercase tracking-widest transition-all duration-200 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    Quick Import
                    <span :class="tab === 'quick' ? 'bg-white/25 text-white' : 'bg-violet-100 text-violet-600'"
                        class="px-2 py-0.5 rounded-full text-[9px] font-black">NEW</span>
                </button>
                <button @click="tab = 'full'"
                    :class="tab === 'full'
                        ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-200'
                        : 'text-slate-500 hover:text-slate-800'"
                    class="px-6 py-3 rounded-xl font-black text-[11px] uppercase tracking-widest transition-all duration-200 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Full Import
                </button>
            </div>

            {{-- ============================= QUICK IMPORT TAB ============================= --}}
            <div x-show="tab === 'quick'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="mt-8 space-y-8">

                {{-- Quick Import Hero Banner --}}
                <div class="relative overflow-hidden bg-gradient-to-br from-violet-600 via-indigo-600 to-violet-800 rounded-[2.5rem] p-8 md:p-10 text-white shadow-2xl shadow-violet-300">
                    <div class="absolute inset-0 opacity-10">
                        <div class="absolute top-0 right-0 w-96 h-96 bg-white rounded-full -translate-y-1/2 translate-x-1/2"></div>
                        <div class="absolute bottom-0 left-0 w-64 h-64 bg-white rounded-full translate-y-1/2 -translate-x-1/2"></div>
                    </div>
                    <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                        <div>
                            <div class="inline-flex items-center gap-2 bg-white/20 backdrop-blur px-4 py-2 rounded-full text-[10px] font-black uppercase tracking-widest mb-4">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z"/></svg>
                                Fast-track enrollment
                            </div>
                            <h2 class="text-3xl font-black tracking-tight mb-2">Quick Import</h2>
                            <p class="text-white/75 font-semibold text-sm max-w-lg">Only 6 essential fields needed. Admission numbers, dates, and portal accounts are all generated automatically — perfect for rapid batch enrollment.</p>
                            <div class="flex flex-wrap items-center gap-3 mt-5">
                                @foreach(['first_name', 'father_name', 'grandfather_name', 'gender', 'grade_level', 'section_name'] as $field)
                                    <span class="px-3 py-1.5 bg-white/15 backdrop-blur border border-white/20 rounded-lg text-[10px] font-black uppercase tracking-wider">{{ $field }}</span>
                                @endforeach
                            </div>
                        </div>
                        <a href="{{ route('admin.students.download-quick-template') }}"
                            class="flex-shrink-0 px-8 py-4 bg-white text-violet-700 font-black text-[10px] uppercase tracking-widest rounded-2xl hover:bg-violet-50 transition-all flex items-center gap-3 shadow-xl shadow-violet-900/20 group">
                            <div class="w-8 h-8 rounded-xl bg-violet-100 flex items-center justify-center group-hover:scale-110 transition-transform">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path></svg>
                            </div>
                            Download Quick Template
                        </a>
                    </div>
                </div>

                {{-- Auto-filled fields info --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-white rounded-2xl border border-slate-100 p-5 flex items-start gap-4 shadow-sm">
                        <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center text-emerald-600 flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/></svg>
                        </div>
                        <div>
                            <p class="text-xs font-black text-slate-700 mb-1">Auto-generated Admission No.</p>
                            <p class="text-[11px] text-slate-500 font-semibold leading-relaxed">Format: <code class="bg-slate-100 px-1.5 py-0.5 rounded text-violet-600">QI-2026-0001</code><br>Unique per row, collision-safe.</p>
                        </div>
                    </div>
                    <div class="bg-white rounded-2xl border border-slate-100 p-5 flex items-start gap-4 shadow-sm">
                        <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center text-blue-600 flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <div>
                            <p class="text-xs font-black text-slate-700 mb-1">Admission Date = Today</p>
                            <p class="text-[11px] text-slate-500 font-semibold leading-relaxed">Set to <code class="bg-slate-100 px-1.5 py-0.5 rounded text-blue-600">{{ now()->format('Y-m-d') }}</code>. You can edit it from the student profile later.</p>
                        </div>
                    </div>
                    <div class="bg-white rounded-2xl border border-slate-100 p-5 flex items-start gap-4 shadow-sm">
                        <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center text-amber-600 flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </div>
                        <div>
                            <p class="text-xs font-black text-slate-700 mb-1">Portal Account Created</p>
                            <p class="text-[11px] text-slate-500 font-semibold leading-relaxed">Default password: <code class="bg-slate-100 px-1.5 py-0.5 rounded text-amber-600">student123</code>. Advise students to change it on first login.</p>
                        </div>
                    </div>
                </div>

                {{-- Error & Flash Messages --}}
                @if(session('error'))
                    <div class="p-4 bg-rose-50 border border-rose-100 rounded-2xl flex items-center gap-4 text-rose-600">
                        <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center shadow-sm flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        </div>
                        <span class="font-black text-[10px] uppercase tracking-widest">{{ session('error') }}</span>
                    </div>
                @endif

                @if($errors->any())
                    <div class="p-6 bg-rose-50 border border-rose-100 rounded-[2rem]">
                        <h4 class="text-xs font-black text-rose-600 uppercase tracking-widest mb-4 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Validation Errors
                        </h4>
                        <ul class="space-y-2">
                            @foreach ($errors->all() as $error)
                                <li class="text-sm font-semibold text-rose-500 flex items-center gap-2">
                                    <div class="w-1 h-1 rounded-full bg-rose-400"></div>{{ $error }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Quick Upload Zone --}}
                <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-xl shadow-slate-200/50 p-8">
                    <form action="{{ route('admin.students.quick-upload') }}" method="POST" enctype="multipart/form-data" x-data="{ dragging: false }">
                        @csrf
                        <div class="relative group"
                            @dragover.prevent="dragging = true"
                            @dragleave.prevent="dragging = false"
                            @drop.prevent="dragging = false">
                            <input type="file" name="file" id="quick-file" accept=".csv"
                                required
                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                                onchange="document.getElementById('quick-file-name').innerText = this.files[0].name; document.getElementById('quick-upload-hint').classList.add('hidden'); document.getElementById('quick-file-preview').classList.remove('hidden');">

                            <div :class="dragging ? 'border-violet-500 bg-violet-50/50 scale-[0.99] shadow-inner' : 'border-slate-200 bg-slate-50/50'"
                                class="w-full h-64 border-4 border-dashed rounded-[3rem] transition-all duration-300 flex flex-col items-center justify-center p-8 text-center">

                                <div id="quick-upload-hint" class="flex flex-col items-center">
                                    <div class="w-20 h-20 rounded-[2rem] bg-violet-100 flex items-center justify-center text-violet-600 mb-6 group-hover:scale-110 transition-transform">
                                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                                    </div>
                                    <h4 class="text-xl font-black text-slate-800 tracking-tight">Drop your Quick CSV here</h4>
                                    <p class="mt-2 text-sm font-bold text-slate-400 uppercase tracking-widest">or click to browse</p>
                                </div>

                                <div id="quick-file-preview" class="hidden flex flex-col items-center animate-bounce-slow">
                                    <div class="w-20 h-20 rounded-[2rem] bg-emerald-100 flex items-center justify-center text-emerald-600 mb-4 shadow-lg shadow-emerald-100">
                                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    </div>
                                    <span id="quick-file-name" class="bg-violet-600 text-white px-6 py-2 rounded-full text-xs font-black uppercase tracking-widest">Filename.csv</span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-10 flex flex-col md:flex-row items-center justify-between gap-6">
                            <div class="flex items-center gap-3">
                                <div class="w-2 h-2 rounded-full bg-violet-500 animate-pulse"></div>
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Maximum file size: 2MB · CSV format only</span>
                            </div>
                            <div class="flex items-center gap-4 w-full md:w-auto">
                                <a href="{{ route('admin.students.index') }}" class="flex-1 md:flex-none px-8 py-4 bg-slate-100 text-slate-500 font-black text-[10px] uppercase tracking-widest rounded-2xl hover:bg-slate-200 transition-all text-center">Cancel</a>
                                <button type="submit" class="flex-1 md:flex-none px-12 py-4 bg-violet-600 text-white font-black text-[10px] uppercase tracking-widest rounded-2xl hover:bg-violet-700 hover:shadow-xl hover:shadow-violet-200 transition-all relative group overflow-hidden">
                                    <span class="relative z-10 flex items-center justify-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                        Quick Import
                                    </span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- ============================= FULL IMPORT TAB ============================= --}}
            <div x-show="tab === 'full'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="mt-8 space-y-8">

                {{-- Instruction Steps --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
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
                                Follow Headers Exactly
                            </li>
                        </ul>
                    </div>

                    <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] p-8 border border-white shadow-xl shadow-slate-200/50 relative overflow-hidden group">
                        <div class="absolute -top-4 -right-4 w-24 h-24 bg-amber-50 rounded-full opacity-50 group-hover:scale-150 transition-transform duration-700"></div>
                        <span class="relative z-10 w-10 h-10 rounded-xl bg-amber-500 flex items-center justify-center text-white font-black mb-6">02</span>
                        <h3 class="text-xl font-bold text-slate-900 mb-3">Rules & Formats</h3>
                        <p class="text-sm font-semibold text-slate-500 leading-relaxed mb-4">Ensure dates follow YYYY-MM-DD and gender is simply M or F.</p>
                        <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100">
                            <code class="text-[10px] font-black text-indigo-600 italic">admission_date: 2024-09-01</code>
                        </div>
                    </div>

                    <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] p-8 border border-white shadow-xl shadow-slate-200/50 relative overflow-hidden group">
                        <div class="absolute -top-4 -right-4 w-24 h-24 bg-emerald-50 rounded-full opacity-50 group-hover:scale-150 transition-transform duration-700"></div>
                        <span class="relative z-10 w-10 h-10 rounded-xl bg-emerald-500 flex items-center justify-center text-white font-black mb-6">03</span>
                        <h3 class="text-xl font-bold text-slate-900 mb-3">Upload & Verify</h3>
                        <p class="text-sm font-semibold text-slate-500 leading-relaxed mb-4">Upload the file and check for any errors reported in the results table.</p>
                    </div>
                </div>

                {{-- Download Full Template Button --}}
                <div class="flex justify-end">
                    <a href="{{ route('admin.students.download-template') }}" class="px-6 py-4 bg-white border border-slate-200 rounded-2xl font-black text-[10px] uppercase tracking-widest text-slate-600 hover:bg-slate-50 hover:border-slate-300 transition-all flex items-center gap-3 group shadow-sm">
                        <div class="w-8 h-8 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600 group-hover:scale-110 transition-transform">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path></svg>
                        </div>
                        Download Full Template
                    </a>
                </div>

                {{-- Error & Flash Messages --}}
                @if(session('error'))
                    <div class="p-4 bg-rose-50 border border-rose-100 rounded-2xl flex items-center gap-4 text-rose-600">
                        <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center shadow-sm flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        </div>
                        <span class="font-black text-[10px] uppercase tracking-widest">{{ session('error') }}</span>
                    </div>
                @endif

                @if($errors->any())
                    <div class="p-6 bg-rose-50 border border-rose-100 rounded-[2rem]">
                        <h4 class="text-xs font-black text-rose-600 uppercase tracking-widest mb-4 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Validation Errors
                        </h4>
                        <ul class="space-y-2">
                            @foreach ($errors->all() as $error)
                                <li class="text-sm font-semibold text-rose-500 flex items-center gap-2">
                                    <div class="w-1 h-1 rounded-full bg-rose-400"></div>{{ $error }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Full Upload Zone --}}
                <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-xl shadow-slate-200/50 p-8">
                    <form action="{{ route('admin.students.upload') }}" method="POST" enctype="multipart/form-data" x-data="{ dragging: false }">
                        @csrf
                        <div class="relative group"
                            @dragover.prevent="dragging = true"
                            @dragleave.prevent="dragging = false"
                            @drop.prevent="dragging = false">
                            <input type="file" name="file" id="full-file" accept=".csv"
                                required
                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                                onchange="document.getElementById('full-file-name').innerText = this.files[0].name; document.getElementById('full-upload-hint').classList.add('hidden'); document.getElementById('full-file-preview').classList.remove('hidden');">

                            <div :class="dragging ? 'border-indigo-500 bg-indigo-50/50 scale-[0.99] shadow-inner' : 'border-slate-200 bg-slate-50/50'"
                                class="w-full h-64 border-4 border-dashed rounded-[3rem] transition-all duration-300 flex flex-col items-center justify-center p-8 text-center">

                                <div id="full-upload-hint" class="flex flex-col items-center">
                                    <div class="w-20 h-20 rounded-[2rem] bg-indigo-100 flex items-center justify-center text-indigo-600 mb-6 group-hover:scale-110 transition-transform">
                                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                                    </div>
                                    <h4 class="text-xl font-black text-slate-800 tracking-tight">Drop your CSV here</h4>
                                    <p class="mt-2 text-sm font-bold text-slate-400 uppercase tracking-widest">or click to browse local files</p>
                                </div>

                                <div id="full-file-preview" class="hidden flex flex-col items-center animate-bounce-slow">
                                    <div class="w-20 h-20 rounded-[2rem] bg-emerald-100 flex items-center justify-center text-emerald-600 mb-4 shadow-lg shadow-emerald-100">
                                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    </div>
                                    <span id="full-file-name" class="bg-indigo-600 text-white px-6 py-2 rounded-full text-xs font-black uppercase tracking-widest">Filename.csv</span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-10 flex flex-col md:flex-row items-center justify-between gap-6">
                            <div class="flex items-center gap-3">
                                <div class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></div>
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Maximum file size: 2MB · CSV format only</span>
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

                {{-- Required Columns Legend --}}
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
    </div>

    <style>
        @keyframes bounce-slow {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        .animate-bounce-slow { animation: bounce-slow 2s infinite; }
    </style>
</x-admin-layout>
