<x-teacher-layout header="Messages">
<div class="space-y-6">
    {{-- Header --}}
    <div class="relative bg-gradient-to-r from-emerald-600 via-teal-600 to-cyan-700 rounded-3xl p-6 lg:p-8 text-white overflow-hidden shadow-lg">
        <div class="absolute -right-10 -top-10 w-40 h-40 bg-white/10 rounded-full blur-xl"></div>
        <div class="absolute -left-10 -bottom-10 w-40 h-40 bg-teal-500/10 rounded-full blur-xl"></div>
        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <span class="text-emerald-200 text-xs font-bold tracking-wider uppercase">Your Inbox</span>
                <h2 class="text-2xl lg:text-3xl font-black font-heading tracking-tight leading-none mt-1">Messages</h2>
                <p class="text-emerald-100 text-sm pt-1">Messages from parents in your classes</p>
            </div>
            @if(app(\App\Services\TeacherService::class)->isHomeroomTeacher(auth()->user()))
            <a href="{{ route('teacher.messages.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-white text-emerald-700 hover:bg-emerald-50 rounded-xl font-bold shadow-md transition-all text-sm self-start sm:self-auto flex-shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
                Compose Message
            </a>
            @endif
        </div>
    </div>

    {{-- Conversation list --}}
    @if(session('error'))
        <div class="px-4 py-3 bg-rose-50 text-rose-700 rounded-xl text-sm font-bold border border-rose-100 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            {{ session('error') }}
        </div>
    @endif
    @if(session('success'))
        <div class="px-4 py-3 bg-emerald-50 text-emerald-700 rounded-xl text-sm font-bold border border-emerald-100 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    <div class="space-y-3">
        @forelse($conversations as $conv)
        @php $hasUnread = $conv->unread_count > 0; @endphp
        <a href="{{ route('teacher.messages.show', $conv) }}"
           class="group flex items-center gap-4 bg-white dark:bg-slate-900 border {{ $hasUnread ? 'border-emerald-200 dark:border-emerald-800' : 'border-slate-100 dark:border-slate-800' }} rounded-2xl p-4 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200">
            <div class="w-12 h-12 rounded-2xl {{ $hasUnread ? 'bg-emerald-600' : 'bg-slate-200 dark:bg-slate-700' }} flex items-center justify-center text-white font-black text-sm flex-shrink-0">
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
                <p class="text-xs text-slate-400 truncate mt-0.5">{{ Str::limit($conv->preview?->body ?? 'No messages yet', 60) }}</p>
            </div>
            @if($hasUnread)
            <span class="w-5 h-5 bg-emerald-600 rounded-full text-white text-[10px] font-black flex items-center justify-center flex-shrink-0">
                {{ $conv->unread_count }}
            </span>
            @endif
        </a>
        @empty
        <div class="p-12 text-center bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl">
            <svg class="w-16 h-16 text-slate-200 dark:text-slate-700 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
            <h3 class="text-lg font-bold text-slate-800 dark:text-slate-200">No messages yet</h3>
            <p class="text-slate-500 dark:text-slate-400 mt-1 text-sm">Parent messages will appear here once they reach out.</p>
        </div>
        @endforelse
    </div>
</div>
</x-teacher-layout>
