<x-admin-layout>
    <x-slot name="header">Export Logistics ✨</x-slot>

    <div class="space-y-8 pb-12">
        <!-- Header & Navigation -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <x-breadcrumb :items="[
                    ['label' => 'Academic Reports', 'url' => route('admin.academic-reports.index')],
                    ['label' => 'Export Background Monitor', 'url' => '#']
                ]" />
                <h1 class="text-4xl font-black text-slate-900 tracking-tight mt-2 flex items-center gap-3">
                    <span class="w-1.5 h-8 bg-indigo-600 rounded-full"></span>
                    Export Logistics
                </h1>
                <p class="text-slate-500 font-semibold mt-1 uppercase text-[10px] tracking-[0.2em] italic">Asynchronous generation pipeline monitor</p>
            </div>
            
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.section-grades.index') }}" class="px-6 py-4 bg-white border border-slate-200 rounded-2xl font-black text-[10px] uppercase tracking-widest text-slate-600 hover:bg-slate-50 hover:border-slate-300 transition-all flex items-center gap-3 group shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Report Card Selection
                </a>
            </div>
        </div>

        <!-- Export Traffic Panel -->
        <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-2xl shadow-slate-200/50 overflow-hidden relative">
            <div class="px-8 py-6 border-b border-slate-100 flex items-center justify-between bg-white/40">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-600 flex items-center justify-center shadow-inner">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-black text-slate-900 uppercase tracking-tight">Active Infrastructure Queues</h2>
                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-[0.2em] mt-0.5">Real-time status of background synthesis batches</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-3 py-1 bg-slate-100 rounded-full text-[9px] font-black text-slate-400 uppercase tracking-widest">{{ $exports->total() }} Total Batches</span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-separate border-spacing-0">
                    <thead>
                        <tr class="bg-slate-50/50">
                            <th class="px-8 py-4 border-b border-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Initiated</th>
                            <th class="px-6 py-4 border-b border-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Details</th>
                            <th class="px-6 py-4 border-b border-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Export Status</th>
                            <th class="px-6 py-4 border-b border-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Finalized At</th>
                            <th class="px-8 py-4 border-b border-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($exports as $export)
                        <tr class="group hover:bg-slate-50/50 transition-colors">
                            <td class="px-8 py-6">
                                <span class="block text-sm font-bold text-slate-900 leading-none">{{ $export->created_at->format('M d, Y') }}</span>
                                <span class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1.5">{{ $export->created_at->format('H:i') }} (UTC)</span>
                            </td>
                            <td class="px-6 py-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253"></path></svg>
                                    </div>
                                    <div>
                                        <span class="block text-xs font-black text-slate-700 uppercase tracking-tight">{{ $export->params['section_name'] ?? 'Undefined Unit' }}</span>
                                        <span class="block text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">{{ $export->params['term_name'] ?? 'N/A' }} Full Certification ZIP</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-6 text-center">
                                @if($export->status === 'pending')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 text-slate-500 rounded-lg text-[10px] font-black uppercase tracking-widest">
                                        <div class="w-1.5 h-1.5 rounded-full bg-slate-300"></div>
                                        Queued
                                    </span>
                                @elseif($export->status === 'processing')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-100 text-indigo-600 rounded-lg text-[10px] font-black uppercase tracking-widest animate-pulse">
                                        <div class="w-1.5 h-1.5 rounded-full bg-indigo-600 animate-ping"></div>
                                        Synthesis...
                                    </span>
                                @elseif($export->status === 'completed')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-100 text-emerald-600 rounded-lg text-[10px] font-black uppercase tracking-widest">
                                        <div class="w-1.5 h-1.5 rounded-full bg-emerald-600"></div>
                                        Ready for Dist
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-rose-100 text-rose-600 rounded-lg text-[10px] font-black uppercase tracking-widest group/error relative cursor-help" title="{{ $export->error_message }}">
                                        <div class="w-1.5 h-1.5 rounded-full bg-rose-600"></div>
                                        Critical Failure
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-6">
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ $export->completed_at ? $export->completed_at->format('H:i:s') : 'Waiting...' }}</span>
                            </td>
                            <td class="px-8 py-6 text-right">
                                @if($export->status === 'completed')
                                    <a href="{{ route('admin.report-cards.download-export', $export) }}" class="inline-flex items-center gap-2 px-6 py-2.5 bg-indigo-900 text-white font-black text-[10px] uppercase tracking-widest rounded-xl hover:bg-slate-900 shadow-lg shadow-slate-200 transition-all group/btn">
                                        Download ZIP
                                        <svg class="w-3 h-3 group-hover/btn:translate-y-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                    </a>
                                @elseif($export->status === 'failed')
                                    <span class="text-[10px] font-black text-rose-400 uppercase tracking-widest italic">Review Logs</span>
                                @else
                                    <div class="flex items-center justify-end gap-2 text-[10px] font-black text-slate-300 uppercase tracking-widest">
                                        <svg class="w-3 h-3 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                        In Queue
                                    </div>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-12 py-20 text-center">
                                <div class="w-20 h-20 bg-slate-50 rounded-[2rem] flex items-center justify-center text-slate-300 mx-auto mb-6">
                                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                                </div>
                                <h3 class="text-xl font-black text-slate-900 tracking-tight">No Export Requests</h3>
                                <p class="text-slate-500 font-semibold mt-1 text-sm italic">Start a bulk report card export from the section management page.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($exports->hasPages())
                <div class="px-8 py-6 bg-slate-50/50 border-t border-slate-100">
                    {{ $exports->links() }}
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
    @if($exports->whereIn('status', ['pending', 'processing'])->count() > 0)
    <script>
        // Smooth auto-refresh for active infrastructure synthesis
        setTimeout(function() {
            window.location.reload();
        }, 5000);
    </script>
    @endif
    @endpush
</x-admin-layout>
