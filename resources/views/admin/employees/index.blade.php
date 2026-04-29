<x-admin-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="font-heading font-bold text-2xl text-slate-900">Staff Management</h2>
                <p class="text-slate-500 text-sm mt-1">Manage all school staff members and their roles.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.employees.import') }}" 
                   class="inline-flex items-center px-4 py-2 bg-white border border-slate-200 text-slate-700 text-sm font-semibold rounded-xl hover:bg-slate-50 transition-all gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                    </svg>
                    Import
                </a>
                <a href="{{ route('admin.employees.create') }}" 
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl transition-all duration-200 shadow-lg shadow-indigo-100 gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Add New Employee
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Breadcrumb -->
        <x-breadcrumb :items="[
            ['label' => 'Staff Management', 'url' => '#']
        ]" />

        <!-- Import Errors Display -->
        @if(session('import_errors'))
            <div class="bg-rose-50 border border-rose-200 rounded-2xl p-4" x-data="{ open: true }">
                <div class="flex items-center justify-between cursor-pointer" @click="open = !open">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-rose-100 flex items-center justify-center text-rose-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-rose-800">Import Issues Detected</h3>
                            <p class="text-xs text-rose-600">{{ count(session('import_errors')) }} row(s) could not be imported. Click to view details.</p>
                        </div>
                    </div>
                    <svg class="w-5 h-5 text-rose-400 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </div>
                <div x-show="open" x-collapse class="mt-4">
                    <div class="bg-white rounded-xl border border-rose-100 divide-y divide-rose-50 max-h-64 overflow-y-auto">
                        @foreach(session('import_errors') as $error)
                            <div class="px-4 py-3 text-sm text-rose-700 flex items-start gap-3">
                                <svg class="w-4 h-4 text-rose-400 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                </svg>
                                <span>{{ $error }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- Stats Overview -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white/60 backdrop-blur-md p-4 rounded-3xl border border-white shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Headcount</p>
                    <p class="text-xl font-bold text-slate-900">{{ $stats['total'] }}</p>
                </div>
            </div>

            <div class="bg-white/60 backdrop-blur-md p-4 rounded-3xl border border-white shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Active Operations</p>
                    <p class="text-xl font-bold text-slate-900">{{ $stats['active'] }}</p>
                </div>
            </div>

            <div class="bg-white/60 backdrop-blur-md p-4 rounded-3xl border border-white shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-purple-50 flex items-center justify-center text-purple-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Teaching Staff</p>
                    <p class="text-xl font-bold text-slate-900">{{ $stats['teachers'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white/80 backdrop-blur-xl rounded-[2rem] border border-white shadow-xl shadow-slate-200/50 overflow-hidden">
            <div class="p-6">
                <!-- Filters Section -->
                <div class="mb-8 p-6 bg-slate-50/50 rounded-3xl border border-slate-100">
                    <form action="{{ route('admin.employees.index') }}" method="GET">
                        <div class="grid grid-cols-1 md:grid-cols-6 gap-4 mb-4">
                            <div class="md:col-span-2">
                                <label for="search" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Search Registry</label>
                                <div class="relative">
                                    <input type="text" name="search" id="search" value="{{ request('search') }}" 
                                           placeholder="Name, ID or Designation..." 
                                           class="w-full pl-10 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all">
                                    <svg class="w-5 h-5 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </div>
                            </div>

                            <div>
                                <label for="role" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Primary Role</label>
                                <select name="role" id="role" class="w-full py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all">
                                    <option value="">All Roles</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->name }}" {{ request('role') == $role->name ? 'selected' : '' }}>{{ $role->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="staff_category" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Category</label>
                                <select name="staff_category" id="staff_category" class="w-full py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all">
                                    <option value="">All Categories</option>
                                    <option value="academic" {{ request('staff_category') == 'academic' ? 'selected' : '' }}>Academic (Teaching)</option>
                                    <option value="administrative" {{ request('staff_category') == 'administrative' ? 'selected' : '' }}>Administrative</option>
                                    <option value="support" {{ request('staff_category') == 'support' ? 'selected' : '' }}>Support Staff</option>
                                </select>
                            </div>

                            <div class="flex items-end gap-2">
                                <button type="submit" class="flex-1 py-2.5 bg-slate-900 text-white font-bold text-sm rounded-xl hover:bg-slate-800 transition-all active:scale-95">
                                    Apply Filters
                                </button>
                                @if(request()->anyFilled(['search', 'role', 'designation', 'staff_category']))
                                    <a href="{{ route('admin.employees.index') }}" class="p-2.5 bg-rose-50 text-rose-600 rounded-xl hover:bg-rose-100 transition-all" title="Clear Filters">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Registry Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100">
                                <th class="px-6 py-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Employee ID & Name</th>
                                <th class="px-6 py-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Role & Division</th>
                                <th class="px-6 py-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Contact</th>
                                <th class="px-6 py-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider text-right">Actions</th>
                            </tr>
                        </thead>
                    <tbody class="divide-y divide-slate-100/50">
                        @forelse($employees as $employee)
                            <tr class="hover:bg-indigo-50/20 transition-all duration-300 group/row">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-4">
                                        <div class="relative">
                                            <div class="w-12 h-12 rounded-2xl overflow-hidden bg-slate-100 transition-transform group-hover/row:scale-105">
                                                @if($employee->photo)
                                                    <img src="{{ Storage::url($employee->photo) }}" class="w-full h-full object-cover">
                                                @else
                                                    <div class="w-full h-full flex items-center justify-center bg-indigo-50 text-indigo-600 font-bold text-lg">
                                                        {{ substr($employee->first_name, 0, 1) }}
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 rounded-full border-2 border-white {{ $employee->is_active ? 'bg-emerald-500' : 'bg-slate-300' }}"></div>
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-slate-900 group-hover/row:text-indigo-600 transition-colors">{{ $employee->full_name }}</p>
                                            <div class="flex items-center gap-2 mt-0.5">
                                                <span class="text-[10px] font-semibold text-slate-500">{{ $employee->employee_id }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="space-y-1">
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($employee->user->roles as $role)
                                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-tight
                                                    {{ $role->category === 'academic' ? 'bg-indigo-50 text-indigo-600 border border-indigo-100' : 'bg-emerald-50 text-emerald-600 border border-emerald-100' }}">
                                                    {{ $role->name }}
                                                </span>
                                            @endforeach
                                            @if($employee->user->roles->isEmpty())
                                                <span class="text-sm font-semibold text-slate-400 italic">No Role</span>
                                            @endif
                                        </div>
                                        <p class="text-[10px] text-slate-400 font-bold uppercase mt-1">{{ $employee->division->name ?? 'Global' }} Division</p>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="space-y-0.5">
                                        <p class="text-xs font-medium text-slate-700">{{ $employee->phone }}</p>
                                        <p class="text-[11px] text-slate-500">{{ $employee->email }}</p>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        @if(($employee->user && $employee->user->hasRole('Teacher')) || stripos($employee->designation, 'teacher') !== false)
                                            <a href="{{ route('admin.teacher-assignments.create', ['teacher_user_id' => $employee->user_id]) }}" 
                                               class="p-2 text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-xl transition-all"
                                               title="Assign Classes & Subjects">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                                            </a>
                                        @endif
                                        <a href="{{ route('admin.employees.show', $employee) }}" 
                                           class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition-all"
                                           title="View Details">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        </a>
                                        <a href="{{ route('admin.employees.edit', $employee) }}" 
                                           class="p-2 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-xl transition-all"
                                           title="Edit">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </a>
                                        <form action="{{ route('admin.employees.destroy', $employee) }}" method="POST" class="delete-form" data-confirm-message="Are you sure?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-all" title="Delete">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-24 text-center">
                                    <div class="flex flex-col items-center">
                                         <div class="w-20 h-20 bg-slate-50 rounded-3xl flex items-center justify-center mb-6">
                                             <svg class="w-10 h-10 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                         </div>
                                         <h3 class="text-xl font-bold text-slate-900 text-[11px] uppercase tracking-wider">No Employees Found</h3>
                                         <p class="text-slate-400 text-sm mt-1">The institutional registry contains no matching records.</p>
                                         <div class="flex gap-4 mt-6">
                                            @if(request()->anyFilled(['search', 'designation', 'staff_category']))
                                                <a href="{{ route('admin.employees.index') }}" class="px-6 py-2 bg-slate-900 text-white rounded-xl font-bold text-sm shadow-xl hover:bg-rose-600 transition-all">Reset Operations</a>
                                            @else
                                                <a href="{{ route('admin.employees.create') }}" class="px-6 py-2 bg-indigo-600 text-white rounded-xl font-bold text-sm shadow-xl hover:bg-indigo-700 transition-all">Add New Employee</a>
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
                <div class="px-6 py-4 border-t border-slate-100 flex justify-between items-center bg-slate-50/30">
                    <div class="text-xs font-bold text-slate-400 uppercase tracking-widest leading-none pt-1">
                        Showing {{ $employees->firstItem() ?? 0 }}-{{ $employees->lastItem() ?? 0 }} of {{ $employees->total() }}
                    </div>
                    {{ $employees->links() }}
                </div>
            @endif
        </div>
        
        <div class="mt-4 flex items-center justify-between px-6 text-[10px] font-bold uppercase tracking-wider text-slate-400">
            <div class="flex items-center gap-4">
                <span class="flex items-center gap-1.5">
                    <span class="w-1 h-1 bg-indigo-400 rounded-full"></span>
                    Peak Core: {{ number_format((microtime(true) - LARAVEL_START) * 1000, 2) }}ms
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-1 h-1 bg-slate-400 rounded-full"></span>
                    Queries: {{ $queryCount ?? 0 }}
                </span>
            </div>
            <div>Registry Engine v4.5</div>
        </div>
    </div>
</x-admin-layout>
