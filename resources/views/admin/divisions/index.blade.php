<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full relative z-10">
            <h2 class="text-2xl font-black text-slate-800 uppercase tracking-tight">Division Control</h2>
            <a href="{{ route('admin.divisions.create') }}" class="vibrant-btn-blue">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Deploy New Division
            </a>
        </div>
    </x-slot>

    <div class="space-y-8">
        <!-- Breadcrumb & Navigation -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 relative z-30">
            <x-breadcrumb :items="[['label' => 'Divisions', 'url' => '#']]" />
            <div class="glass-panel p-1 flex bg-slate-100/50 rounded-2xl">
                @include('admin.layouts.partials.school-structure-tabs')
            </div>
        </div>

        <!-- System Alerts -->
        @if(session('error'))
            <div class="glass-panel p-4 border-l-4 border-rose-500 bg-rose-50/50 flex items-center gap-3 animate-headshake">
                <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <p class="text-sm font-bold text-rose-800 tracking-tight">{{ session('error') }}</p>
            </div>
        @endif

        <!-- Main Data Table -->
        <div class="glass-panel overflow-hidden border-white/40 shadow-2xl">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50 border-b border-slate-100">
                            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Division Identity</th>
                            <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Designation Code</th>
                            <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Grade Matrix</th>
                            <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Sequence</th>
                            <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Lifecycle</th>
                            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-right">Operational Tools</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($divisions as $division)
                            <tr class="hover:bg-indigo-50/30 transition-all group">
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-xl bg-indigo-500/10 flex items-center justify-center text-indigo-600 font-black text-sm group-hover:scale-110 transition-transform">
                                            {{ substr($division->name, 0, 1) }}
                                        </div>
                                        <span class="font-bold text-slate-900 tracking-tight">{{ $division->name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-6">
                                    <span class="px-3 py-1 bg-slate-100 text-slate-600 rounded-lg text-xs font-black uppercase tracking-widest">{{ $division->code }}</span>
                                </td>
                                <td class="px-6 py-6 font-semibold text-slate-600 text-sm">
                                    {{ $division->grade_levels_count }} Levels
                                </td>
                                <td class="px-6 py-6 text-slate-400 font-bold text-xs">{{ $division->sort_order }}</td>
                                <td class="px-6 py-6">
                                    @if($division->is_active)
                                        <span class="badge-success px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest bg-emerald-500/10 text-emerald-600 border border-emerald-500/20">Active</span>
                                    @else
                                        <span class="badge-danger px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest bg-rose-500/10 text-rose-600 border border-rose-500/20">Inactive</span>
                                    @endif
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <div class="flex items-center justify-end gap-3 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <a href="{{ route('admin.divisions.edit', $division) }}" class="p-2 bg-white border border-slate-200 rounded-lg text-indigo-600 hover:bg-indigo-600 hover:text-white hover:border-indigo-600 transition-all shadow-sm">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </a>
                                        <form action="{{ route('admin.divisions.destroy', $division) }}" method="POST" class="inline-block delete-form" data-confirm-message="Are you sure you want to delete this division?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 bg-white border border-slate-200 rounded-lg text-rose-600 hover:bg-rose-600 hover:text-white hover:border-rose-600 transition-all shadow-sm">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-8 py-12 text-center">
                                    <div class="flex flex-col items-center gap-4 opacity-40">
                                        <svg class="w-16 h-16 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                                        <p class="text-sm font-black uppercase tracking-widest text-slate-500 italic">Static Environment: No Divisions Found</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-admin-layout>
