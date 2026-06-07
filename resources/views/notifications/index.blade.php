@php
    $layout = 'admin-layout';
    if (auth()->user()->hasRole('Teacher')) {
        $layout = 'teacher-layout';
    } elseif (auth()->user()->hasRole('Parent')) {
        $layout = 'parent-layout';
    } elseif (auth()->user()->hasRole('Student')) {
        $layout = 'student-layout';
    }
@endphp

<x-dynamic-component :component="$layout" header="All Notifications">
    <div class="space-y-6 max-w-4xl mx-auto">
        {{-- Header Card --}}
        <div class="relative bg-gradient-to-r from-slate-800 to-slate-900 dark:from-slate-900 dark:to-black rounded-3xl p-6 lg:p-8 text-white overflow-hidden shadow-lg">
            <div class="absolute -right-10 -top-10 w-40 h-40 bg-white/5 rounded-full blur-xl"></div>
            <div class="absolute -left-10 -bottom-10 w-40 h-40 bg-slate-700/10 rounded-full blur-xl"></div>
            <div class="relative z-10 flex flex-col sm:flex-row sm:items-center justify-between gap-6">
                <div>
                    <span class="text-slate-400 text-xs font-bold tracking-wider uppercase">Updates Center</span>
                    <h2 class="text-2xl lg:text-3xl font-black font-heading tracking-tight leading-none mt-1">Notifications</h2>
                    <p class="text-slate-300 text-sm pt-1">All your automated system alerts, message notifications, and reports in one place</p>
                </div>
                
                @if(auth()->user()->unreadNotifications->count() > 0)
                <form method="POST" action="{{ route('notifications.mark-all') }}">
                    @csrf
                    <button type="submit" class="px-5 py-2.5 bg-white/10 hover:bg-white/20 border border-white/10 text-white rounded-xl text-xs font-bold uppercase tracking-wider transition-all shadow-md">
                        Mark All Read
                    </button>
                </form>
                @endif
            </div>
        </div>

        {{-- Notifications List --}}
        <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl overflow-hidden shadow-sm divide-y divide-slate-100 dark:divide-slate-800">
            @forelse($notifications as $n)
                @php 
                    $isRead = !is_null($n->read_at);
                    $type = $n->data['type'] ?? 'info';
                @endphp
                <div class="p-6 transition-all duration-200 relative {{ $isRead ? 'opacity-70' : 'bg-indigo-50/10 dark:bg-indigo-950/5' }}">
                    <div class="flex items-start gap-4">
                        <!-- Type Icon -->
                        <div class="w-10 h-10 rounded-xl flex-shrink-0 flex items-center justify-center"
                             :class="{
                                'bg-blue-50 text-blue-600 dark:bg-blue-950/30 dark:text-blue-400': '{{ $type }}' === 'message' || '{{ $type }}' === 'info',
                                'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/30 dark:text-emerald-400': '{{ $type }}' === 'success' || '{{ $type }}' === 'notice',
                                'bg-rose-50 text-rose-600 dark:bg-rose-950/30 dark:text-rose-450': '{{ $type }}' === 'danger' || '{{ $type }}' === 'absent',
                                'bg-amber-50 text-amber-600 dark:bg-amber-950/30 dark:text-amber-400': '{{ $type }}' === 'warning'
                             }">
                            @if($type === 'message')
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                </svg>
                            @elseif($type === 'notice')
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                                </svg>
                            @else
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                            @endif
                        </div>

                        <!-- Content -->
                        <div class="flex-1 min-w-0 space-y-1">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 sm:gap-4">
                                <h3 class="text-sm font-black text-slate-800 dark:text-slate-100 font-heading">
                                    {{ $n->data['title'] ?? 'Notification' }}
                                </h3>
                                <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider whitespace-nowrap">
                                    {{ $n->created_at->diffForHumans() }}
                                </span>
                            </div>
                            <p class="text-xs text-slate-500 dark:text-slate-450 leading-relaxed font-semibold">
                                {{ $n->data['body'] ?? '' }}
                            </p>
                        </div>

                        <!-- Action Button -->
                        <div class="flex-shrink-0 self-center">
                            <a href="{{ route('notifications.read', $n->id) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-750 border border-slate-100 dark:border-slate-800 text-slate-600 dark:text-slate-300 rounded-xl text-xs font-black transition-all">
                                View
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-12 text-center text-slate-400 dark:text-slate-500">
                    <svg class="w-16 h-16 mx-auto mb-4 text-slate-200 dark:text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <h3 class="text-lg font-bold text-slate-800 dark:text-slate-200 font-heading uppercase tracking-wider">No notifications yet</h3>
                    <p class="text-sm text-slate-400 mt-1">When the system alerts you (e.g. absent reports or new messages), they will appear here.</p>
                </div>
            @endforelse
        </div>

        {{-- Pagination Links --}}
        <div class="pt-4">
            {{ $notifications->links() }}
        </div>
    </div>
</x-dynamic-component>
