<x-admin-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Role Management</h2>
                <p class="text-sm text-slate-500 font-medium mt-1">Control system access levels and permissions</p>
            </div>
            <a href="{{ route('admin.roles.create') }}" 
               class="inline-flex items-center px-6 py-3 bg-slate-900 hover:bg-slate-800 text-white text-sm font-bold rounded-2xl transition-all shadow-xl shadow-slate-200 gap-2 group">
                <svg class="w-5 h-5 group-hover:rotate-90 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                Create New Role
            </a>
        </div>
    </x-slot>

    <div class="space-y-6 pb-24">
        <!-- Role Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($roles as $role)
                <div class="group bg-white rounded-[2rem] border border-slate-200 shadow-sm hover:shadow-xl hover:shadow-indigo-50 hover:border-indigo-200 transition-all duration-300 overflow-hidden">
                    <div class="p-8">
                        <div class="flex items-start justify-between mb-6">
                            <div class="w-14 h-14 bg-slate-50 rounded-2xl flex items-center justify-center group-hover:bg-indigo-50 transition-colors">
                                @if($role->name === 'Super Admin')
                                    <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                                @else
                                    <svg class="w-8 h-8 text-slate-400 group-hover:text-indigo-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                @endif
                            </div>
                            <div class="flex items-center gap-1">
                                <span class="px-3 py-1 bg-slate-100 text-[10px] font-black text-slate-500 uppercase tracking-widest rounded-full">
                                    {{ $role->permissions->count() }} Perms
                                </span>
                            </div>
                        </div>

                        <h3 class="text-xl font-bold text-slate-900 mb-2 group-hover:text-indigo-600 transition-colors">{{ $role->name }}</h3>
                        <p class="text-sm text-slate-500 font-medium leading-relaxed mb-6">
                            {{ $role->name === 'Super Admin' ? 'Full system access with no restrictions.' : 'Custom defined access levels for specific staff responsibilities.' }}
                        </p>

                        <div class="flex items-center gap-3">
                            <a href="{{ route('admin.roles.edit', $role->id) }}" 
                               class="flex-1 py-3 bg-white border border-slate-200 text-slate-700 text-xs font-bold rounded-xl hover:bg-slate-50 transition-all text-center flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                Manage Role
                            </a>
                            @if($role->name !== 'Super Admin')
                                <form action="{{ route('admin.roles.destroy', $role->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure? This will affect all users with this role.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-3 text-rose-500 hover:bg-rose-50 border border-transparent hover:border-rose-100 rounded-xl transition-all">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                    
                    @if($role->permissions->count() > 0)
                        <div class="px-8 py-4 bg-slate-50/50 border-t border-slate-100 flex flex-wrap gap-1.5">
                            @foreach($role->permissions->take(3) as $perm)
                                <span class="text-[9px] font-bold text-indigo-600 bg-indigo-50/50 px-2 py-0.5 rounded border border-indigo-100 capitalize">{{ $perm->name }}</span>
                            @endforeach
                            @if($role->permissions->count() > 3)
                                <span class="text-[9px] font-bold text-slate-400 px-2 py-0.5">+{{ $role->permissions->count() - 3 }} more</span>
                            @endif
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        <!-- Info Section -->
        <div class="bg-indigo-900 rounded-[2.5rem] p-10 text-white relative overflow-hidden shadow-2xl shadow-indigo-200">
            <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/2 blur-3xl"></div>
            <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-8">
                <div class="max-w-xl">
                    <h3 class="text-2xl font-black mb-3 tracking-tight">Advanced Permission Control</h3>
                    <p class="text-indigo-100 font-medium leading-relaxed">
                        Granular control allows you to define exactly what staff can see and do. Grouping permissions by module makes it easy to audit and manage access for Supervisors, Teachers, and Administrative staff.
                    </p>
                </div>
                <div class="flex-shrink-0 grid grid-cols-2 gap-4">
                    <div class="bg-white/10 backdrop-blur-md rounded-2xl p-6 text-center border border-white/10">
                        <p class="text-3xl font-black">{{ $roles->count() }}</p>
                        <p class="text-[10px] font-black uppercase tracking-widest text-indigo-300">Total Roles</p>
                    </div>
                    <div class="bg-white/10 backdrop-blur-md rounded-2xl p-6 text-center border border-white/10">
                        <p class="text-3xl font-black">{{ \Spatie\Permission\Models\Permission::count() }}</p>
                        <p class="text-[10px] font-black uppercase tracking-widest text-indigo-300">Permissions</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
