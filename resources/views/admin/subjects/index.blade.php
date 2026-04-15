<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full relative z-10">
            <h2 class="text-2xl font-black text-slate-800 uppercase tracking-tight">Curriculum Master</h2>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.subjects.reorder') }}" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-600 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-50 transition-all shadow-sm">
                    Reorder Subjects
                </a>
                <a href="{{ route('admin.subjects.create') }}" class="vibrant-btn-blue">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Add Academic Subject
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-8">
        <!-- Breadcrumb & Import -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 relative z-30">
            <x-breadcrumb :items="[['label' => 'Subjects', 'url' => '#']]" />
            <a href="{{ route('admin.subjects.import') }}" class="flex items-center gap-2 text-indigo-600 hover:text-indigo-800 font-black text-[10px] uppercase tracking-[0.2em] transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                Bulk Import Portal
            </a>
        </div>

        <!-- Import Errors -->
        @if(session('import_errors'))
            <div class="glass-panel p-6 border-l-4 border-amber-500 bg-amber-50/50 animate-headshake">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-amber-500/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                    <h3 class="font-black text-amber-900 uppercase tracking-widest text-xs">Import Conflict Detected</h3>
                </div>
                <ul class="space-y-2 max-h-40 overflow-y-auto custom-scrollbar pr-2">
                    @foreach(session('import_errors') as $error)
                        <li class="text-sm font-medium text-amber-800 flex items-center gap-2">
                            <span class="w-1 h-1 rounded-full bg-amber-400"></span>
                            {{ $error }}
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Subjects List -->
        <div class="glass-panel overflow-hidden border-white/40 shadow-2xl">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50 border-b border-slate-100">
                            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Rank</th>
                            <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Subject Identity</th>
                            <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Curriculum Code</th>
                            <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Catalog Description</th>
                            <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Status</th>
                            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-right">Operations</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($subjects as $subject)
                            <tr class="hover:bg-indigo-50/30 transition-all group">
                                <td class="px-8 py-6 text-slate-400 font-bold text-xs">{{ $subject->sort_order }}</td>
                                <td class="px-6 py-6 font-bold text-slate-900 tracking-tight">{{ $subject->name }}</td>
                                <td class="px-6 py-6">
                                    <span class="px-3 py-1 bg-slate-100 text-slate-600 rounded-lg text-xs font-black uppercase tracking-widest group-hover:bg-white transition-colors">{{ $subject->code }}</span>
                                </td>
                                <td class="px-6 py-6 text-slate-500 text-sm max-w-xs truncate" title="{{ $subject->description }}">
                                    {{ $subject->description ?? '-' }}
                                </td>
                                <td class="px-6 py-6">
                                    @if($subject->is_active)
                                        <span class="badge-success px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest bg-emerald-500/10 text-emerald-600 border border-emerald-500/20">Active</span>
                                    @else
                                        <span class="badge-danger px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest bg-rose-500/10 text-rose-600 border border-rose-500/20">Inactive</span>
                                    @endif
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <div class="flex items-center justify-end gap-3 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <a href="{{ route('admin.subjects.edit', $subject) }}" class="p-2 bg-white border border-slate-200 rounded-lg text-indigo-600 hover:bg-indigo-600 hover:text-white hover:border-indigo-600 transition-all shadow-sm">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </a>
                                        <form action="{{ route('admin.subjects.destroy', $subject) }}" method="POST" class="inline-block delete-form" data-confirm-message="Are you sure you want to delete this subject?">
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
                                <td colspan="6" class="px-8 py-12 text-center text-slate-500 text-sm font-medium italic">No subjects registered in system.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-admin-layout>
