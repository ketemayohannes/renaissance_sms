<x-admin-layout header="Messaging Inbox">
    <div class="space-y-6">
        {{-- Header --}}
        <div class="relative bg-gradient-to-r from-indigo-600 via-violet-600 to-purple-700 rounded-3xl p-6 lg:p-8 text-white overflow-hidden shadow-lg">
            <div class="absolute -right-10 -top-10 w-40 h-40 bg-white/10 rounded-full blur-xl"></div>
            <div class="absolute -left-10 -bottom-10 w-40 h-40 bg-purple-500/10 rounded-full blur-xl"></div>
            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <span class="text-indigo-200 text-xs font-bold tracking-wider uppercase">Admin Communications</span>
                    <h2 class="text-2xl lg:text-3xl font-black font-heading tracking-tight leading-none mt-1">In-App Messages</h2>
                    <p class="text-indigo-100 text-sm pt-1">Direct private messages with parents, teachers, and school staff</p>
                </div>
                <a href="{{ route('admin.messages.create') }}" class="px-5 py-2.5 bg-white text-indigo-600 hover:bg-indigo-50 rounded-xl text-sm font-semibold transition-all shadow-md hover:shadow-lg flex items-center gap-2 self-start md:self-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    New Message
                </a>
            </div>
        </div>

        {{-- Conversation list --}}
        <div class="space-y-3">
            @forelse($conversations as $conv)
            @php $hasUnread = $conv->unread_count > 0; @endphp
            <a href="{{ route('admin.messages.show', $conv) }}"
               class="group flex items-center gap-4 bg-white dark:bg-slate-900 border {{ $hasUnread ? 'border-indigo-200 dark:border-indigo-800' : 'border-slate-100 dark:border-slate-800' }} rounded-2xl p-4 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200">
                <div class="w-12 h-12 rounded-2xl {{ $hasUnread ? 'bg-indigo-600' : 'bg-slate-200 dark:bg-slate-700' }} flex items-center justify-center text-white font-black text-sm flex-shrink-0">
                    {{ substr($conv->other?->name ?? '?', 0, 1) }}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-sm font-{{ $hasUnread ? 'black' : 'bold' }} text-slate-800 dark:text-slate-100 truncate">
                            {{ $conv->other?->name ?? 'Unknown' }}
                        </span>
                        <span class="text-[10px] font-bold text-slate-400 flex-shrink-0">
                            {{ $conv->preview?->created_at->diffForHumans() ?? $conv->created_at->diffForHumans() }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-semibold truncate mt-0.5">{{ $conv->name }}</p>
                    <p class="text-xs text-slate-400 truncate mt-0.5">{{ Str::limit($conv->preview?->body ?? 'No messages yet', 80) }}</p>
                </div>
                @if($hasUnread)
                <span class="w-5 h-5 bg-indigo-600 rounded-full text-white text-[10px] font-black flex items-center justify-center flex-shrink-0">
                    {{ $conv->unread_count }}
                </span>
                @endif
            </a>
            @empty
            <div class="p-12 text-center bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl">
                <svg class="w-16 h-16 text-slate-200 dark:text-slate-700 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
                <h3 class="text-lg font-bold text-slate-800 dark:text-slate-200">No conversations yet</h3>
                <p class="text-slate-500 dark:text-slate-400 mt-1 text-sm">You haven't started or received any messages yet. Click "New Message" to begin.</p>
            </div>
            @endforelse
        </div>
    </div>
</x-admin-layout>
