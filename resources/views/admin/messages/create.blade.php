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

    {{-- Main 3-column layout --}}
    <div x-data="{
        /* ── All users + roles ── */
        allUsers: @js($users),
        roleNames: @js($roles->pluck('name')),

        /* ── Filter state ── */
        activeRole: 'All',
        search: '',

        /* ── Selection ── */
        selectedUserId: '{{ old('recipient_id', '') }}',

        /* ── Computed: filter by ALL roles, not just primary ── */
        get filteredUsers() {
            return this.allUsers.filter(u => {
                const matchRole   = this.activeRole === 'All' || u.roles.includes(this.activeRole);
                const q           = this.search.toLowerCase().trim();
                const matchSearch = !q
                    || u.name.toLowerCase().includes(q)
                    || u.email.toLowerCase().includes(q);
                return matchRole && matchSearch;
            });
        },

        get selectedUser() {
            return this.allUsers.find(u => u.id == this.selectedUserId) || null;
        },

        /* ── Count per role (counts ALL role memberships) ── */
        countFor(role) {
            if (role === 'All') return this.allUsers.length;
            return this.allUsers.filter(u => u.roles.includes(role)).length;
        },

        avatarColor(name) {
            const palette = [
                'bg-indigo-500','bg-violet-500','bg-purple-600','bg-rose-500',
                'bg-emerald-500','bg-amber-500','bg-sky-500','bg-pink-500',
                'bg-teal-500','bg-orange-500','bg-cyan-600','bg-lime-600',
            ];
            let h = 0;
            for (let i = 0; i < name.length; i++) h = name.charCodeAt(i) + ((h << 5) - h);
            return palette[Math.abs(h) % palette.length];
        },

        selectUser(user) { this.selectedUserId = user.id; }

    }" class="grid grid-cols-12 gap-5" style="min-height: 70vh;">

        {{-- ══════════════════════════════════════════════════════
             COL 1 — Role sidebar (3 cols)
        ══════════════════════════════════════════════════════ --}}
        <div class="col-span-12 lg:col-span-3">
            <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden sticky top-4">
                <div class="px-4 py-3.5 border-b border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/60">
                    <p class="text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest">Filter by Role</p>
                </div>
                <div class="overflow-y-auto" style="max-height: 520px;">
                    {{-- All --}}
                    <button type="button" @click="activeRole = 'All'; search = ''"
                        :class="activeRole === 'All'
                            ? 'bg-indigo-50 dark:bg-indigo-900/25 text-indigo-700 dark:text-indigo-300 border-r-2 border-indigo-500 font-black'
                            : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/50 font-semibold'"
                        class="w-full flex items-center justify-between px-4 py-2.5 text-sm transition-all text-left">
                        <span>All Users</span>
                        <span :class="activeRole === 'All' ? 'bg-indigo-600 text-white' : 'bg-slate-100 dark:bg-slate-700 text-slate-500'"
                              class="text-[10px] font-black px-1.5 py-0.5 rounded-md min-w-[24px] text-center"
                              x-text="allUsers.length"></span>
                    </button>

                    <div class="border-t border-slate-100 dark:border-slate-800 my-1"></div>

                    {{-- Individual roles --}}
                    <template x-for="role in roleNames" :key="role">
                        <button type="button" @click="activeRole = role; search = ''"
                            :class="activeRole === role
                                ? 'bg-indigo-50 dark:bg-indigo-900/25 text-indigo-700 dark:text-indigo-300 border-r-2 border-indigo-500 font-black'
                                : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/50 font-semibold'"
                            class="w-full flex items-center justify-between px-4 py-2 text-sm transition-all text-left group">
                            <span class="truncate pr-2" x-text="role"></span>
                            <span :class="activeRole === role
                                            ? 'bg-indigo-600 text-white'
                                            : countFor(role) > 0
                                                ? 'bg-slate-100 dark:bg-slate-700 text-slate-500'
                                                : 'bg-slate-50 dark:bg-slate-800 text-slate-300 dark:text-slate-600'"
                                  class="flex-shrink-0 text-[10px] font-black px-1.5 py-0.5 rounded-md min-w-[24px] text-center"
                                  x-text="countFor(role)">
                            </span>
                        </button>
                    </template>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════
             COL 2 — Search + User list (5 cols)
        ══════════════════════════════════════════════════════ --}}
        <div class="col-span-12 lg:col-span-5">
            <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden flex flex-col" style="max-height: 80vh;">

                {{-- Search --}}
                <div class="p-4 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30">
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input x-model="search" type="text" id="recipient-search"
                            placeholder="Search by name or email…"
                            class="w-full pl-10 pr-9 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-slate-100 placeholder-slate-400 font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all shadow-sm">
                        <button x-show="search" @click="search=''" type="button"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    {{-- Active filter breadcrumb --}}
                    <div class="flex items-center gap-2 mt-2.5">
                        <span class="text-xs text-slate-400 font-semibold">Showing:</span>
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 rounded-lg text-xs font-black">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                            </svg>
                            <span x-text="activeRole"></span>
                        </span>
                        <span class="text-xs text-slate-400 font-semibold">
                            · <span class="text-indigo-600 font-black" x-text="filteredUsers.length"></span>
                            <span x-text="filteredUsers.length === 1 ? 'user' : 'users'"></span>
                        </span>
                    </div>
                </div>

                {{-- User list --}}
                <div class="overflow-y-auto flex-1">

                    {{-- Empty state --}}
                    <div x-show="filteredUsers.length === 0" class="flex flex-col items-center justify-center py-16 text-slate-400">
                        <div class="w-16 h-16 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center mb-4">
                            <svg class="w-8 h-8 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <p class="text-sm font-bold text-slate-500">No users found</p>
                        <p class="text-xs mt-1 opacity-60">Try a different search or role</p>
                    </div>

                    {{-- User rows --}}
                    <template x-for="user in filteredUsers" :key="user.id">
                        <button type="button" @click="selectUser(user)"
                            :class="selectedUserId == user.id
                                ? 'bg-indigo-50/80 dark:bg-indigo-900/20 border-l-[3px] border-indigo-500'
                                : 'border-l-[3px] border-transparent hover:bg-slate-50 dark:hover:bg-slate-800/40'"
                            class="w-full flex items-center gap-3.5 px-4 py-3 text-left transition-all group">

                            {{-- Avatar --}}
                            <div class="relative flex-shrink-0">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white text-sm font-black transition-transform group-hover:scale-105"
                                     :class="avatarColor(user.name)"
                                     x-text="user.name.charAt(0).toUpperCase()">
                                </div>
                                {{-- Selected dot --}}
                                <div x-show="selectedUserId == user.id"
                                     class="absolute -bottom-0.5 -right-0.5 w-4 h-4 bg-indigo-600 rounded-full border-2 border-white dark:border-slate-900 flex items-center justify-center">
                                    <svg class="w-2 h-2 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3.5" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                            </div>

                            {{-- Name + email --}}
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold text-slate-800 dark:text-slate-100 truncate" x-text="user.name"></p>
                                <p class="text-xs text-slate-400 truncate mt-0.5" x-text="user.email"></p>
                            </div>

                            {{-- Primary role badge --}}
                            <span class="flex-shrink-0 px-2 py-0.5 rounded-lg text-[10px] font-black tracking-wide bg-slate-100 dark:bg-slate-700/60 text-slate-500 dark:text-slate-300 max-w-[90px] truncate"
                                  x-text="user.primaryRole">
                            </span>
                        </button>
                    </template>
                </div>

                {{-- Footer --}}
                <div class="px-4 py-2.5 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30 flex items-center justify-between">
                    <p class="text-xs text-slate-400 font-semibold">
                        <span class="text-slate-600 dark:text-slate-300 font-black" x-text="filteredUsers.length"></span>
                        of <span x-text="allUsers.length"></span> users
                    </p>
                    <button x-show="activeRole !== 'All' || search"
                            type="button" @click="activeRole='All'; search=''"
                            class="text-xs text-indigo-600 hover:text-indigo-800 font-bold transition-colors">
                        Clear filters
                    </button>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════
             COL 3 — Preview card + Compose form (4 cols)
        ══════════════════════════════════════════════════════ --}}
        <div class="col-span-12 lg:col-span-4 space-y-4">

            {{-- Selected user preview --}}
            <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl overflow-hidden shadow-sm">
                <div class="h-16 bg-gradient-to-r from-indigo-500 via-violet-500 to-purple-600 relative">
                    <div class="absolute inset-0 opacity-20"
                         style="background-image: url(\"data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='1'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E\");">
                    </div>
                </div>
                <div class="px-5 pb-5 relative">
                    <div class="absolute -top-6 left-5">
                        <template x-if="selectedUser">
                            <div class="w-12 h-12 rounded-xl text-white flex items-center justify-center font-black text-xl border-4 border-white dark:border-slate-900 shadow-lg uppercase"
                                 :class="avatarColor(selectedUser.name)"
                                 x-text="selectedUser.name.charAt(0)">
                            </div>
                        </template>
                        <template x-if="!selectedUser">
                            <div class="w-12 h-12 rounded-xl bg-slate-200 dark:bg-slate-700 border-4 border-white dark:border-slate-900 shadow-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                        </template>
                    </div>
                    <div class="pt-8">
                        <template x-if="selectedUser">
                            <div>
                                <div class="flex flex-wrap gap-1 mb-1.5">
                                    <template x-for="r in selectedUser.roles" :key="r">
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-300"
                                              x-text="r">
                                        </span>
                                    </template>
                                </div>
                                <h3 class="text-sm font-black text-slate-800 dark:text-slate-100" x-text="selectedUser.name"></h3>
                                <p class="text-xs text-slate-500 mt-0.5 truncate" x-text="selectedUser.email"></p>
                            </div>
                        </template>
                        <template x-if="!selectedUser">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-slate-300 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                                </svg>
                                <p class="text-xs text-slate-400 font-semibold">Select a recipient from the list</p>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            {{-- Compose form --}}
            <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
                @if(session('success'))
                    <div class="mb-4 px-4 py-3 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300 rounded-xl text-sm font-bold flex items-center gap-2">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        {{ session('success') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.messages.store') }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="recipient_id" :value="selectedUserId">

                    @error('recipient_id')
                        <div class="px-3 py-2 bg-rose-50 dark:bg-rose-900/20 rounded-xl text-xs text-rose-600 font-bold flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ $message }}
                        </div>
                    @enderror

                    <div>
                        <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-1.5">
                            Subject <span class="text-rose-500">*</span>
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
                        <textarea name="body" rows="5" placeholder="Write your message here…"
                            class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-800 dark:text-slate-100 font-medium text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all leading-relaxed resize-none" required>{{ old('body') }}</textarea>
                        @error('body') <p class="text-xs text-rose-500 mt-1 font-semibold">{{ $message }}</p> @enderror
                    </div>

                    {{-- CTA --}}
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
