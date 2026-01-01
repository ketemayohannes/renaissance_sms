<x-admin-layout>
    <div class="space-y-8 pb-12">
        <!-- Modern Header & Actions -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <x-breadcrumb :items="[
                    ['label' => 'Staff Management', 'url' => '#']
                ]" />
                <h1 class="text-4xl font-black text-slate-900 tracking-tight mt-2 flex items-center gap-3">
                    <span class="w-1.5 h-8 bg-indigo-600 rounded-full shadow-[0_0_15px_rgba(79,70,229,0.4)]"></span>
                    Human Resources
                </h1>
                <p class="text-slate-500 font-semibold mt-1 uppercase text-[10px] tracking-[0.3em] italic">Institutional personnel registry & role management设计</p>
            </div>
            
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.employees.import') }}" class="px-6 py-4 bg-white text-slate-600 font-black text-[10px] uppercase tracking-widest rounded-2xl hover:bg-slate-50 shadow-xl shadow-slate-200 transition-all flex items-center gap-2 active:scale-95 border border-slate-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    Bulk Import
                </a>
                <a href="{{ route('admin.employees.create') }}" class="px-8 py-4 bg-slate-900 text-white font-black text-[10px] uppercase tracking-widest rounded-2xl hover:bg-indigo-600 shadow-xl shadow-slate-200 transition-all flex items-center gap-3 active:scale-95 group">
                    <div class="w-8 h-8 rounded-xl bg-white/10 flex items-center justify-center group-hover:rotate-12 transition-transform">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                    </div>
                    Register Personnel
                </a>
            </div>
        </div>

        <!-- Analytical Context Layer -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="bg-gradient-to-br from-indigo-900 via-purple-900 to-indigo-950 rounded-[2.5rem] p-10 text-white shadow-2xl relative overflow-hidden group">
                <div class="absolute -top-20 -right-20 w-80 h-80 bg-white/5 rounded-full blur-3xl transition-transform group-hover:scale-110"></div>
                <div class="relative z-10 flex items-center gap-6">
                    <div class="w-20 h-20 bg-white/10 backdrop-blur-xl rounded-[1.8rem] flex items-center justify-center border border-white/10">
                        <svg class="w-10 h-10 text-indigo-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    </div>
                    <div>
                        <span class="text-[10px] font-black uppercase tracking-[0.4em] opacity-50 mb-1 block leading-none">Total Headcount</span>
                        <p class="text-5xl font-black italic tracking-tighter">{{ $stats['total'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] p-10 shadow-2xl border border-white relative overflow-hidden flex items-center gap-6">
                <div class="w-20 h-20 bg-emerald-50 rounded-[1.8rem] flex items-center justify-center border border-emerald-100 shadow-inner">
                    <svg class="w-10 h-10 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <span class="text-[10px] font-black uppercase tracking-[0.4em] text-slate-400 mb-1 block leading-none">Active Operations</span>
                    <p class="text-5xl font-black text-slate-900 tracking-tighter italic">{{ $stats['active'] }}</p>
                </div>
            </div>

            <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] p-10 shadow-2xl border border-white relative overflow-hidden flex items-center gap-6">
                <div class="w-20 h-20 bg-purple-50 rounded-[1.8rem] flex items-center justify-center border border-purple-100 shadow-inner">
                    <svg class="w-10 h-10 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                </div>
                <div>
                    <span class="text-[10px] font-black uppercase tracking-[0.4em] text-slate-400 mb-1 block leading-none">Faculty Members</span>
                    <p class="text-5xl font-black text-slate-900 tracking-tighter italic">{{ $stats['teachers'] }}</p>
                </div>
            </div>
        </div>

        <!-- Filter Control Terminal -->
        <div class="bg-white/40 backdrop-blur-md border border-white rounded-[2.5rem] p-10 shadow-xl overflow-hidden relative">
            <div class="absolute top-0 right-0 w-64 h-64 bg-slate-100/50 -mr-32 -mt-32 rounded-full blur-3xl"></div>
            <form action="{{ route('admin.employees.index') }}" method="GET" class="relative z-10 grid grid-cols-1 md:grid-cols-12 gap-6 items-end">
                <div class="md:col-span-4">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 block ml-1">Search Registry</label>
                    <div class="relative group">
                        <input type="text" name="search" value="{{ request('search') }}" 
                               placeholder="Name, ID or Designation..."
                               class="w-full bg-white border-slate-100 rounded-[1.4rem] py-4 px-6 focus:ring-indigo-600 focus:border-indigo-600 text-sm font-bold shadow-inner placeholder:text-slate-300 placeholder:italic transition-all group-hover:border-indigo-200">
                        <svg class="w-5 h-5 absolute right-5 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-indigo-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                </div>
                <div class="md:col-span-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 block ml-1">Designation</label>
                    <select name="designation" class="w-full bg-white border-slate-100 rounded-[1.4rem] py-4 px-6 focus:ring-indigo-600 focus:border-indigo-600 text-sm font-bold shadow-inner appearance-none transition-all">
                        <option value="">All Roles</option>
                        <option value="Teacher" {{ request('designation') == 'Teacher' ? 'selected' : '' }}>Teacher</option>
                        <option value="Administrator" {{ request('designation') == 'Administrator' ? 'selected' : '' }}>Administrator</option>
                        <option value="Accountant" {{ request('designation') == 'Accountant' ? 'selected' : '' }}>Accountant</option>
                        <option value="Principal" {{ request('designation') == 'Principal' ? 'selected' : '' }}>Principal</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 block ml-1">Category</label>
                    <select name="staff_category" class="w-full bg-white border-slate-100 rounded-[1.4rem] py-4 px-6 focus:ring-indigo-600 focus:border-indigo-600 text-sm font-bold shadow-inner appearance-none transition-all">
                        <option value="">All Categories</option>
                        <option value="academic" {{ request('staff_category') == 'academic' ? 'selected' : '' }}>Academic (Teaching)</option>
                        <option value="administrative" {{ request('staff_category') == 'administrative' ? 'selected' : '' }}>Administrative</option>
                        <option value="support" {{ request('staff_category') == 'support' ? 'selected' : '' }}>Support Staff</option>
                    </select>
                </div>
                <div class="md:col-span-1">
                    <button type="submit" class="w-full py-4 px-4 bg-slate-900 text-white rounded-[1.4rem] hover:bg-indigo-600 transition-all shadow-lg active:scale-95 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </button>
                </div>
                <div class="md:col-span-3">
                     @if(request()->anyFilled(['search', 'designation', 'staff_category']))
                        <a href="{{ route('admin.employees.index') }}" class="w-full py-4 bg-slate-100 text-slate-500 rounded-[1.4rem] hover:bg-rose-50 hover:text-rose-600 transition-all font-black text-[10px] uppercase tracking-widest flex items-center justify-center gap-2 border border-slate-200">
                             <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                             Purge Filters
                        </a>
                     @endif
                </div>
            </form>
        </div>

        <!-- Registry Data Matrix -->
        <div class="bg-white/80 backdrop-blur-xl border border-white rounded-[3rem] shadow-2xl overflow-hidden relative group">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-900">
                            <th class="px-10 py-8 text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Personnel ID & Identity</th>
                            <th class="px-8 py-8 text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Designation Meta</th>
                            <th class="px-8 py-8 text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Comm Channels</th>
                            <th class="px-10 py-8 text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] text-right">Operational Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100/50">
                        @forelse($employees as $employee)
                            <tr class="hover:bg-indigo-50/20 transition-all duration-300 group/row">
                                <td class="px-10 py-6">
                                    <div class="flex items-center gap-6">
                                        <div class="relative">
                                            <div class="w-16 h-16 rounded-[1.4rem] overflow-hidden bg-slate-100 ring-4 ring-white shadow-xl transition-all group-hover/row:scale-105 group-hover/row:rotate-3">
                                                @if($employee->photo)
                                                    <img src="{{ Storage::url($employee->photo) }}" class="w-full h-full object-cover">
                                                @else
                                                    <div class="w-full h-full flex items-center justify-center bg-indigo-50 text-indigo-600 font-black text-lg italic">
                                                        {{ substr($employee->first_name, 0, 1) }}
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="absolute -bottom-1 -right-1 w-5 h-5 rounded-full border-2 border-white {{ $employee->is_active ? 'bg-emerald-500' : 'bg-slate-300' }} shadow-md"></div>
                                        </div>
                                        <div>
                                            <p class="text-lg font-black text-slate-900 group-hover/row:text-indigo-600 transition-colors uppercase italic">{{ $employee->full_name }}</p>
                                            <div class="flex items-center gap-3 mt-1">
                                                <span class="text-xs font-black text-indigo-600/60 uppercase tracking-tighter">{{ $employee->employee_id }}</span>
                                                <span class="w-1 h-1 bg-slate-200 rounded-full"></span>
                                                <span class="text-[9px] font-black px-2 py-0.5 rounded-full bg-slate-100 text-slate-500 uppercase tracking-widest italic">
                                                    {{ str_replace('_', ' ', $employee->employment_type) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="space-y-1">
                                        <div class="flex items-center gap-2">
                                            <div class="w-2 h-8 bg-indigo-600/10 rounded-full overflow-hidden">
                                                <div class="w-full h-1/2 bg-indigo-600"></div>
                                            </div>
                                            <div>
                                                <p class="text-sm font-black text-slate-800 uppercase italic">{{ $employee->designation }}</p>
                                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.1em]">{{ $employee->department ?? 'General' }} Division</p>
                                            </div>
                                        </div>
                                        <div class="mt-2 flex items-center gap-2">
                                            <span class="px-2 py-0.5 bg-indigo-50 text-indigo-600 text-[8px] font-black uppercase tracking-widest rounded-md border border-indigo-100">
                                                {{ $employee->staff_category ?? 'STAFF' }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="space-y-1">
                                        <p class="text-[11px] font-black text-slate-700 flex items-center gap-2">
                                            <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                            {{ $employee->phone }}
                                        </p>
                                        <p class="text-[10px] font-bold text-slate-400 flex items-center gap-2 italic">
                                            <svg class="w-3.5 h-3.5 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                            {{ $employee->email }}
                                        </p>
                                    </div>
                                </td>
                                <td class="px-10 py-6 text-right">
                                    <div class="flex justify-end gap-3 translate-x-4 opacity-0 group-hover/row:translate-x-0 group-hover/row:opacity-100 transition-all duration-300">
                                        <a href="{{ route('admin.employees.show', $employee) }}" 
                                           class="w-12 h-12 bg-white text-indigo-600 rounded-2xl flex items-center justify-center hover:bg-slate-900 hover:text-white transition-all shadow-xl hover:-translate-y-1 active:scale-95 border border-slate-50"
                                           title="Deep Analytical View">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        </a>
                                        <a href="{{ route('admin.employees.edit', $employee) }}" 
                                           class="w-12 h-12 bg-white text-amber-600 rounded-2xl flex items-center justify-center hover:bg-slate-900 hover:text-white transition-all shadow-xl hover:-translate-y-1 active:scale-95 border border-slate-50"
                                           title="Modify Configuration">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-10 py-32 text-center">
                                    <div class="flex flex-col items-center">
                                         <div class="w-32 h-32 bg-slate-50 rounded-[3rem] flex items-center justify-center mb-8 shadow-inner">
                                             <svg class="w-16 h-16 text-slate-200 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                         </div>
                                         <h3 class="text-3xl font-black text-slate-900 tracking-tight italic uppercase">Personnel Database Null</h3>
                                         <p class="text-slate-400 font-bold uppercase text-[10px] tracking-[0.2em] mt-2">The institutional registry contains no matching records.</p>
                                         <div class="flex gap-4 mt-8">
                                            @if(request()->anyFilled(['search', 'designation']))
                                                <a href="{{ route('admin.employees.index') }}" class="px-8 py-4 bg-slate-900 text-white rounded-2xl font-black text-[10px] uppercase tracking-[0.2em] shadow-xl hover:bg-rose-600 transition-all">Reset Operations</a>
                                            @else
                                                <a href="{{ route('admin.employees.create') }}" class="px-8 py-4 bg-slate-900 text-white rounded-2xl font-black text-[10px] uppercase tracking-[0.2em] shadow-xl hover:bg-indigo-600 transition-all animate-bounce">Register First Entity</a>
                                            @endif
                                         </div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($employees->hasPages())
                <div class="px-10 py-8 bg-slate-50/50 border-t border-slate-100 flex justify-center">
                    {{ $employees->links() }}
                </div>
            @endif
        </div>
        
        <div class="mt-8 flex items-center justify-between px-10 text-[9px] font-black uppercase tracking-[0.3em] text-slate-400 italic">
            <div class="flex items-center gap-4">
                <span class="flex items-center gap-1.5">
                    <span class="w-1 h-1 bg-indigo-500 rounded-full"></span>
                    Engine: {{ number_format((microtime(true) - LARAVEL_START) * 1000, 2) }}ms
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-1 h-1 bg-slate-500 rounded-full"></span>
                    Queries: {{ $queryCount }}
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-1 h-1 bg-emerald-500 rounded-full"></span>
                    Memory: {{ number_format(memory_get_usage() / 1024 / 1024, 2) }}MB
                </span>
            </div>
            <div>Institutional HR Registry v4.2</div>
        </div>
    </div>
</x-admin-layout>
