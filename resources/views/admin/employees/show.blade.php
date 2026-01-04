<x-admin-layout>
    <x-slot name="header">Staff Profile: {{ $employee->full_name }}</x-slot>

    <div class="space-y-6" x-data="{ tab: 'overview' }">
        <!-- Breadcrumb -->
        <x-breadcrumb :items="[
            ['label' => 'Staff Management', 'url' => route('admin.employees.index')],
            ['label' => 'Staff Profile', 'url' => '#']
        ]" />

        <!-- Profile Header Section -->
        <div class="relative mb-12 z-[50]">
            <!-- Background Banner Decorative Element -->
            <div class="absolute inset-0 h-48 bg-gradient-to-r from-indigo-600 to-violet-600 rounded-[3rem] opacity-10 blur-3xl -z-10"></div>
            
            <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-xl shadow-slate-200/50 p-8">
                <div class="flex flex-col lg:flex-row gap-8 items-start">
                    <!-- Profile Image & Status -->
                    <div class="relative group">
                        <div class="w-40 h-40 rounded-[2.5rem] bg-slate-100 border-4 border-white shadow-xl overflow-hidden">
                            @if($employee->photo)
                                <img src="{{ Storage::url($employee->photo) }}" alt="Staff Photo" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-slate-50 to-slate-100 text-slate-300">
                                    <svg class="w-20 h-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                </div>
                            @endif
                        </div>
                        <div class="absolute -bottom-3 -right-3 px-4 py-1.5 rounded-2xl {{ $employee->is_active ? 'bg-emerald-500 shadow-emerald-200' : 'bg-rose-400 shadow-rose-200' }} text-white text-[10px] font-black uppercase tracking-widest shadow-lg">
                            {{ $employee->is_active ? 'Active' : 'Inactive' }}
                        </div>
                    </div>

                    <!-- Info and Actions -->
                    <div class="flex-grow">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 relative z-[60]">
                            <div>
                                <h1 class="text-4xl font-black text-slate-900 tracking-tight mb-2">{{ $employee->full_name }}</h1>
                                <div class="flex flex-wrap items-center gap-4 text-slate-500">
                                    <span class="bg-slate-100 px-3 py-1 rounded-lg font-black text-slate-700 text-xs tracking-wider uppercase">{{ $employee->employee_id }}</span>
                                    <span class="flex items-center gap-1.5 font-semibold text-sm">
                                        <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        Joined {{ $employee->joining_date->format('M d, Y') }}
                                    </span>
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($employee->user->roles as $role)
                                            <span class="px-2 py-0.5 bg-indigo-50 text-indigo-600 text-[9px] font-bold rounded-lg uppercase tracking-widest border border-indigo-100">
                                                {{ $role->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-3">
                                <a href="{{ route('admin.employees.edit', $employee) }}" class="px-6 py-3 bg-indigo-600 text-white font-black text-[10px] uppercase tracking-widest rounded-xl hover:bg-white hover:text-indigo-600 hover:ring-2 hover:ring-indigo-600 transition-all shadow-lg shadow-indigo-100 flex items-center gap-2 group">
                                    <svg class="w-4 h-4 group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    Edit Profile
                                </a>
                                
                                <div class="relative z-50" x-data="{ open: false }">
                                    <button @click.stop="open = !open" class="px-6 py-3 bg-white text-slate-700 font-black text-[10px] uppercase tracking-widest rounded-xl hover:bg-slate-50 transition-all border border-slate-200 shadow-sm flex items-center gap-2">
                                        Quick Actions
                                        <svg class="w-4 h-4 transition-transform" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    </button>
                                    <div x-cloak x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-3 w-64 bg-white border border-slate-100 rounded-[2rem] shadow-2xl p-3 z-[100]">
                                        <div class="px-3 py-2 text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Account Management</div>
                                        
                                        <form action="{{ route('admin.employees.reset-password', $employee) }}" method="POST" onsubmit="return confirm('Reset password for this staff member?')">
                                            @csrf
                                            <button type="submit" class="w-full flex items-center gap-3 px-4 py-2.5 rounded-xl hover:bg-slate-50 text-slate-700 transition-all font-semibold text-xs group/item text-left">
                                                <svg class="w-4 h-4 text-amber-500 group-hover/item:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                                                Reset Password
                                            </button>
                                        </form>

                                        <form action="{{ route('admin.employees.toggle-status', $employee) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="w-full flex items-center gap-3 px-4 py-2.5 rounded-xl hover:bg-slate-50 {{ $employee->is_active ? 'text-rose-600' : 'text-emerald-600' }} transition-all font-semibold text-xs text-left">
                                                @if($employee->is_active)
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                                                    Deactivate Staff
                                                @else
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                    Activate Staff
                                                @endif
                                            </button>
                                        </form>

                                        <div class="my-2 border-t border-slate-100/50 mx-2"></div>
                                        
                                        <form action="{{ route('admin.employees.destroy', $employee) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this staff record?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="w-full flex items-center gap-3 px-4 py-2.5 rounded-xl hover:bg-rose-50 text-rose-600 transition-all font-semibold text-xs text-left">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                Delete Record
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mt-8">
                            <div class="bg-slate-50/50 p-4 rounded-[1.5rem] border border-slate-100 shadow-sm">
                                <span class="text-[10px] text-slate-400 font-black uppercase tracking-widest block mb-1">Gender</span>
                                <span class="font-bold text-slate-900">{{ $employee->gender == 'M' ? 'Male' : 'Female' }}</span>
                            </div>
                            <div class="bg-indigo-50/30 p-4 rounded-[1.5rem] border border-indigo-100/30 shadow-sm">
                                <span class="text-[10px] text-indigo-400 font-black uppercase tracking-widest block mb-1">Category</span>
                                <span class="font-bold text-slate-900 capitalize">{{ $employee->user->roles->first()->category ?? ($employee->staff_category ?? 'N/A') }}</span>
                            </div>
                            <div class="bg-amber-50/30 p-4 rounded-[1.5rem] border border-amber-100/30 shadow-sm">
                                <span class="text-[10px] text-amber-400 font-black uppercase tracking-widest block mb-1">Department</span>
                                <span class="font-bold text-slate-900 capitalize">{{ $employee->department ?? 'General' }}</span>
                            </div>
                            <div class="bg-emerald-50/30 p-4 rounded-[1.5rem] border border-emerald-100/30 shadow-sm">
                                <span class="text-[10px] text-emerald-400 font-black uppercase tracking-widest block mb-1">Birth Date</span>
                                <span class="font-bold text-slate-900">{{ $employee->date_of_birth->format('M d, Y') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab Navigation -->
        <div class="sticky top-4 z-40 bg-white/60 backdrop-blur-xl border border-white rounded-[2rem] shadow-xl shadow-slate-200/50 p-2 overflow-x-auto no-scrollbar">
            <nav class="flex gap-1 min-w-max">
                <button @click="tab = 'overview'" 
                        :class="tab === 'overview' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-100' : 'text-slate-500 hover:bg-slate-50'"
                        class="px-6 py-3 rounded-2xl font-black text-[10px] uppercase tracking-widest transition-all duration-300 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    Overview
                </button>
                <button @click="tab = 'work'" 
                        :class="tab === 'work' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-100' : 'text-slate-500 hover:bg-slate-50'"
                        class="px-6 py-3 rounded-2xl font-black text-[10px] uppercase tracking-widest transition-all duration-300 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                    Work Details
                </button>
                <button @click="tab = 'financials'" 
                        :class="tab === 'financials' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-100' : 'text-slate-500 hover:bg-slate-50'"
                        class="px-6 py-3 rounded-2xl font-black text-[10px] uppercase tracking-widest transition-all duration-300 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Financials
                </button>
                <button @click="tab = 'documents'" 
                        :class="tab === 'documents' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-100' : 'text-slate-500 hover:bg-slate-50'"
                        class="px-6 py-3 rounded-2xl font-black text-[10px] uppercase tracking-widest transition-all duration-300 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Documents
                </button>
            </nav>
        </div>

        <!-- OVERVIEW TAB -->
        <div x-show="tab === 'overview'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Personal & Residence Info card -->
                <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-xl shadow-slate-200/50 p-8">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600 shadow-sm border border-indigo-100/50">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-slate-900">Personal & Residence</h3>
                            <p class="text-slate-500 text-sm">Vital demographic and address records.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-6 gap-x-8">
                        <div class="sm:col-span-2">
                            <span class="text-[10px] text-slate-400 font-black uppercase tracking-widest block mb-1">Full Name</span>
                            <span class="font-bold text-slate-700 text-lg">{{ $employee->full_name }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-400 font-black uppercase tracking-widest block mb-1">Date of Birth</span>
                            <span class="font-bold text-slate-700">{{ $employee->date_of_birth->format('M d, Y') }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-400 font-black uppercase tracking-widest block mb-1">Marital Status</span>
                            <span class="font-bold text-slate-700 capitalize">{{ $employee->marital_status ?? 'Not declared' }}</span>
                        </div>
                        <div class="sm:col-span-2 pt-4 border-t border-slate-50">
                            <span class="text-[10px] text-slate-400 font-black uppercase tracking-widest block mb-1">Current Address</span>
                            <span class="font-bold text-slate-700 leading-relaxed">{{ $employee->address ?? 'No address registered.' }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-400 font-black uppercase tracking-widest block mb-1">Subcity / Woreda</span>
                            <span class="font-bold text-slate-700">{{ $employee->region ?? '-' }} / {{ $employee->woreda ?? '-' }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-indigo-400 font-black uppercase tracking-widest block mb-1">Personal Phone</span>
                            <span class="font-black text-indigo-600 text-lg">{{ $employee->phone }}</span>
                        </div>
                    </div>
                </div>

                <div class="space-y-8">
                    <!-- Identification records -->
                    <div class="bg-slate-900 rounded-[2.5rem] p-8 text-white shadow-xl shadow-indigo-900/10">
                        <h4 class="text-[10px] font-bold text-slate-500 uppercase tracking-[0.2em] mb-6 flex items-center gap-2">
                            <span class="w-1.5 h-4 bg-indigo-500 rounded-full"></span>
                            Govt Identification Records
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="bg-white/5 p-4 rounded-2xl border border-white/10">
                                <span class="text-[9px] font-bold text-slate-500 uppercase tracking-widest block mb-1">National ID</span>
                                <span class="text-sm font-bold text-indigo-300 italic">{{ $employee->national_id ?? 'N/A' }}</span>
                            </div>
                            <div class="bg-white/5 p-4 rounded-2xl border border-white/10">
                                <span class="text-[9px] font-bold text-slate-500 uppercase tracking-widest block mb-1">TIN Number</span>
                                <span class="text-sm font-bold text-emerald-300 italic">{{ $employee->tin ?? 'N/A' }}</span>
                            </div>
                            <div class="bg-white/5 p-4 rounded-2xl border border-white/10">
                                <span class="text-[9px] font-bold text-slate-500 uppercase tracking-widest block mb-1">Pension No.</span>
                                <span class="text-sm font-bold text-amber-300 italic">{{ $employee->pension_number ?? 'N/A' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Portal Account Management -->
                    <div class="bg-indigo-600 rounded-[2.5rem] shadow-xl shadow-indigo-100 p-8 text-white relative overflow-hidden group">
                        <div class="absolute top-0 right-0 -m-12 w-48 h-48 bg-white/10 rounded-full blur-3xl group-hover:scale-150 transition-transform duration-700"></div>
                        
                        <h3 class="text-xl font-bold mb-4 flex items-center gap-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A10.003 10.003 0 0014 11V4h-3L9 7l-2-3H4v7a10 10 0 005.183 8.761"></path></svg>
                            System Account Access
                        </h3>
                        
                        @if($employee->user_id)
                            <div class="flex items-center justify-between gap-4">
                                <div class="flex-grow">
                                    <p class="text-indigo-100 text-[10px] font-black uppercase tracking-widest mb-1 opacity-70">Login Username</p>
                                    <p class="font-bold text-lg leading-tight break-all uppercase tracking-tight">{{ $employee->user->email }}</p>
                                </div>
                                <div class="flex-grow">
                                    <p class="text-indigo-100 text-[10px] font-black uppercase tracking-widest mb-1 opacity-70">Status</p>
                                    <p class="font-bold text-lg leading-tight">{{ $employee->is_active ? 'Active' : 'Locked' }}</p>
                                </div>
                                @if($employee->user->temp_password)
                                <div class="flex-grow">
                                    <p class="text-indigo-100 text-[10px] font-black uppercase tracking-widest mb-1 opacity-70">Temp Pass</p>
                                    <p class="font-black text-lg leading-tight tracking-wider">{{ $employee->user->temp_password }}</p>
                                </div>
                                @endif
                            </div>
                        @else
                            <div class="flex items-center justify-between gap-4">
                                <p class="text-indigo-100 text-sm font-medium opacity-80 italic">No linked system account found for this staff record.</p>
                                <a href="{{ route('admin.employees.edit', $employee) }}" class="px-6 py-3 bg-white text-indigo-600 font-black text-[10px] uppercase tracking-widest rounded-xl hover:shadow-lg transition-all text-center">
                                    Link via Edit
                                </a>
                            </div>
                        @endif
                    </div>

                    <!-- Emergency Contact -->
                    <div class="bg-white/80 backdrop-blur-xl border border-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 p-8">
                        <div class="flex items-center gap-4 mb-6">
                            <div class="w-10 h-10 bg-rose-50 rounded-xl flex items-center justify-center text-rose-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            </div>
                            <h3 class="font-bold text-slate-900">Emergency Contact</h3>
                        </div>
                        <div class="flex items-center justify-between bg-rose-50/30 rounded-2xl p-6 border border-rose-100">
                            <div>
                                <p class="text-[10px] font-bold text-rose-400 uppercase tracking-widest mb-1">Contact Person</p>
                                <p class="text-xl font-bold text-rose-900">{{ $employee->emergency_contact_name ?? 'Not Registered' }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-[10px] font-bold text-rose-400 uppercase tracking-widest mb-1">Phone Number</p>
                                <p class="px-6 py-2 bg-rose-600 text-white rounded-xl text-sm font-bold shadow-lg shadow-rose-200">{{ $employee->emergency_contact_phone ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- WORK DETAILS TAB -->
        <div x-show="tab === 'work'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Employment Records card -->
                <div class="md:col-span-1 bg-white/80 backdrop-blur-xl border border-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 p-8">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-12 h-12 rounded-2xl bg-amber-50 flex items-center justify-center text-amber-600 shadow-sm border border-amber-100/50">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-slate-900">Employment</h3>
                            <p class="text-slate-500 text-sm">Role and contract status.</p>
                        </div>
                    </div>

                    <div class="space-y-4 mb-8">
                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 flex justify-between items-center">
                            <span class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Department</span>
                            <span class="font-bold text-slate-900 capitalize">{{ $employee->department ?? 'General' }}</span>
                        </div>
                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 flex justify-between items-center">
                            <span class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Contract</span>
                            <span class="font-bold text-slate-900 capitalize">{{ str_replace('_', ' ', $employee->employment_type) }}</span>
                        </div>
                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 flex justify-between items-center">
                            <span class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Joining Date</span>
                            <span class="font-bold text-slate-900">{{ $employee->joining_date->format('M d, Y') }}</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 mb-4 pt-4 border-t border-slate-50">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600 shadow-sm border border-indigo-100/50">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-900 text-sm">Educational Qualifications</h3>
                        </div>
                    </div>

                    @php
                        $details = $employee->academicDetails ?? $employee->administrativeDetails;
                    @endphp

                    <div class="space-y-4">
                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
                             <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Last Degree / Level</span>
                             <p class="font-bold text-slate-800">{{ $details->last_degree ?? 'N/A' }} ({{ $details->qualification_level ?? 'Tier N/A' }})</p>
                        </div>
                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
                             <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Institution</span>
                             <p class="font-bold text-slate-800 capitalize">{{ $details->institution ?? 'N/A' }}</p>
                        </div>
                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
                             <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Graduation Year</span>
                             <p class="font-bold text-slate-800">{{ $details->graduation_year ?? 'N/A' }}</p>
                        </div>
                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
                             <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Specialization</span>
                             <p class="font-bold text-indigo-600 capitalize italic">{{ $details->specialization ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>

                @if($employee->staff_category === 'academic')
                <!-- Academic Specialized Details -->
                <div class="md:col-span-2 bg-white/80 backdrop-blur-xl border border-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 p-8">
                    <div class="flex items-center justify-between mb-8 border-b border-slate-50 pb-6">
                        <div class="flex items-center gap-4">
                            <div class="w-1.5 h-6 bg-indigo-600 rounded-full"></div>
                            <div>
                                <h3 class="text-xl font-bold text-slate-900">Academic Governance</h3>
                                <p class="text-slate-500 text-sm">Faculty workload and qualifications.</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                        <div class="bg-indigo-50/30 p-4 rounded-2xl border border-indigo-100/20">
                            <span class="text-[9px] font-bold text-indigo-400 uppercase tracking-widest mb-1 block">Faculty Rank</span>
                            <p class="font-bold text-indigo-900 uppercase">{{ $employee->academicDetails->teacher_rank ?? 'N/A' }}</p>
                        </div>
                        <div class="bg-violet-50/30 p-4 rounded-2xl border border-violet-100/20">
                            <span class="text-[9px] font-bold text-violet-400 uppercase tracking-widest mb-1 block">Qualification</span>
                            <p class="font-bold text-violet-900 uppercase">{{ $employee->academicDetails->qualification_level ?? 'N/A' }}</p>
                        </div>
                        <div class="bg-emerald-50/30 p-4 rounded-2xl border border-emerald-100/20">
                            <span class="text-[9px] font-bold text-emerald-400 uppercase tracking-widest mb-1 block">Weekly Periods</span>
                            <p class="font-black text-emerald-900">{{ $employee->academicDetails->periods_per_week ?? '0' }}</p>
                        </div>
                    </div>

                    @if($employee->academicDetails && $employee->academicDetails->secondary_responsibilities)
                    <div class="mb-8 p-4 bg-slate-50/50 rounded-2xl border border-slate-100 flex items-start gap-3">
                        <svg class="w-5 h-5 text-indigo-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <div>
                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-0.5 block">Secondary Duties</span>
                            <p class="text-sm font-semibold text-slate-700 leading-tight">{{ $employee->academicDetails->secondary_responsibilities }}</p>
                        </div>
                    </div>
                    @endif

                    <div class="space-y-4">
                        <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Current Class Assignments</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @forelse($employee->user->teacherAssignments as $assignment)
                                <div class="bg-white border border-slate-100 p-4 rounded-2xl shadow-sm flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-xl bg-slate-900 text-white flex flex-col items-center justify-center font-black">
                                        <span class="text-[7px] uppercase opacity-50">GR</span>
                                        <span class="text-xs">{{ $assignment->section->gradeLevel->name }}</span>
                                    </div>
                                    <div>
                                        <p class="text-xs font-black text-slate-800 uppercase tracking-tight">{{ $assignment->subject->name }}</p>
                                        <p class="text-[9px] font-bold text-indigo-500 uppercase">{{ $assignment->section->name }}</p>
                                    </div>
                                </div>
                            @empty
                                <div class="col-span-full py-6 bg-slate-50/50 rounded-2xl border-2 border-dashed border-slate-100 text-center">
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">No active assignments</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
                @elseif($employee->staff_category === 'administrative')
                <!-- Administrative Specialized Details -->
                <div class="md:col-span-2 bg-white/80 backdrop-blur-xl border border-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 p-8">
                    <div class="flex items-center gap-4 mb-8 border-b border-slate-50 pb-6">
                        <div class="w-1.5 h-6 bg-emerald-600 rounded-full"></div>
                        <div>
                            <h3 class="text-xl font-bold text-slate-900">Administrative Governance</h3>
                            <p class="text-slate-500 text-sm">System access and operational roles.</p>
                        </div>
                    </div>

                    @if($employee->administrativeDetails && $employee->administrativeDetails->system_access_roles)
                    <div class="p-6 bg-emerald-50/30 rounded-[2rem] border border-emerald-100/50">
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-xl bg-white border border-emerald-100 flex items-center justify-center text-emerald-600 shadow-sm flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                            </div>
                            <div>
                                <span class="text-[9px] font-bold text-emerald-500 uppercase tracking-widest mb-1 block">Permission Scope</span>
                                <p class="text-sm font-semibold text-slate-700 leading-relaxed">{{ $employee->administrativeDetails->system_access_roles }}</p>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="py-10 bg-slate-50/50 rounded-[2rem] border-2 border-dashed border-slate-100 text-center">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">No specialized access recorded</p>
                    </div>
                    @endif
                </div>
                @endif
            </div>
        </div>

        <!-- FINANCIALS TAB -->
        <div x-show="tab === 'financials'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Salary Card -->
                <div class="bg-slate-900 rounded-[2.5rem] p-8 text-white shadow-xl shadow-slate-200">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="w-12 h-12 rounded-2xl bg-white/10 flex items-center justify-center text-amber-400 shadow-sm border border-white/10">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold">Compensation</h3>
                            <p class="text-slate-500 text-sm">Monthly pay records.</p>
                        </div>
                    </div>
                    
                    <div class="bg-white/5 p-6 rounded-2xl border border-white/10 mb-6">
                        <span class="text-[9px] font-bold text-slate-500 uppercase tracking-widest block mb-1">Basic Salary</span>
                        <p class="text-3xl font-black text-amber-400">ETB {{ number_format($employee->basic_salary, 2) }}</p>
                        <p class="text-[10px] text-slate-500 mt-1 italic font-semibold">Gross amount per standard cycle</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="p-4 rounded-xl bg-white/5 border border-white/10">
                            <span class="text-[9px] font-bold text-slate-500 uppercase tracking-widest block mb-1">Tax Code</span>
                            <span class="text-sm font-bold text-slate-300">STD-Tax</span>
                        </div>
                        <div class="p-4 rounded-xl bg-white/5 border border-white/10">
                            <span class="text-[9px] font-bold text-slate-500 uppercase tracking-widest block mb-1">Pension %</span>
                            <span class="text-sm font-bold text-slate-300">Standard</span>
                        </div>
                    </div>
                </div>

                <!-- Banking details card -->
                <div class="lg:col-span-2 bg-white/80 backdrop-blur-xl border border-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 p-8">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-600 shadow-sm border border-emerald-100/50">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-slate-900">Banking & Disbursement</h3>
                            <p class="text-slate-500 text-sm">Where salary is remitted.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div class="p-6 bg-slate-50/50 rounded-2xl border border-slate-100">
                             <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-2">Primary Bank</span>
                             <div class="flex items-center gap-4">
                                 <div class="w-10 h-10 rounded-lg bg-white border border-slate-200 flex items-center justify-center text-indigo-600 font-black text-xs">BK</div>
                                 <p class="text-lg font-black text-slate-900 uppercase tracking-tight">{{ $employee->bank_name ?? 'Internal / Petty' }}</p>
                             </div>
                        </div>
                        <div class="p-6 bg-slate-50/50 rounded-2xl border border-slate-100">
                             <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-2">Account Number</span>
                             <p class="text-xl font-black text-slate-800 tracking-widest">{{ $employee->account_number ?? 'Not Registered' }}</p>
                        </div>
                    </div>

                    <div class="mt-8 p-4 bg-emerald-50/30 rounded-2xl border border-emerald-100/30 flex items-center gap-3">
                        <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                        <p class="text-[10px] font-black text-emerald-600 uppercase tracking-widest">Verified Payment Account for {{ $employee->full_name }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- DOCUMENTS TAB -->
        <div x-show="tab === 'documents'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-8 pb-12">
            <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-xl shadow-slate-200/50 p-10">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-12">
                    <div class="flex items-center gap-5">
                        <div class="w-16 h-16 rounded-[1.5rem] bg-indigo-50 flex items-center justify-center text-indigo-600 shadow-sm border border-indigo-100/50">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-2xl font-black text-slate-900 tracking-tight">Digital Dossier</h3>
                            <p class="text-slate-500 font-semibold">Verified academic and professional attachments.</p>
                        </div>
                    </div>
                    @can('update', $employee)
                    <a href="{{ route('admin.employees.edit', $employee) }}" class="px-8 py-3 bg-slate-900 text-white font-black text-[10px] uppercase tracking-widest rounded-xl hover:bg-slate-800 transition-all shadow-xl shadow-slate-200 flex items-center gap-2 group">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                        Update Dossier
                    </a>
                    @endcan
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                    @php
                        $documentTypes = [
                            'resume' => ['label' => 'Curriculum Vitae', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'color' => 'indigo'],
                            'degree' => ['label' => 'Educational Degree', 'icon' => 'M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222', 'color' => 'violet'],
                            'certificate' => ['label' => 'Professional Cert.', 'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z', 'color' => 'emerald'],
                            'other' => ['label' => 'Support Documents', 'icon' => 'M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13', 'color' => 'slate']
                        ];
                    @endphp

                    @foreach($documentTypes as $type => $info)
                        @php $doc = $employee->documents->where('type', $type)->first(); @endphp
                        <div class="relative group h-full">
                            <div class="h-full bg-slate-50/50 rounded-[2rem] border-2 {{ $doc ? 'border-emerald-100' : 'border-dashed border-slate-200' }} p-6 flex flex-col items-center text-center transition-all duration-300 {{ $doc ? 'hover:bg-emerald-50/30' : 'hover:bg-slate-100/50' }}">
                                <div class="w-14 h-14 rounded-2xl {{ $doc ? 'bg-emerald-100 text-emerald-600' : 'bg-slate-100 text-slate-300' }} flex items-center justify-center mb-4 shadow-sm">
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $info['icon'] }}"></path></svg>
                                </div>
                                <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">{{ $info['label'] }}</h4>
                                
                                @if($doc)
                                    <p class="text-xs font-bold text-slate-800 line-clamp-1 mb-6 px-2">{{ $doc->name }}</p>
                                    <div class="mt-auto flex gap-2 w-full">
                                        <a href="{{ route('admin.employees.documents.download', $doc) }}" class="flex-grow py-2.5 bg-white border border-emerald-200 text-emerald-600 text-[9px] font-black uppercase tracking-widest rounded-xl hover:bg-emerald-600 hover:text-white transition-all shadow-sm flex items-center justify-center gap-2">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                            View File
                                        </a>
                                        <button @click.prevent="if(confirm('Request deletion of this certified document?')) window.location.href='{{ route('admin.employees.documents.delete', $doc) }}'" class="p-2.5 bg-white border border-rose-100 text-rose-400 rounded-xl hover:bg-rose-50 hover:text-rose-600 transition-all flex items-center justify-center shadow-sm">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </div>
                                @else
                                    <p class="text-[9px] font-bold text-slate-300 uppercase tracking-widest italic mb-6">Asset Not Uploaded</p>
                                    @can('update', $employee)
                                    <a href="{{ route('admin.employees.edit', $employee) }}" class="mt-auto w-full py-2.5 bg-white border border-slate-200 text-slate-400 text-[9px] font-black uppercase tracking-widest rounded-xl hover:bg-indigo-50 hover:text-indigo-600 hover:border-indigo-100 transition-all shadow-sm flex items-center justify-center gap-2">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                        Upload Now
                                    </a>
                                    @else
                                    <div class="mt-auto w-full py-2.5 bg-slate-100/50 text-slate-300 text-[9px] font-black uppercase tracking-widest rounded-xl flex items-center justify-center gap-2">
                                        Restricted
                                    </div>
                                    @endcan
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
