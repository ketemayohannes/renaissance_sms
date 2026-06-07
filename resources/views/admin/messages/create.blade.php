<x-admin-layout header="Compose Message">
<div class="space-y-5">

    {{-- Page header --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.messages.index') }}"
           class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-500 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h2 class="text-xl font-black text-slate-800 dark:text-slate-100">New Conversation</h2>
            <p class="text-xs text-slate-400 font-semibold mt-0.5">Filter by role, search, then select a recipient</p>
        </div>
    </div>

    {{-- Two-panel flex layout --}}
    <div x-data="{
        allUsers:  @js($users),
        roleNames: @js($roles->pluck('name')),
        activeRole: 'All',
        search: '',
        selectedUserId: '{{ old('recipient_id', '') }}',

        get filteredUsers() {
            return this.allUsers.filter(u => {
                const matchRole   = this.activeRole === 'All' || u.roles.includes(this.activeRole);
                const q           = this.search.toLowerCase().trim();
                const matchSearch = !q || u.name.toLowerCase().includes(q) || u.email.toLowerCase().includes(q);
                return matchRole && matchSearch;
            });
        },
        get selectedUser() {
            return this.allUsers.find(u => u.id == this.selectedUserId) || null;
        },
        countFor(role) {
            if (role === 'All') return this.allUsers.length;
            return this.allUsers.filter(u => u.roles.includes(role)).length;
        },
        avatarColor(name) {
            const p = ['bg-indigo-500','bg-violet-500','bg-purple-600','bg-rose-500',
                       'bg-emerald-500','bg-amber-500','bg-sky-500','bg-pink-500',
                       'bg-teal-500','bg-orange-500','bg-cyan-600','bg-lime-600'];
            let h = 0;
            for (let i = 0; i < name.length; i++) h = name.charCodeAt(i) + ((h << 5) - h);
            return p[Math.abs(h) % p.length];
        },
        selectUser(u) { this.selectedUserId = u.id; }
    }" style="display:grid; grid-template-columns: 360px 1fr; gap: 20px; align-items: start;">

        {{-- ══════════════════════════════════════════════════════
             LEFT — Fixed 360px: Role pills + Search + User list
        ══════════════════════════════════════════════════════ --}}
        <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden flex flex-col" style="max-height: 78vh;">

            {{-- Role filter pills (wrapped, scrollable) --}}
            <div class="flex-none border-b border-slate-100 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-800/30">
                <div class="px-3 pt-2.5 pb-2">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Filter by Role</p>
                    <div class="flex flex-wrap gap-1 overflow-y-auto" style="max-height: 110px;">

                        <button type="button" @click="activeRole = 'All'; search = ''"
                            :class="activeRole === 'All'
                                ? 'bg-indigo-600 text-white border-indigo-600'
                                : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:border-indigo-400 hover:text-indigo-600'"
                            class="inline-flex items-center gap-1 pl-2 pr-1.5 py-0.5 rounded-lg border text-[11px] font-bold transition-all flex-shrink-0">
                            All
                            <span :class="activeRole === 'All' ? 'bg-white/25 text-white' : 'bg-slate-100 dark:bg-slate-700 text-slate-500'"
                                  class="px-1 py-0.5 rounded text-[10px] font-black min-w-[18px] text-center"
                                  x-text="allUsers.length"></span>
                        </button>

                        <template x-for="role in roleNames" :key="role">
                            <button type="button" @click="activeRole = role; search = ''"
                                :class="activeRole === role
                                    ? 'bg-indigo-600 text-white border-indigo-600'
                                    : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:border-indigo-400 hover:text-indigo-600'"
                                class="inline-flex items-center gap-1 pl-2 pr-1.5 py-0.5 rounded-lg border text-[11px] font-bold transition-all flex-shrink-0 max-w-[160px]">
                                <span x-text="role" class="truncate"></span>
                                <span :class="activeRole === role
                                                ? 'bg-white/25 text-white'
                                                : countFor(role) > 0
                                                    ? 'bg-slate-100 dark:bg-slate-700 text-slate-500'
                                                    : 'bg-slate-50 dark:bg-slate-800 text-slate-300'"
                                      class="px-1 py-0.5 rounded text-[10px] font-black min-w-[18px] text-center flex-shrink-0"
                                      x-text="countFor(role)">
                                </span>
                            </button>
                        </template>
                    </div>
                </div>
            </div>

            {{-- Search --}}
            <div class="flex-none px-3 py-2 border-b border-slate-100 dark:border-slate-800">
                <div class="relative">
                    <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input x-model="search" type="text" id="recipient-search"
                        placeholder="Search name or email…"
                        class="w-full pl-8 pr-7 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs text-slate-800 dark:text-slate-100 placeholder-slate-400 font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all">
                    <button x-show="search" @click="search=''" type="button"
                        class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- User list --}}
            <div class="overflow-y-auto flex-1">
                <div x-show="filteredUsers.length === 0" class="flex flex-col items-center justify-center py-12 text-slate-400">
                    <svg class="w-8 h-8 mb-2 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <p class="text-xs font-bold text-slate-500">No users found</p>
                </div>

                <template x-for="user in filteredUsers" :key="user.id">
                    <button type="button" @click="selectUser(user)"
                        :class="selectedUserId == user.id
                            ? 'bg-indigo-50 dark:bg-indigo-900/20 border-l-[3px] border-indigo-500'
                            : 'border-l-[3px] border-transparent hover:bg-slate-50 dark:hover:bg-slate-800/40'"
                        class="w-full flex items-center gap-2.5 px-3 py-2.5 text-left transition-all">

                        <div class="relative flex-shrink-0">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center text-white text-xs font-black"
                                 :class="avatarColor(user.name)"
                                 x-text="user.name.charAt(0).toUpperCase()">
                            </div>
                            <div x-show="selectedUserId == user.id"
                                 class="absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 bg-indigo-600 rounded-full border-2 border-white dark:border-slate-900 flex items-center justify-center">
                                <svg class="w-1.5 h-1.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                        </div>

                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-bold text-slate-800 dark:text-slate-100 truncate" x-text="user.name"></p>
                            <p class="text-[10px] text-slate-400 truncate" x-text="user.email"></p>
                        </div>

                        <span class="flex-shrink-0 px-1.5 py-0.5 rounded text-[9px] font-black bg-slate-100 dark:bg-slate-700 text-slate-500 max-w-[60px] truncate"
                              x-text="user.primaryRole">
                        </span>
                    </button>
                </template>
            </div>

            {{-- Footer --}}
            <div class="flex-none px-3 py-2 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 flex items-center justify-between">
                <p class="text-[10px] text-slate-400 font-semibold">
                    <span class="text-slate-600 font-black" x-text="filteredUsers.length"></span>
                    / <span x-text="allUsers.length"></span> users
                </p>
                <button x-show="activeRole !== 'All' || search" type="button"
                    @click="activeRole='All'; search=''"
                    class="text-[10px] text-indigo-600 hover:text-indigo-800 font-bold transition-colors">
                    Clear
                </button>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════
             RIGHT — Flexible: Preview card + Compose form
        ══════════════════════════════════════════════════════ --}}
        <div class="flex flex-col gap-4 min-w-0">

            {{-- Selected user preview --}}
            <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl overflow-hidden shadow-sm">
                <div class="h-14 bg-gradient-to-r from-indigo-500 via-violet-500 to-purple-600 relative">
                    <div class="absolute inset-0 opacity-10"
                         style="background-image:url(\"data:image/svg+xml,%3Csvg width='20' height='20' viewBox='0 0 20 20' xmlns='http://www.w3.org/2000/svg'%3E%3Ccircle cx='3' cy='3' r='3' fill='%23fff'/%3E%3C/svg%3E\")">
                    </div>
                </div>
                <div class="px-5 pb-4 relative">
                    <div class="absolute -top-5 left-5">
                        <template x-if="selectedUser">
                            <div class="w-10 h-10 rounded-xl text-white flex items-center justify-center font-black text-base border-4 border-white dark:border-slate-900 shadow-lg uppercase"
                                 :class="avatarColor(selectedUser.name)"
                                 x-text="selectedUser.name.charAt(0)">
                            </div>
                        </template>
                        <template x-if="!selectedUser">
                            <div class="w-10 h-10 rounded-xl bg-slate-200 dark:bg-slate-700 border-4 border-white dark:border-slate-900 shadow-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                        </template>
                    </div>
                    <div class="pt-7 flex items-start justify-between gap-4">
                        <template x-if="selectedUser">
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap gap-1 mb-1">
                                    <template x-for="r in selectedUser.roles" :key="r">
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-300"
                                              x-text="r"></span>
                                    </template>
                                </div>
                                <h3 class="text-sm font-black text-slate-800 dark:text-slate-100 truncate" x-text="selectedUser.name"></h3>
                                <p class="text-xs text-slate-500 truncate" x-text="selectedUser.email"></p>
                            </div>
                        </template>
                        <template x-if="!selectedUser">
                            <div class="flex items-center gap-2 flex-1">
                                <svg class="w-4 h-4 text-slate-300 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"/>
                                </svg>
                                <p class="text-xs text-slate-400 font-semibold">Select a recipient from the list</p>
                            </div>
                        </template>
                        <template x-if="selectedUser">
                            <button type="button" @click="selectedUserId = ''"
                                class="flex-shrink-0 w-6 h-6 rounded-lg bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-slate-400 hover:text-rose-500 hover:bg-rose-50 transition-colors">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </template>
                    </div>
                </div>
            </div>

            {{-- Compose form --}}
            <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl p-6 shadow-sm">
                @if(session('success'))
                    <div class="mb-4 px-4 py-3 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300 rounded-xl text-sm font-bold flex items-center gap-2">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        {{ session('success') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.messages.store') }}" class="space-y-5">
                    @csrf
                    <input type="hidden" name="recipient_id" :value="selectedUserId">

                    @error('recipient_id')
                        <div class="px-3 py-2 bg-rose-50 dark:bg-rose-900/20 rounded-xl text-xs text-rose-600 font-bold flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            {{ $message }}
                        </div>
                    @enderror

                    <div>
                        <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-1.5">
                            Subject / Thread Name <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="subject" value="{{ old('subject') }}"
                            placeholder="What is this about?"
                            class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-800 dark:text-slate-100 font-semibold text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all" required>
                        @error('subject') <p class="text-xs text-rose-500 mt-1 font-semibold">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-1.5">
                            Message <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="body" rows="9" placeholder="Write your message here…"
                            class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-800 dark:text-slate-100 font-medium text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all leading-relaxed resize-none" required>{{ old('body') }}</textarea>
                        @error('body') <p class="text-xs text-rose-500 mt-1 font-semibold">{{ $message }}</p> @enderror
                    </div>

                    <button type="submit"
                        :disabled="!selectedUserId"
                        class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl font-bold text-sm transition-all shadow-md
                               bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-700 hover:to-violet-700 text-white
                               disabled:opacity-40 disabled:cursor-not-allowed disabled:shadow-none">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                        <span x-text="selectedUser
                            ? 'Send to ' + selectedUser.name.split(' ')[0]
                            : 'Select a recipient first'">
                        </span>
                    </button>
                </form>
            </div>
        </div>

    </div>{{-- /grid --}}
</div>
</x-admin-layout>
