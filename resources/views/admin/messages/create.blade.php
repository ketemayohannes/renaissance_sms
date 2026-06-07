<x-admin-layout header="Compose Message">
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.messages.index') }}"
           class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-500 hover:bg-slate-200 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h2 class="text-xl font-black text-slate-800 dark:text-slate-100">New Conversation</h2>
            <p class="text-xs text-slate-400 font-semibold mt-0.5">Search and select a recipient to start a conversation</p>
        </div>
    </div>

    <div x-data="{
        /* ── Data ── */
        allUsers: @js($users),
        roles:    @js($roles->pluck('name')->prepend('All')),

        /* ── Filter state ── */
        activeRole: 'All',
        search: '',

        /* ── Selection ── */
        selectedUserId: '{{ old('recipient_id', '') }}',

        /* ── Computed ── */
        get filteredUsers() {
            return this.allUsers.filter(u => {
                const matchRole   = this.activeRole === 'All' || u.role === this.activeRole;
                const q           = this.search.toLowerCase().trim();
                const matchSearch = !q || u.name.toLowerCase().includes(q) || u.email.toLowerCase().includes(q);
                return matchRole && matchSearch;
            });
        },
        get selectedUser() {
            return this.allUsers.find(u => u.id == this.selectedUserId) || null;
        },
        get roleCounts() {
            const counts = { All: this.allUsers.length };
            this.allUsers.forEach(u => {
                counts[u.role] = (counts[u.role] || 0) + 1;
            });
            return counts;
        },
        get initial() {
            return this.selectedUser ? this.selectedUser.name.charAt(0).toUpperCase() : '';
        },
        avatarColor(name) {
            const colors = [
                'bg-indigo-500','bg-violet-500','bg-purple-500','bg-rose-500',
                'bg-emerald-500','bg-amber-500','bg-sky-500','bg-pink-500',
                'bg-teal-500','bg-orange-500',
            ];
            let hash = 0;
            for (let i = 0; i < name.length; i++) hash = name.charCodeAt(i) + ((hash << 5) - hash);
            return colors[Math.abs(hash) % colors.length];
        },
        selectUser(user) {
            this.selectedUserId = user.id;
        }
    }" class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        {{-- ── LEFT PANEL: Recipient Picker ─────────────────────────────────── --}}
        <div class="xl:col-span-2 space-y-4">
            <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">

                {{-- Panel header --}}
                <div class="px-5 pt-5 pb-4 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="text-sm font-black text-slate-700 dark:text-slate-200 mb-3">Select Recipient</h3>

                    {{-- Search bar --}}
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input
                            x-model="search"
                            type="text"
                            id="recipient-search"
                            placeholder="Search by name or email..."
                            class="w-full pl-10 pr-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-slate-100 placeholder-slate-400 font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all"
                        >
                        <button x-show="search" @click="search=''" type="button"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Role filter tabs --}}
                <div class="px-5 pt-3 pb-0 flex gap-2 flex-wrap border-b border-slate-100 dark:border-slate-800">
                    <template x-for="role in roles" :key="role">
                        <button
                            type="button"
                            @click="activeRole = role; search = ''"
                            :class="activeRole === role
                                ? 'bg-indigo-600 text-white border-indigo-600 shadow-sm'
                                : 'bg-transparent text-slate-500 border-slate-200 dark:border-slate-700 hover:border-indigo-400 hover:text-indigo-600'"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 mb-3 rounded-lg border text-xs font-bold transition-all">
                            <span x-text="role"></span>
                            <span
                                :class="activeRole === role ? 'bg-white/20 text-white' : 'bg-slate-100 dark:bg-slate-700 text-slate-500'"
                                class="px-1.5 py-0.5 rounded-md text-[10px] font-black"
                                x-text="roleCounts[role] || 0">
                            </span>
                        </button>
                    </template>
                </div>

                {{-- User list --}}
                <div class="overflow-y-auto" style="max-height: 340px;">
                    {{-- No results --}}
                    <div x-show="filteredUsers.length === 0" class="flex flex-col items-center justify-center py-12 text-slate-400">
                        <svg class="w-10 h-10 mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <p class="text-sm font-semibold">No users found</p>
                        <p class="text-xs mt-1 opacity-70">Try a different search or role filter</p>
                    </div>

                    {{-- User rows --}}
                    <template x-for="user in filteredUsers" :key="user.id">
                        <button
                            type="button"
                            @click="selectUser(user)"
                            :class="selectedUserId == user.id
                                ? 'bg-indigo-50 dark:bg-indigo-900/20 border-l-4 border-indigo-500'
                                : 'border-l-4 border-transparent hover:bg-slate-50 dark:hover:bg-slate-800/50'"
                            class="w-full flex items-center gap-3 px-5 py-3 text-left transition-all group">

                            {{-- Avatar --}}
                            <div class="w-9 h-9 rounded-xl flex items-center justify-center text-white text-xs font-black flex-shrink-0 transition-transform group-hover:scale-105"
                                 :class="avatarColor(user.name)"
                                 x-text="user.name.charAt(0).toUpperCase()">
                            </div>

                            {{-- Name + email --}}
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold text-slate-800 dark:text-slate-100 truncate" x-text="user.name"></p>
                                <p class="text-xs text-slate-400 truncate" x-text="user.email"></p>
                            </div>

                            {{-- Role badge --}}
                            <span class="flex-shrink-0 px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wide bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-300"
                                  x-text="user.role">
                            </span>

                            {{-- Check icon when selected --}}
                            <div x-show="selectedUserId == user.id" class="flex-shrink-0 w-5 h-5 bg-indigo-600 rounded-full flex items-center justify-center">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                        </button>
                    </template>
                </div>

                {{-- Footer count --}}
                <div class="px-5 py-2.5 border-t border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50">
                    <p class="text-xs text-slate-400 font-semibold">
                        Showing <span class="text-indigo-600 font-black" x-text="filteredUsers.length"></span>
                        of <span class="font-black text-slate-500" x-text="allUsers.length"></span> users
                    </p>
                </div>
            </div>
        </div>

        {{-- ── RIGHT PANEL: Selected user preview + form ───────────────────── --}}
        <div class="xl:col-span-1 space-y-4">

            {{-- Selected user card --}}
            <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl overflow-hidden shadow-sm">
                <div class="h-16 bg-gradient-to-r from-indigo-500 via-violet-500 to-purple-500"></div>
                <div class="px-5 pb-5 relative">
                    <div class="absolute -top-6 left-5">
                        <template x-if="selectedUser">
                            <div class="w-12 h-12 rounded-xl text-white flex items-center justify-center font-black text-lg border-4 border-white dark:border-slate-900 shadow-lg uppercase"
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
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 mb-1.5"
                                      x-text="selectedUser.role">
                                </span>
                                <h3 class="text-sm font-black text-slate-800 dark:text-slate-100" x-text="selectedUser.name"></h3>
                                <p class="text-xs text-slate-500 mt-0.5 truncate" x-text="selectedUser.email"></p>
                            </div>
                        </template>
                        <template x-if="!selectedUser">
                            <p class="text-xs text-slate-400 font-semibold leading-relaxed">← Select a recipient from the list to start a conversation.</p>
                        </template>
                    </div>
                </div>
            </div>

            {{-- Compose form --}}
            <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
                @if(session('success'))
                    <div class="mb-4 px-4 py-3 bg-emerald-50 text-emerald-700 rounded-xl text-sm font-bold">{{ session('success') }}</div>
                @endif

                <form method="POST" action="{{ route('admin.messages.store') }}" class="space-y-4">
                    @csrf

                    {{-- Hidden recipient id --}}
                    <input type="hidden" name="recipient_id" :value="selectedUserId">

                    {{-- Validation error for recipient --}}
                    @error('recipient_id')
                        <p class="text-xs text-rose-500 font-bold">{{ $message }}</p>
                    @enderror

                    <div>
                        <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-1.5">
                            Subject / Thread Name <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="subject" value="{{ old('subject') }}"
                            placeholder="What is this about?"
                            class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-800 dark:text-slate-100 font-semibold text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all" required>
                        @error('subject') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-1.5">
                            Message <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="body" rows="5" placeholder="Write your message here..."
                            class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-800 dark:text-slate-100 font-medium text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all leading-relaxed resize-none" required>{{ old('body') }}</textarea>
                        @error('body') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <button type="submit"
                        :disabled="!selectedUserId"
                        class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-700 hover:to-violet-700 text-white font-bold rounded-xl shadow-md hover:shadow-lg transition-all text-sm disabled:opacity-40 disabled:cursor-not-allowed">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                        <span x-text="selectedUser ? 'Send to ' + selectedUser.name.split(' ')[0] : 'Send Message'"></span>
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>
</x-admin-layout>
