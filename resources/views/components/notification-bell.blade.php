<div class="relative" 
     x-data="{ 
        open: false, 
        count: {{ auth()->user() ? auth()->user()->unreadNotificationsCount() : 0 }}, 
        prevCount: {{ auth()->user() ? auth()->user()->unreadNotificationsCount() : 0 }},
        notifications: [], 
        loading: false,
        requestBrowserPermission() {
            if ('Notification' in window && Notification.permission === 'default') {
                Notification.requestPermission();
            }
        },
        showBrowserNotif(title, body, url) {
            if ('Notification' in window && Notification.permission === 'granted' && document.hidden) {
                const n = new Notification(title, {
                    body: body,
                    icon: '/favicon.ico',
                    tag: 'renaissance-sms',
                    renotify: true,
                });
                n.onclick = function() {
                    window.focus();
                    if (url) window.location.href = url;
                    n.close();
                };
            }
        },
        fetchCount() {
            fetch('{{ route('notifications.count') }}')
                .then(res => res.json())
                .then(data => {
                    const newCount = data.count;
                    if (newCount > this.prevCount) {
                        // New notification arrived — fetch latest to get title/body
                        fetch('{{ route('notifications.latest') }}')
                            .then(r => r.json())
                            .then(items => {
                                if (items.length > 0) {
                                    const latest = items[0];
                                    this.showBrowserNotif(
                                        latest.title,
                                        latest.body,
                                        latest.url
                                    );
                                }
                            });
                    }
                    this.prevCount = newCount;
                    this.count = newCount;
                });
        },
        fetchLatest() {
            this.loading = true;
            fetch('{{ route('notifications.latest') }}')
                .then(res => res.json())
                .then(data => {
                    this.notifications = data;
                    this.loading = false;
                })
                .catch(err => {
                    console.error(err);
                    this.loading = false;
                });
        },
        markAllRead() {
            fetch('{{ route('notifications.mark-all') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'ok') {
                    this.count = 0;
                    this.prevCount = 0;
                    this.notifications.forEach(n => n.read = true);
                }
            });
        },
        clearAll() {
            fetch('{{ route('notifications.clear-all') }}', {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'ok') {
                    this.count = 0;
                    this.prevCount = 0;
                    this.notifications = [];
                }
            });
        },
        toggle() {
            this.open = !this.open;
            if (this.open) {
                this.fetchLatest();
            }
        }
     }"
     x-init="requestBrowserPermission(); fetchCount(); setInterval(() => fetchCount(), 15000);"
     @click.outside="open = false">

     
    <!-- Bell Button -->
    <button @click="toggle()" 
            class="relative p-2 rounded-xl text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-700 dark:hover:text-slate-200 transition-all duration-200 focus:outline-none"
            aria-label="Notifications">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        <!-- Unread Badge -->
        <span x-show="count > 0" class="absolute top-1 right-1 flex h-4 w-4">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-4 w-4 bg-rose-500 text-[9px] font-black text-white items-center justify-center" x-text="count"></span>
        </span>
    </button>

    <!-- Dropdown Panel -->
    <div x-cloak 
         x-show="open" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-2"
         class="absolute right-0 mt-3 w-80 md:w-96 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-850 rounded-2xl shadow-xl overflow-hidden z-[110]">
         
        <!-- Header -->
        <div class="px-4 py-3 bg-slate-50 dark:bg-slate-800/50 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <span class="text-xs font-bold text-slate-800 dark:text-slate-200 font-heading uppercase tracking-wider">Notifications</span>
            <button x-show="count > 0" 
                    @click="markAllRead()" 
                    class="text-[10px] font-bold text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 transition-colors uppercase tracking-wider focus:outline-none">
                Mark all read
            </button>
        </div>

        <!-- Notification list -->
        <div class="max-h-80 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-800 custom-scrollbar">
            <template x-if="loading">
                <div class="p-6 text-center text-slate-400">
                    <div class="inline-block animate-spin w-5 h-5 border-2 border-indigo-600 border-t-transparent rounded-full mr-2"></div>
                    <span class="text-xs font-semibold uppercase tracking-wider">Loading...</span>
                </div>
            </template>

            <template x-if="!loading && notifications.length === 0">
                <div class="p-8 text-center text-slate-400 dark:text-slate-500">
                    <svg class="w-10 h-10 mx-auto mb-2 text-slate-300 dark:text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <p class="text-xs font-bold uppercase tracking-widest">All caught up!</p>
                    <p class="text-[10px] text-slate-400 mt-0.5">No notifications to show.</p>
                </div>
            </template>

            <template x-for="item in notifications" :key="item.id">
                <a :href="'/notifications/' + item.id + '/read'"
                   class="block p-4 hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors duration-150 relative"
                   :class="item.read ? 'opacity-75' : 'bg-indigo-50/20 dark:bg-indigo-950/10'">
                    <!-- Unread indicator dot -->
                    <template x-if="!item.read">
                        <span class="absolute top-4 right-4 w-2 h-2 rounded-full bg-indigo-600 dark:bg-indigo-400"></span>
                    </template>
                    
                    <div class="flex gap-3">
                        <!-- Type Icon -->
                        <div class="w-8 h-8 rounded-lg flex-shrink-0 flex items-center justify-center"
                             :class="{
                                'bg-blue-50 text-blue-600 dark:bg-blue-950/30 dark:text-blue-400': item.type === 'message' || item.type === 'info',
                                'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/30 dark:text-emerald-400': item.type === 'success' || item.type === 'notice',
                                'bg-rose-50 text-rose-600 dark:bg-rose-950/30 dark:text-rose-450': item.type === 'danger' || item.type === 'absent',
                                'bg-amber-50 text-amber-600 dark:bg-amber-950/30 dark:text-amber-400': item.type === 'warning'
                             }">
                            <template x-if="item.type === 'message'">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                </svg>
                            </template>
                            <template x-if="item.type === 'notice'">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                                </svg>
                            </template>
                            <template x-if="item.type !== 'message' && item.type !== 'notice'">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                            </template>
                        </div>
                        
                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-bold text-slate-800 dark:text-slate-200 truncate" x-text="item.title"></p>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 line-clamp-2 mt-0.5" x-text="item.body"></p>
                            <p class="text-[9px] text-slate-400 dark:text-slate-500 mt-1 font-semibold" x-text="item.time"></p>
                        </div>
                    </div>
                </a>
            </template>
        </div>

        <!-- Footer -->
        <div class="grid grid-cols-2 divide-x divide-slate-100 dark:divide-slate-800 border-t border-slate-100 dark:border-slate-850">
            <a href="{{ route('notifications.index') }}" 
               class="block py-2.5 text-center bg-slate-50 dark:bg-slate-800/50 hover:bg-slate-100 dark:hover:bg-slate-800 text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 transition-colors uppercase tracking-wider">
                View all
            </a>
            <button @click="clearAll()" 
                    class="block py-2.5 w-full text-center bg-slate-50 dark:bg-slate-800/50 hover:bg-slate-100 dark:hover:bg-slate-800 text-xs font-bold text-rose-600 dark:text-rose-450 hover:text-rose-850 transition-colors uppercase tracking-wider focus:outline-none">
                Clear all
            </button>
        </div>
    </div>
</div>
