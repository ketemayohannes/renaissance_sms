<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full relative z-10">
            <h2 class="text-2xl font-black text-slate-800 uppercase tracking-tight">System Sentinel</h2>
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-emerald-500 animate-pulse"></span>
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Live Monitoring Active</span>
            </div>
        </div>
    </x-slot>

    <div class="space-y-8 pb-12">
        <!-- Breadcrumb & Control -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 relative z-30">
            <x-breadcrumb :items="[['label' => 'System Audit Logs', 'url' => '#']]" />
            <div class="flex items-center gap-4">
                 <button class="px-4 py-2 bg-white border border-slate-200 rounded-xl text-[10px] font-black uppercase tracking-widest text-slate-500 hover:bg-slate-50 transition-all shadow-sm">
                    Export Incident Report
                 </button>
            </div>
        </div>

        <!-- Main Audit Matrix -->
        <div class="glass-panel overflow-hidden border-white/40 shadow-2xl">
            <div class="p-8 border-b border-slate-100 bg-slate-50/30 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest">Operational Feedback Loop</h3>
                    <p class="text-xs text-slate-500 font-medium mt-1 italic">Comprehensive history of all state changes within the environment.</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50 border-b border-slate-100">
                            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Temporal Stamp</th>
                            <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Operator Identity</th>
                            <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Classification</th>
                            <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Entity Target</th>
                            <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Data Mutations</th>
                            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-right">Infrastructure Meta</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($logs as $log)
                            <tr class="hover:bg-indigo-50/20 transition-all group">
                                <td class="px-8 py-6">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-bold text-slate-900 tracking-tight">{{ $log->created_at->format('H:i:s') }}</span>
                                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ $log->created_at->format('Y-M-d') }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-6">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center text-slate-500 font-black text-xs uppercase">
                                            {{ substr($log->user?->name ?? 'S', 0, 1) }}
                                        </div>
                                        <span class="text-sm font-bold text-slate-700">{{ $log->user?->name ?? 'System' }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-6">
                                    @php
                                        $badgeConfig = match($log->event) {
                                            'created' => ['bg-emerald-500', 'text-emerald-600', 'border-emerald-500/20'],
                                            'updated' => ['bg-indigo-500', 'text-indigo-600', 'border-indigo-500/20'],
                                            'deleted' => ['bg-rose-500', 'text-rose-600', 'border-rose-500/20'],
                                            default => ['bg-slate-500', 'text-slate-600', 'border-slate-500/20']
                                        };
                                    @endphp
                                    <span class="px-3 py-1 bg-{{ explode('-', $badgeConfig[0])[1] }}-500/10 {{ $badgeConfig[1] }} border {{ $badgeConfig[2] }} rounded-full text-[9px] font-black uppercase tracking-[0.15em]">
                                        {{ $log->event }}
                                    </span>
                                </td>
                                <td class="px-6 py-6">
                                    <div class="flex flex-col">
                                        <span class="text-xs font-black text-slate-800 uppercase tracking-tight">{{ class_basename($log->auditable_type) }}</span>
                                        <span class="text-[10px] font-bold text-slate-400 italic">UID-{{ $log->auditable_id }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-6 overflow-hidden">
                                    @if($log->event === 'updated')
                                        <div class="space-y-2 max-w-xs">
                                            @foreach($log->new_values as $key => $value)
                                                @if($key !== 'updated_at')
                                                    <div class="flex items-center gap-2 text-[10px] group/diff">
                                                        <span class="font-black text-slate-400 uppercase tracking-widest w-16 truncate" title="{{ $key }}">{{ $key }}:</span>
                                                        <span class="text-rose-400 line-through truncate max-w-[60px]">{{ $log->old_values[$key] ?? 'null' }}</span>
                                                        <svg class="w-3 h-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                                                        <span class="text-emerald-600 font-bold truncate max-w-[80px]">{{ $value }}</span>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    @elseif($log->event === 'created')
                                        <span class="text-[10px] font-black text-slate-300 uppercase tracking-[0.2em] italic">Genesis Initialization</span>
                                    @endif
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <div class="flex flex-col items-end opacity-40 group-hover:opacity-100 transition-opacity">
                                        <span class="text-[10px] font-black text-slate-900 tracking-widest">IP: {{ $log->ip_address }}</span>
                                        <span class="text-[9px] font-bold text-slate-400 truncate max-w-[120px]" title="{{ $log->user_agent }}">{{ $log->user_agent }}</span>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-8 border-t border-slate-50 bg-slate-50/20">
                {{ $logs->links() }}
            </div>
        </div>
    </div>
</x-admin-layout>
