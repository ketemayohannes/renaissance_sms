<x-admin-layout>
    <x-slot name="header">Notice Board</x-slot>

    <div class="space-y-8 pb-12">
        {{-- Header --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <x-breadcrumb :items="[
                    ['label' => 'Communication', 'url' => '#'],
                    ['label' => 'Notice Board', 'url' => '#'],
                ]" />
                <h1 class="text-4xl font-black text-slate-900 tracking-tight mt-2 flex items-center gap-3">
                    <span class="w-1.5 h-8 bg-violet-600 rounded-full"></span>
                    Notice Board
                </h1>
                <p class="text-slate-500 font-semibold mt-1 uppercase text-[10px] tracking-[0.2em] italic">Manage all school-wide announcements</p>
            </div>
            <a href="{{ route('admin.notices.create') }}" class="px-8 py-4 bg-slate-900 text-white font-black text-[10px] uppercase tracking-widest rounded-[2rem] hover:bg-violet-600 shadow-xl shadow-slate-200 transition-all flex items-center gap-3 group">
                <div class="w-8 h-8 rounded-xl bg-white/10 flex items-center justify-center group-hover:bg-white/20 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                </div>
                Post Notice
            </a>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white/80 backdrop-blur-xl rounded-[2rem] p-6 border border-white shadow-xl shadow-slate-200/50">
                <span class="block text-[10px] font-black text-slate-400 uppercase tracking-widest">Total Notices</span>
                <span class="block text-3xl font-black text-slate-900 mt-2 italic tracking-tighter">{{ $notices->total() }}</span>
            </div>
            <div class="bg-violet-50/50 backdrop-blur-xl rounded-[2rem] p-6 border border-violet-100 shadow-xl shadow-violet-200/20">
                <span class="block text-[10px] font-black text-violet-500 uppercase tracking-widest">Active Now</span>
                <span class="block text-3xl font-black text-violet-700 mt-2 italic tracking-tighter">{{ $totalActive }}</span>
            </div>
            <div class="bg-indigo-50/50 backdrop-blur-xl rounded-[2rem] p-6 border border-indigo-100 shadow-xl shadow-indigo-200/20">
                <span class="block text-[10px] font-black text-indigo-500 uppercase tracking-widest">This Page</span>
                <span class="block text-3xl font-black text-indigo-900 mt-2 italic tracking-tighter">{{ $notices->count() }}</span>
            </div>
        </div>

        {{-- Filters --}}
        <div class="bg-white/40 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-xl shadow-slate-200/50 p-6">
            <form action="{{ route('admin.notices.index') }}" method="GET" class="flex flex-wrap items-center gap-4">
                <select name="audience" class="premium-select bg-white/60">
                    <option value="">All Audiences</option>
                    @foreach(['All','Parent','Teacher','Student'] as $aud)
                        <option value="{{ $aud }}" {{ request('audience') == $aud ? 'selected' : '' }}>{{ $aud }}</option>
                    @endforeach
                </select>
                <select name="status" class="premium-select bg-white/60">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
                <button type="submit" class="px-8 py-3 bg-indigo-600 text-white font-black text-[10px] uppercase tracking-widest rounded-2xl hover:bg-indigo-700 shadow-lg shadow-indigo-200 transition-all">Filter</button>
                @if(request()->anyFilled(['audience','status']))
                    <a href="{{ route('admin.notices.index') }}" class="text-[10px] font-black text-slate-400 uppercase tracking-widest hover:text-rose-500 transition-colors">Clear</a>
                @endif
            </form>
        </div>

        {{-- Table --}}
        <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-2xl shadow-slate-200/50 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-separate border-spacing-0">
                    <thead>
                        <tr class="bg-slate-50/50">
                            <th class="px-8 py-4 border-b border-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Title</th>
                            <th class="px-6 py-4 border-b border-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Audience</th>
                            <th class="px-6 py-4 border-b border-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Published</th>
                            <th class="px-6 py-4 border-b border-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Expires</th>
                            <th class="px-6 py-4 border-b border-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-center">Status</th>
                            <th class="px-8 py-4 border-b border-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($notices as $notice)
                        <tr class="group hover:bg-slate-50/50 transition-colors">
                            <td class="px-8 py-5">
                                <span class="block text-sm font-black text-slate-900">{{ $notice->title }}</span>
                                <span class="block text-[10px] font-bold text-slate-400 mt-0.5">{{ Str::limit(strip_tags($notice->content), 60) }}</span>
                            </td>
                            <td class="px-6 py-5">
                                <span class="px-3 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-widest {{ \App\Models\Notice::audienceColor($notice->target_audience) }}">
                                    {{ $notice->target_audience }}
                                </span>
                            </td>
                            <td class="px-6 py-5">
                                <span class="text-sm font-bold text-slate-700">{{ $notice->publish_date->format('M d, Y') }}</span>
                            </td>
                            <td class="px-6 py-5">
                                <span class="text-sm font-bold text-slate-500">{{ $notice->expiry_date?->format('M d, Y') ?? '—' }}</span>
                            </td>
                            <td class="px-6 py-5 text-center">
                                @if($notice->is_active)
                                    <span class="px-3 py-1.5 bg-emerald-50 text-emerald-600 rounded-xl text-[10px] font-black uppercase tracking-widest">Active</span>
                                @else
                                    <span class="px-3 py-1.5 bg-slate-100 text-slate-400 rounded-xl text-[10px] font-black uppercase tracking-widest">Inactive</span>
                                @endif
                            </td>
                            <td class="px-8 py-5 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.notices.edit', $notice) }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-slate-200 text-slate-600 font-black text-[10px] uppercase tracking-widest rounded-xl hover:bg-indigo-50 hover:text-indigo-600 hover:border-indigo-100 transition-all shadow-sm">
                                        Edit
                                    </a>
                                    <form action="{{ route('admin.notices.destroy', $notice) }}" method="POST" onsubmit="return confirm('Delete this notice?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 bg-rose-50 border border-rose-100 text-rose-500 font-black text-[10px] uppercase tracking-widest rounded-xl hover:bg-rose-100 transition-all shadow-sm">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-12 py-24 text-center">
                                <div class="w-24 h-24 bg-slate-50 rounded-[2.5rem] flex items-center justify-center text-slate-200 mx-auto mb-6">
                                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                </div>
                                <h3 class="text-2xl font-black text-slate-900 tracking-tight italic">No notices posted</h3>
                                <p class="text-slate-500 font-semibold mt-2 text-sm">Post your first school announcement to get started.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($notices->hasPages())
                <div class="px-8 py-6 bg-slate-50/40 border-t border-slate-100">
                    {{ $notices->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
</x-admin-layout>
