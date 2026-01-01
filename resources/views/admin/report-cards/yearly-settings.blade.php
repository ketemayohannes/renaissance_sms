<x-admin-layout>
    <x-slot name="header">Yearly Report Config</x-slot>

    <div class="space-y-8 pb-32">
        <!-- Header & Navigation -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <x-breadcrumb :items="[
                    ['label' => 'Academic Reports', 'url' => route('admin.academic-reports.index')],
                    ['label' => 'Yearly Certification', 'url' => '#']
                ]" />
                <h1 class="text-4xl font-black text-slate-900 tracking-tight mt-2 flex items-center gap-3">
                    <span class="w-1.5 h-8 bg-amber-500 rounded-full"></span>
                    Yearly Certification Config
                </h1>
                <p class="text-slate-500 font-semibold mt-1 uppercase text-[10px] tracking-[0.2em] italic">Promotion rules, grading legends and administrative signatures</p>
            </div>
        </div>

        @if (session('success'))
            <div class="bg-emerald-50/50 backdrop-blur-md border border-emerald-100 p-6 rounded-[2rem] flex items-center gap-4 animate-in fade-in slide-in-from-top-4 duration-500">
                <div class="w-12 h-12 bg-emerald-100 rounded-2xl flex items-center justify-center text-emerald-600 shadow-sm">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <div>
                    <h3 class="text-emerald-900 font-black text-sm uppercase tracking-widest">Settings Synchronized</h3>
                    <p class="text-emerald-700 text-xs font-semibold mt-0.5">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        <form action="{{ route('admin.report-cards.update-yearly-settings') }}" method="POST">
            @csrf
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Academic Logic & Remarks -->
                <div class="space-y-8">
                    <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-2xl shadow-slate-200/50 p-10">
                        <div class="flex items-center gap-4 mb-8">
                            <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 text-indigo-600 flex items-center justify-center shadow-inner">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-black text-slate-900 uppercase tracking-tight">Grading Scale Legend</h3>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1 italic">Evaluation method description (printed on card)</p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <textarea name="evaluation_method" rows="6" 
                                      class="premium-input w-full py-6 min-h-[180px] font-mono text-sm leading-relaxed" 
                                      placeholder="e.g., 100-90 - A .... Excellent">{{ $settings->yearly_config['evaluation_method'] ?? "100-90 - A .... Excellent\n89-80 - B .... Very Good\n79-70 - C .... Satisfactory\n69-60 - D .... Fair\n<60 .... Poor" }}</textarea>
                            <div class="flex items-center gap-2 px-4 py-2 bg-indigo-50 rounded-xl border border-indigo-100">
                                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <span class="text-[9px] font-black text-indigo-600 uppercase tracking-widest">Use plain text format with consistent spacing for back-page alignment.</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-slate-900 rounded-[2.5rem] border border-white shadow-2xl p-10 relative overflow-hidden group">
                        <div class="absolute inset-0 bg-indigo-600 opacity-0 group-hover:opacity-10 transition-opacity"></div>
                        <div class="relative z-10">
                            <div class="flex items-center gap-4 mb-8">
                                <div class="w-12 h-12 rounded-2xl bg-white/10 text-white flex items-center justify-center shadow-inner">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-black text-white uppercase tracking-tight">Promotion Rules & Remarks</h3>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1 italic italic">Legal and academic interpretation for parents</p>
                                </div>
                            </div>
                            <textarea name="remark" rows="8" 
                                      class="w-full bg-white/5 border border-white/10 rounded-[1.5rem] px-6 py-6 text-sm font-semibold text-white focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 transition-all outline-none leading-relaxed placeholder-white/20 min-h-[220px]"
                                      placeholder="Promotion policy details...">{{ $settings->yearly_config['remark'] ?? "" }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Administrative Details & Footer -->
                <div class="space-y-8">
                    <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-2xl shadow-slate-200/50 p-10">
                        <div class="flex items-center gap-4 mb-10">
                            <div class="w-12 h-12 rounded-2xl bg-amber-500/10 text-amber-600 flex items-center justify-center shadow-inner">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-black text-slate-900 uppercase tracking-tight">Official Authority</h3>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1 italic">Signatory identity for certification</p>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="px-1 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Full Principal Name</label>
                            <input type="text" name="principal_name" value="{{ $settings->yearly_config['principal_name'] ?? '' }}" 
                                   class="premium-input w-full px-6 py-5 text-lg" placeholder="e.g., Dr. Yohannes Ketema">
                        </div>
                    </div>

                    <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-2xl shadow-slate-200/50 p-10">
                        <div class="flex items-center gap-4 mb-8">
                            <div class="w-12 h-12 rounded-2xl bg-violet-500/10 text-violet-600 flex items-center justify-center shadow-inner">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-black text-slate-900 uppercase tracking-tight">Parental Guidance</h3>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1 italic italic">Footer instructions and document handling rules</p>
                            </div>
                        </div>
                        <textarea name="parent_instructions" rows="6" 
                                  class="premium-input w-full py-6 min-h-[180px] leading-relaxed" 
                                  placeholder="e.g., Please sign and return...">{{ $settings->yearly_config['parent_instructions'] ?? "" }}</textarea>
                    </div>

                    <!-- Informational Box -->
                    <div class="p-8 bg-indigo-600 rounded-[2.5rem] text-white shadow-xl shadow-indigo-900/40 relative overflow-hidden group">
                        <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-white/10 rounded-full blur-3xl group-hover:scale-150 transition-transform duration-700"></div>
                        <div class="relative z-10 flex items-start gap-4">
                            <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <div>
                                <h4 class="font-black text-sm uppercase tracking-widest mb-2">Technical Insight</h4>
                                <p class="text-xs font-medium text-indigo-100/90 leading-relaxed">Yearly settings apply specifically to the Final Academic Certification report generated at the end of the academic year. Standard quarter/semester report cards use their own respective configuration logic.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Floating Save Bar -->
            <div class="fixed bottom-8 left-1/2 -translate-x-1/2 z-50 flex items-center gap-4 p-4 bg-white/80 backdrop-blur-2xl rounded-[2.5rem] border border-white shadow-2xl animate-in slide-in-from-bottom-12 duration-500">
                <button type="submit" class="px-12 py-4 bg-indigo-900 text-white font-black text-[10px] uppercase tracking-widest rounded-full hover:bg-slate-900 transition-all shadow-xl shadow-slate-900/20 flex items-center gap-2 group">
                    Synchronize Yearly Rules
                    <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </button>
            </div>
        </form>
    </div>
</x-admin-layout>
