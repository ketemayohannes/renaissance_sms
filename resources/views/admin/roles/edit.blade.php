<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-indigo-600 rounded-2xl flex items-center justify-center shadow-lg shadow-indigo-100">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Edit Role</h2>
                    <p class="text-sm text-slate-500 font-medium">{{ $role->name }}</p>
                </div>
            </div>
            <a href="{{ route('admin.roles.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-slate-200 text-slate-700 text-sm font-bold rounded-xl hover:bg-slate-50 transition-all gap-2 shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Back to List
            </a>
        </div>
    </x-slot>

    <div class="pb-24">
        <form action="{{ route('admin.roles.update', $role->id) }}" method="POST" id="roleForm">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Role Info Side -->
                <div class="lg:col-span-1 space-y-6">
                    <div class="bg-white rounded-[2rem] p-8 border border-slate-200 shadow-sm sticky top-6">
                        <h3 class="text-lg font-bold text-slate-900 mb-6 flex items-center gap-2">
                            <span class="w-1.5 h-6 bg-indigo-600 rounded-full"></span>
                            Role Information
                        </h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label for="name" class="block text-xs font-black text-slate-500 uppercase tracking-wider mb-2 ml-1">Role Name</label>
                                <input type="text" name="name" id="name" value="{{ old('name', $role->name) }}" 
                                       class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 font-bold focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all placeholder:text-slate-400"
                                       required {{ $role->name === 'Super Admin' ? 'readonly' : '' }}>
                                @error('name') <p class="mt-2 text-xs font-bold text-rose-500 ml-1">{{ $message }}</p> @enderror
                            </div>

                            <div class="pt-6">
                                <button type="submit" class="w-full py-4 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-2xl shadow-xl shadow-slate-200 transition-all flex items-center justify-center gap-3 group">
                                    <span>Update Role & Permissions</span>
                                    <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                                </button>
                            </div>
                        </div>

                        <div class="mt-8 p-6 bg-indigo-50 rounded-2xl border border-indigo-100">
                            <div class="flex gap-3">
                                <svg class="w-5 h-5 text-indigo-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <p class="text-xs text-indigo-900 font-medium leading-relaxed">
                                    Changes to permissions take effect immediately for all users assigned to this role.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Permissions Matrix -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-[2rem] border border-slate-200 shadow-sm overflow-hidden">
                        <div class="p-8 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                            <div>
                                <h3 class="text-lg font-bold text-slate-900">Module Permissions</h3>
                                <p class="text-sm text-slate-500 font-medium mt-1">Select the actions allowed for this role across different modules.</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" @click="selectAll()" class="text-xs font-bold text-indigo-600 hover:text-indigo-700 bg-indigo-50 px-3 py-1.5 rounded-lg transition-colors">Select All</button>
                                <button type="button" @click="deselectAll()" class="text-xs font-bold text-slate-500 hover:text-slate-600 bg-slate-100 px-3 py-1.5 rounded-lg transition-colors">Deselect All</button>
                            </div>
                        </div>

                        <div class="p-8">
                            <div class="space-y-8">
                                @foreach($permissions as $group => $perms)
                                    <div class="permission-group" x-data="{ groupOpen: true }">
                                        <div class="flex items-center justify-between mb-4 pb-2 border-b border-slate-100">
                                            <div class="flex items-center gap-3">
                                                <div class="w-2 h-2 rounded-full bg-indigo-400"></div>
                                                <h4 class="text-sm font-black text-slate-900 uppercase tracking-widest">{{ $group }}</h4>
                                            </div>
                                            <label class="inline-flex items-center cursor-pointer group">
                                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-tighter mr-2 group-hover:text-indigo-600 transition-colors">Toggle Group</span>
                                                <input type="checkbox" class="hidden" @change="toggleGroup($event, '{{ $group }}')">
                                                <div class="w-8 h-4 bg-slate-200 rounded-full relative transition-colors" :class="groupChecked('{{ $group }}') ? 'bg-indigo-600' : ''">
                                                    <div class="absolute top-0.5 left-0.5 w-3 h-3 bg-white rounded-full transition-transform" :class="groupChecked('{{ $group }}') ? 'translate-x-4' : ''"></div>
                                                </div>
                                            </label>
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                            @foreach($perms as $permission)
                                                <label for="perm_{{ $permission->id }}" 
                                                       class="relative flex items-center p-4 bg-slate-50 border border-slate-200 rounded-2xl cursor-pointer hover:bg-white hover:border-indigo-300 hover:shadow-md hover:shadow-indigo-50 transition-all group"
                                                       :class="isPermChecked('{{ $permission->name }}') ? 'bg-white border-indigo-500 ring-2 ring-indigo-500/10' : ''">
                                                    <div class="flex items-center gap-4">
                                                        <div class="relative flex items-center justify-center">
                                                            <input type="checkbox" name="permissions[]" value="{{ $permission->name }}" id="perm_{{ $permission->id }}" 
                                                                   {{ in_array($permission->name, $rolePermissions) ? 'checked' : '' }}
                                                                   class="peer w-6 h-6 bg-white border-2 border-slate-300 rounded-lg text-indigo-600 focus:ring-0 focus:ring-offset-0 transition-all cursor-pointer"
                                                                   data-group="{{ $group }}">
                                                            <svg class="absolute w-4 h-4 text-white opacity-0 peer-checked:opacity-100 transition-opacity pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                                        </div>
                                                        <div class="flex flex-col">
                                                            <span class="text-sm font-bold text-slate-800 group-hover:text-indigo-600 transition-colors capitalize">
                                                                {{ str_replace($group, '', $permission->name) }}
                                                            </span>
                                                            <span class="text-[10px] font-medium text-slate-400 uppercase tracking-tighter">Permission ID: {{ $permission->id }}</span>
                                                        </div>
                                                    </div>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('roleManager', () => ({
                checkedPermissions: @json($rolePermissions),
                
                selectAll() {
                    const checkboxes = document.querySelectorAll('input[name="permissions[]"]');
                    checkboxes.forEach(cb => {
                        cb.checked = true;
                        if (!this.checkedPermissions.includes(cb.value)) {
                            this.checkedPermissions.push(cb.value);
                        }
                    });
                },

                deselectAll() {
                    const checkboxes = document.querySelectorAll('input[name="permissions[]"]');
                    checkboxes.forEach(cb => cb.checked = false);
                    this.checkedPermissions = [];
                },

                toggleGroup(e, groupName) {
                    const groupCheckboxes = document.querySelectorAll(`input[data-group="${groupName}"]`);
                    groupCheckboxes.forEach(cb => {
                        cb.checked = e.target.checked;
                        if (e.target.checked) {
                            if (!this.checkedPermissions.includes(cb.value)) this.checkedPermissions.push(cb.value);
                        } else {
                            this.checkedPermissions = this.checkedPermissions.filter(p => p !== cb.value);
                        }
                    });
                },

                groupChecked(groupName) {
                    const groupCheckboxes = document.querySelectorAll(`input[data-group="${groupName}"]`);
                    if (groupCheckboxes.length === 0) return false;
                    return Array.from(groupCheckboxes).every(cb => this.checkedPermissions.includes(cb.value));
                },

                isPermChecked(name) {
                    return this.checkedPermissions.includes(name);
                }
            }))
        });

        // Simple vanilla sync for the checkboxes to Alpine state if needed, 
        // but for now standard form submission works fine.
        document.querySelectorAll('input[name="permissions[]"]').forEach(cb => {
            cb.addEventListener('change', (e) => {
                const alpineData = document.querySelector('[x-data]').__x.$data;
                if (e.target.checked) {
                    if (!alpineData.checkedPermissions.includes(e.target.value)) alpineData.checkedPermissions.push(e.target.value);
                } else {
                    alpineData.checkedPermissions = alpineData.checkedPermissions.filter(p => p !== e.target.value);
                }
            });
        });
    </script>
    @endpush

    <!-- Initialize Alpine component on the form -->
    <script>
        document.getElementById('roleForm').setAttribute('x-data', 'roleManager()');
    </script>
</x-admin-layout>
