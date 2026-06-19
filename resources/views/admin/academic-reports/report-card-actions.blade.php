<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full relative z-10">
            <span class="text-xl font-bold text-slate-800">Report Card Actions</span>
            <a href="{{ route('admin.academic-reports.index') }}" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-600 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-50 transition-all shadow-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Back to Reports
            </a>
        </div>
    </x-slot>

    <div class="space-y-8 pb-12">
        <x-breadcrumb :items="[
            ['label' => 'Academic Reports', 'url' => route('admin.academic-reports.index')],
            ['label' => 'Report Card Actions', 'url' => '#']
        ]" />

        {{-- Context Banner --}}
        <div class="relative overflow-hidden rounded-[2rem] bg-slate-900 px-8 py-8 text-white shadow-2xl">
            <div class="absolute inset-0 bg-gradient-to-br from-indigo-600 via-indigo-700 to-violet-800 opacity-95"></div>
            <div class="absolute -right-16 -top-16 w-64 h-64 bg-white/10 rounded-full blur-3xl animate-pulse"></div>
            <div class="relative z-10 flex flex-col md:flex-row md:items-center gap-6">
                <div class="flex-1">
                    <div class="inline-flex items-center gap-2 px-3 py-1 bg-white/10 backdrop-blur-md rounded-full border border-white/20 mb-3">
                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-300 animate-ping"></span>
                        <span class="text-[9px] font-black uppercase tracking-[0.2em] text-indigo-100">Action Required</span>
                    </div>
                    <h2 class="text-2xl md:text-3xl font-black uppercase tracking-tighter italic">Report Card Ready</h2>
                    <p class="text-indigo-100/70 text-xs font-bold mt-1">Choose how you want to process the report cards below.</p>
                </div>
                <div class="flex flex-col gap-2 min-w-[200px] bg-white/10 backdrop-blur-xl rounded-2xl border border-white/20 p-5">
                    <div>
                        <p class="text-[9px] font-black text-indigo-300 uppercase tracking-[0.25em]">Section</p>
                        <p class="font-black text-lg tracking-tight">{{ $section->gradeLevel->name ?? '' }} – {{ $section->name }}</p>
                    </div>
                    <div class="mt-1 pt-3 border-t border-white/20">
                        <p class="text-[9px] font-black text-indigo-300 uppercase tracking-[0.25em]">Term</p>
                        <p class="font-bold text-sm">{{ $termLabel }} · {{ $academicYear->name }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Action Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            {{-- Option 1: View / Print --}}
            <a href="{{ route('admin.section-grades.bulk-print-report-cards', ['section' => $section->id, 'academic_year_id' => $academicYear->id, 'term_id' => $termId]) }}"
               class="group relative flex flex-col bg-white/80 backdrop-blur-xl border-2 border-slate-100 rounded-[2.5rem] p-10 shadow-xl hover:shadow-2xl hover:-translate-y-1 hover:border-indigo-400 transition-all duration-300 cursor-pointer overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-br from-indigo-50/40 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-[2.5rem]"></div>

                <div class="relative z-10">
                    <div class="w-14 h-14 rounded-2xl bg-indigo-600 shadow-xl shadow-indigo-200 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-black text-slate-900 tracking-tight uppercase mb-2">View &amp; Print</h3>
                    <p class="text-slate-500 text-sm font-semibold leading-relaxed">Open the report cards in a printable browser view. Use <kbd class="px-1.5 py-0.5 bg-slate-100 rounded text-xs font-mono">Ctrl+P</kbd> or the Print button to save as PDF.</p>

                    <div class="mt-8 inline-flex items-center gap-2 text-indigo-600 font-black text-xs uppercase tracking-widest group-hover:gap-3 transition-all">
                        Open Print View
                        <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </div>
                </div>
            </a>

            {{-- Option 2: Request ZIP Export --}}
            <div class="group relative flex flex-col bg-white/80 backdrop-blur-xl border-2 border-slate-100 rounded-[2.5rem] p-10 shadow-xl hover:shadow-2xl hover:-translate-y-1 hover:border-emerald-400 transition-all duration-300 overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-br from-emerald-50/40 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-[2.5rem]"></div>

                <div class="relative z-10 flex flex-col h-full">
                    <div class="w-14 h-14 rounded-2xl bg-emerald-600 shadow-xl shadow-emerald-200 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-black text-slate-900 tracking-tight uppercase mb-2">Export as ZIP</h3>
                    <p class="text-slate-500 text-sm font-semibold leading-relaxed">Queue a background job to generate individual PDFs for every student and bundle them into a downloadable ZIP archive.</p>

                    <div class="mt-auto pt-8">
                        <form action="{{ route('admin.section-grades.bulk-export-report-cards', ['section' => $section->id]) }}"
                              method="GET"
                              onsubmit="return confirmExport(this)">
                            <input type="hidden" name="academic_year_id" value="{{ $academicYear->id }}">
                            <input type="hidden" name="term_id" value="{{ $termId }}">

                            <button type="submit"
                                    id="btn-export-zip"
                                    class="w-full inline-flex items-center justify-center gap-3 px-6 py-4 bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs uppercase tracking-widest rounded-2xl shadow-lg shadow-emerald-100 transition-all group-hover:shadow-emerald-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                                Request ZIP Export
                            </button>
                        </form>

                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-3 text-center">
                            You'll be notified when the export is ready in
                            <a href="{{ route('admin.report-cards.exports') }}" class="text-indigo-500 hover:underline">Export Logistics</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Info note --}}
        <div class="flex items-start gap-4 bg-amber-50 border border-amber-100 rounded-2xl p-5">
            <div class="w-8 h-8 rounded-full bg-amber-100 flex items-center justify-center text-amber-500 shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-xs font-black text-amber-800 uppercase tracking-widest mb-1">Note</p>
                <p class="text-xs text-amber-700 font-semibold">The ZIP export runs as a background job and may take several minutes for large classes. You can continue using the system and download the archive from the <a href="{{ route('admin.report-cards.exports') }}" class="underline">Export Logistics</a> page when it's ready.</p>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function confirmExport(form) {
            const btn = document.getElementById('btn-export-zip');
            btn.disabled = true;
            btn.innerHTML = `
                <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Queuing Export…`;
            return true;
        }
    </script>
    @endpush
</x-admin-layout>
