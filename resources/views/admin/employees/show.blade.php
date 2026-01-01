<x-admin-layout>
    <div class="space-y-8 pb-12">
        <!-- Modern Header & Actions -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center gap-6">
                <a href="{{ route('admin.employees.index') }}" class="w-14 h-14 bg-white border border-slate-100 rounded-2xl flex items-center justify-center text-slate-400 hover:text-indigo-600 hover:bg-slate-50 transition-all shadow-xl active:scale-90">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 19l-7-7 7-7"></path></svg>
                </a>
                <div>
                    <x-breadcrumb :items="[
                        ['label' => 'Staff Management', 'url' => route('admin.employees.index')],
                        ['label' => 'Profile Intelligence', 'url' => '#']
                    ]" />
                    <h1 class="text-4xl font-black text-slate-900 tracking-tight mt-2 flex items-center gap-3">
                        <span class="w-1.5 h-8 bg-indigo-600 rounded-full shadow-[0_0_15px_rgba(79,70,229,0.4)]"></span>
                        Personnel Core
                    </h1>
                </div>
            </div>
            
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.employees.edit', $employee) }}" class="px-8 py-4 bg-white text-slate-600 font-black text-[10px] uppercase tracking-widest rounded-2xl hover:bg-slate-50 shadow-xl shadow-slate-200 transition-all flex items-center gap-2 active:scale-95 border border-slate-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    Modify Entry
                </a>
                <form action="{{ route('admin.employees.destroy', $employee) }}" method="POST" onsubmit="return confirm('DANGER: This will permanently purge the personnel record. Continue?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-8 py-4 bg-rose-50 text-rose-600 font-black text-[10px] uppercase tracking-widest rounded-2xl hover:bg-rose-600 hover:text-white shadow-xl shadow-rose-200 transition-all flex items-center gap-2 active:scale-95 border border-rose-100">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        Purge Entity
                    </button>
                </form>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <!-- Sidebar Aspect -->
            <div class="lg:col-span-4 space-y-8">
                <!-- Profile Identity Card -->
                <div class="bg-white/80 backdrop-blur-xl border border-white rounded-[3rem] shadow-2xl p-10 relative overflow-hidden group">
                    <div class="absolute -top-12 -right-12 w-40 h-40 bg-indigo-50/50 rounded-full blur-2xl group-hover:scale-150 transition-transform"></div>
                    
                    <div class="relative z-10 text-center">
                        <div class="inline-block relative">
                            <div class="w-48 h-48 rounded-[2.5rem] overflow-hidden bg-slate-100 mx-auto ring-8 ring-white shadow-2xl transition-all group-hover:scale-105 group-hover:rotate-3">
                                @if($employee->photo)
                                    <img src="{{ Storage::url($employee->photo) }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center bg-indigo-50 text-indigo-600 font-black text-5xl italic border-4 border-white">
                                        {{ substr($employee->first_name, 0, 1) }}
                                    </div>
                                @endif
                            </div>
                            <div class="absolute -bottom-2 -right-2 {{ $employee->is_active ? 'bg-emerald-500 shadow-[0_0_15px_rgba(16,185,129,0.5)]' : 'bg-slate-400' }} w-8 h-8 rounded-full border-4 border-white shadow-xl transition-transform group-hover:scale-110" title="Status: {{ $employee->is_active ? 'Operational' : 'Inactive' }}"></div>
                        </div>
                        
                        <h2 class="mt-8 text-3xl font-black text-slate-900 tracking-tight leading-none uppercase italic">{{ $employee->full_name }}</h2>
                        <p class="mt-3 text-indigo-600 font-black text-[10px] uppercase tracking-[0.3em] bg-indigo-50 px-6 py-2 rounded-full inline-block border border-indigo-100 italic">{{ $employee->designation }}</p>
                        
                        <div class="mt-8 grid grid-cols-2 gap-4">
                            <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100">
                                <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-1">Entity ID</span>
                                <span class="text-sm font-black text-slate-700 tracking-tighter">{{ $employee->employee_id }}</span>
                            </div>
                            <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100">
                                <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-1">Division</span>
                                <span class="text-sm font-black text-slate-700 tracking-tighter truncate">{{ $employee->staff_category ?? 'Staff' }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-10 pt-10 border-t border-slate-100 space-y-6">
                        <div class="flex items-center gap-4 group/item">
                            <div class="w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600 group-hover/item:bg-indigo-600 group-hover/item:text-white transition-all shadow-inner">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                            </div>
                            <div>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Electronic Mail</p>
                                <p class="text-sm font-black text-slate-700 truncate max-w-[180px]">{{ $employee->email }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4 group/item">
                            <div class="w-12 h-12 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-600 group-hover/item:bg-emerald-600 group-hover/item:text-white transition-all shadow-inner">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                            </div>
                            <div>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Direct Terminal</p>
                                <p class="text-sm font-black text-slate-700">{{ $employee->phone }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Strategic ID Panel -->
                <div class="bg-slate-900 rounded-[2.5rem] p-10 text-white shadow-2xl space-y-8 relative overflow-hidden">
                    <div class="absolute -bottom-8 -left-8 w-32 h-32 bg-white/5 rounded-full blur-xl"></div>
                    <div class="flex items-center gap-4 border-b border-white/10 pb-4">
                        <div class="w-1.5 h-6 bg-indigo-500 rounded-full"></div>
                        <h4 class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-400">Governance ID Portfolio</h4>
                    </div>
                    
                    <div class="grid grid-cols-1 gap-6">
                        <div class="bg-white/5 rounded-2xl p-6 border border-white/10 group hover:bg-white/10 transition-colors">
                            <span class="text-[9px] font-black text-slate-500 uppercase tracking-widest block mb-2 leading-none">Institutional National ID</span>
                            <span class="text-lg font-black tracking-tighter italic text-indigo-300">{{ $employee->national_id ?? 'SECRET' }}</span>
                        </div>
                        <div class="bg-white/5 rounded-2xl p-6 border border-white/10 group hover:bg-white/10 transition-colors">
                            <span class="text-[9px] font-black text-slate-500 uppercase tracking-widest block mb-2 leading-none">Tax Identification Node (TIN)</span>
                            <span class="text-lg font-black tracking-tighter italic text-emerald-300">{{ $employee->tin ?? 'NOT LINKED' }}</span>
                        </div>
                        <div class="bg-white/5 rounded-2xl p-6 border border-white/10 group hover:bg-white/10 transition-colors">
                            <span class="text-[9px] font-black text-slate-500 uppercase tracking-widest block mb-2 leading-none">Retirement Asset Pension</span>
                            <span class="text-lg font-black tracking-tighter italic text-amber-300">{{ $employee->pension_number ?? 'PENDING' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Intelligence Feed -->
            <div class="lg:col-span-8 space-y-8">
                <!-- Administrative Core Details -->
                <div class="bg-white/80 backdrop-blur-xl border border-white rounded-[3rem] shadow-2xl p-10">
                    <div class="flex items-center justify-between mb-10 border-b border-slate-50 pb-8">
                        <div class="flex items-center gap-4">
                            <div class="w-1.5 h-8 bg-slate-900 rounded-full"></div>
                            <div>
                                <h3 class="text-xl font-black text-slate-900 uppercase tracking-tight italic">Registry Specification</h3>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Personnel operational status and demographics</p>
                            </div>
                        </div>
                        <span class="px-6 py-2 {{ $employee->is_active ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600' }} text-[10px] font-black rounded-full uppercase tracking-widest border {{ $employee->is_active ? 'border-emerald-100' : 'border-rose-100' }} italic">
                           ENTITY STATE: {{ $employee->is_active ? 'OPERATIONAL' : 'DECOMMISSIONED' }}
                        </span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
                        <div class="group/stat">
                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-2 leading-none">Biological Sex</span>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-slate-50 flex items-center justify-center text-slate-400 group-hover/stat:bg-indigo-50 group-hover/stat:text-indigo-600 transition-colors shadow-inner">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                </div>
                                <p class="text-lg font-black text-slate-800 italic uppercase">{{ $employee->gender == 'M' ? 'Male' : 'Female' }}</p>
                            </div>
                        </div>
                        <div class="group/stat">
                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-2 leading-none">Temporal Origin (DOB)</span>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-slate-50 flex items-center justify-center text-slate-400 group-hover/stat:bg-indigo-50 group-hover/stat:text-indigo-600 transition-colors shadow-inner">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                </div>
                                <p class="text-lg font-black text-slate-800 italic uppercase border-b-2 border-indigo-600/10">{{ $employee->date_of_birth->format('M d, Y') }}</p>
                            </div>
                        </div>
                        <div class="group/stat">
                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-2 leading-none">Civil Stand</span>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-slate-50 flex items-center justify-center text-slate-400 group-hover/stat:bg-indigo-50 group-hover/stat:text-indigo-600 transition-colors shadow-inner">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                                </div>
                                <p class="text-lg font-black text-slate-800 italic uppercase capitalize">{{ $employee->marital_status ?? 'NOT DECLARED' }}</p>
                            </div>
                        </div>

                        <div class="md:col-span-3 py-6"><div class="w-full h-px bg-slate-50"></div></div>

                        <div class="group/stat">
                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-2 leading-none">Operational Division</span>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-slate-50 flex items-center justify-center text-slate-400 group-hover/stat:bg-indigo-50 group-hover/stat:text-indigo-600 transition-colors shadow-inner">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                </div>
                                <p class="text-lg font-black text-slate-800 italic uppercase">{{ $employee->department ?? 'General' }}</p>
                            </div>
                        </div>
                        <div class="group/stat">
                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-2 leading-none">Contract Format</span>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-slate-50 flex items-center justify-center text-slate-400 group-hover/stat:bg-indigo-50 group-hover/stat:text-indigo-600 transition-colors shadow-inner">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                </div>
                                <p class="text-lg font-black text-slate-800 italic uppercase capitalize">{{ str_replace('_', ' ', $employee->employment_type) }}</p>
                            </div>
                        </div>
                        <div class="group/stat">
                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-2 leading-none">Registry Initialization</span>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-slate-50 flex items-center justify-center text-slate-400 group-hover/stat:bg-indigo-50 group-hover/stat:text-indigo-600 transition-colors shadow-inner">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                                <p class="text-lg font-black text-slate-800 italic uppercase">{{ $employee->joining_date->format('M d, Y') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Geographic & Emergency Feed -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="bg-white/80 backdrop-blur-xl border border-white rounded-[3rem] shadow-2xl p-10 group overflow-hidden">
                        <div class="flex items-center gap-4 mb-8">
                            <div class="w-12 h-12 bg-emerald-50 rounded-2xl flex items-center justify-center text-emerald-600 shadow-inner group-hover:rotate-12 transition-transform">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            </div>
                            <h3 class="text-xl font-black text-slate-900 uppercase tracking-tight italic">Geospatial Data</h3>
                        </div>
                        <div class="space-y-6">
                            <div class="flex justify-between items-center bg-slate-50/50 p-4 rounded-2xl border border-slate-100">
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Region / State</p>
                                <p class="text-sm font-black text-slate-800 uppercase italic">{{ $employee->region ?? 'GLOBAL' }}</p>
                            </div>
                            <div class="flex justify-between items-center bg-slate-50/50 p-4 rounded-2xl border border-slate-100">
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Zone / District</p>
                                <p class="text-sm font-black text-slate-800 uppercase italic">{{ $employee->zone ?? 'CENTRAL' }}</p>
                            </div>
                            <div class="flex justify-between items-center bg-slate-50/50 p-4 rounded-2xl border border-slate-100">
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Woreda / Sector</p>
                                <p class="text-sm font-black text-slate-800 uppercase italic">{{ $employee->woreda ?? 'N/A' }}</p>
                            </div>
                            <div class="p-4 rounded-2xl bg-slate-900 text-white shadow-xl shadow-slate-200">
                                <p class="text-[9px] font-black text-slate-500 uppercase tracking-[0.2em] mb-2">Detailed Coordinate</p>
                                <p class="text-xs font-bold leading-relaxed opacity-80 italic">{{ $employee->address ?? 'Registry location strictly confidential.' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white/80 backdrop-blur-xl border border-white rounded-[3rem] shadow-2xl p-10 group overflow-hidden">
                        <div class="flex items-center gap-4 mb-8">
                            <div class="w-12 h-12 bg-rose-50 rounded-2xl flex items-center justify-center text-rose-600 shadow-inner group-hover:-rotate-12 transition-transform">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            </div>
                            <h3 class="text-xl font-black text-slate-900 uppercase tracking-tight italic">Emergency Protocol</h3>
                        </div>
                        <div class="space-y-6">
                            <div class="bg-rose-50/50 rounded-3xl p-8 border border-rose-100 text-center flex flex-col items-center justify-center h-full min-h-[220px]">
                                <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center text-rose-600 shadow-lg mb-6 ring-4 ring-rose-50">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                                </div>
                                <h4 class="text-[10px] font-black text-rose-400 uppercase tracking-[0.3em] mb-2 leading-none">Incident Response Lead</h4>
                                <p class="text-2xl font-black text-rose-900 tracking-tight italic uppercase">{{ $employee->emergency_contact_name ?? 'NOT REGISTERED' }}</p>
                                <p class="mt-4 px-6 py-2 bg-rose-600 text-white rounded-xl text-sm font-black shadow-lg shadow-rose-200 tracking-widest">{{ $employee->emergency_contact_phone ?? 'NO TERMINAL' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Asset & Financial Node -->
                <div class="bg-white/80 backdrop-blur-xl border border-white rounded-[3rem] shadow-2xl p-10 group overflow-hidden relative">
                    <div class="absolute top-0 right-0 p-8 opacity-5 transition-opacity group-hover:opacity-10">
                        <svg class="w-48 h-48 text-slate-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div class="flex items-center gap-4 mb-10 pb-8 border-b border-slate-50">
                        <div class="w-1.5 h-8 bg-amber-500 rounded-full"></div>
                        <h3 class="text-xl font-black text-slate-900 uppercase tracking-tight italic">Financial Metadata</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-10 relative z-10">
                        <div class="p-8 bg-slate-900 rounded-3xl text-white shadow-2xl shadow-slate-200">
                             <span class="text-[9px] font-black text-slate-500 uppercase tracking-[0.2em] block mb-2 leading-none">Operational Salary</span>
                             <p class="text-sm font-black opacity-60 italic mb-1 uppercase tracking-tighter">Net Institutional Allocation</p>
                             <p class="text-3xl font-black text-amber-400 italic">ETB {{ number_format($employee->basic_salary, 2) }}</p>
                        </div>
                        <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="bg-slate-50 p-6 rounded-2xl border border-slate-100 flex flex-col justify-center">
                                <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-1">Clearing Bank</span>
                                <p class="text-lg font-black text-slate-800 italic uppercase truncate">{{ $employee->bank_name ?? 'INTERNAL FUNDING' }}</p>
                            </div>
                            <div class="bg-slate-50 p-6 rounded-2xl border border-slate-100 flex flex-col justify-center">
                                <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-1">Asset Channel UID</span>
                                <p class="text-lg font-black text-slate-800 italic tracking-tighter">{{ $employee->account_number ?? 'SECRET ACCOUNT' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                @if($employee->staff_category === 'academic')
                <!-- Specialized Academic Node -->
                <div class="bg-gradient-to-br from-indigo-900 via-indigo-950 to-indigo-900 rounded-[3rem] p-12 text-white shadow-2xl relative overflow-hidden group">
                    <div class="absolute -top-20 -right-20 w-80 h-80 bg-white/5 rounded-full blur-3xl group-hover:scale-125 transition-transform duration-700"></div>
                    
                    <div class="flex items-center justify-between mb-12 relative z-10">
                        <div class="flex items-center gap-5">
                            <div class="w-16 h-16 bg-white/10 rounded-[1.5rem] flex items-center justify-center border border-white/10 group-hover:rotate-12 transition-all">
                                <svg class="w-8 h-8 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                            </div>
                            <div>
                                <h3 class="text-2xl font-black italic uppercase tracking-tight">Academic Competency</h3>
                                <p class="text-[10px] font-black text-indigo-400 uppercase tracking-[0.3em] font-medium mt-1">Faculty Qualification & Strategic Workload</p>
                            </div>
                        </div>
                        <a href="{{ route('admin.subject-assignments.index') }}" class="px-6 py-3 bg-white/10 hover:bg-white/20 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all backdrop-blur-xl border border-white/10 active:scale-95">
                            Sync Workload
                        </a>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-8 relative z-10">
                        <div class="bg-white/5 p-6 rounded-2xl border border-white/10 aspect-square flex flex-col items-center justify-center text-center">
                            <span class="text-[9px] font-black text-indigo-400 uppercase tracking-widest mb-2">Faculty Rank</span>
                            <p class="text-lg font-black italic uppercase leading-tight">{{ $employee->teacher_rank ?? 'PROBATION' }}</p>
                        </div>
                        <div class="bg-white/5 p-6 rounded-2xl border border-white/10 aspect-square flex flex-col items-center justify-center text-center">
                            <span class="text-[9px] font-black text-indigo-400 uppercase tracking-widest mb-2">Credential</span>
                            <p class="text-lg font-black italic uppercase leading-tight">{{ $employee->qualification_level ?? 'DIPLOMA' }}</p>
                        </div>
                        <div class="bg-white/5 p-6 rounded-2xl border border-white/10 aspect-square flex flex-col items-center justify-center text-center">
                            <span class="text-[9px] font-black text-indigo-400 uppercase tracking-widest mb-2">Weekly Load</span>
                            <p class="text-3xl font-black italic text-indigo-300 leading-none">{{ $employee->periods_per_week ?? '0' }}</p>
                            <span class="text-[8px] font-bold text-slate-500 uppercase mt-1">Periods</span>
                        </div>
                        <div class="bg-white/5 p-6 rounded-2xl border border-white/10 aspect-square flex flex-col items-center justify-center text-center overflow-hidden">
                            <span class="text-[9px] font-black text-indigo-400 uppercase tracking-widest mb-2">Domain Major</span>
                            <p class="text-xs font-black italic uppercase leading-relaxed text-indigo-100">{{ $employee->specialization ?? 'NOT DEFINED' }}</p>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</x-admin-layout>
