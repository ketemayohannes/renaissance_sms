<x-parent-layout header="Conversation">
    <div class="space-y-4 pb-6">
        <x-breadcrumb :items="[
            ['label' => 'Messages', 'url' => route('parent.messages.index')],
            ['label' => \Illuminate\Support\Str::limit($conversation->name, 40), 'url' => '#']
        ]" />

        {{-- Back + Thread Title --}}
        <div class="flex items-center gap-3">
            <a href="{{ route('parent.messages.index') }}" class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-500 hover:bg-slate-200 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h2 class="text-lg font-black text-slate-800 dark:text-slate-100 leading-tight">{{ $conversation->name }}</h2>
                <p class="text-xs text-slate-400 font-semibold">with {{ $conversation->otherParticipant($user)?->name ?? 'Unknown' }}</p>
            </div>
        </div>

        {{-- Messages thread --}}
        <div x-data="{
            messages: [],
            replyBody: '',
            userId: {{ $user->id }},
            userInitial: '{{ substr($user->name, 0, 1) }}',
            themeColor: 'bg-indigo-600',
            loading: false,
            init() {
                this.messages = @js($conversation->messages->map(fn($m) => [
                    'id'             => $m->id,
                    'body'           => $m->body,
                    'sender_id'      => $m->sender_id,
                    'sender_initial' => substr($m->sender->name ?? '?', 0, 1),
                    'time'           => $m->created_at->format('M d, g:i A'),
                ]));
                this.scrollToBottom();
                setInterval(() => this.pollMessages(), 3000);
            },
            scrollToBottom() {
                this.$nextTick(() => {
                    const el = document.getElementById('chat-thread');
                    if (el) el.scrollTop = el.scrollHeight;
                });
            },
            showBrowserNotif(title, body) {
                if ('Notification' in window && Notification.permission === 'granted' && document.hidden) {
                    const n = new Notification(title, {
                        body: body,
                        icon: '/favicon.ico',
                        tag: 'renaissance-chat-{{ $conversation->id }}',
                        renotify: true,
                    });
                    n.onclick = function() { window.focus(); n.close(); };
                }
            },
            pollMessages() {
                fetch('{{ route('conversations.messages', $conversation) }}')
                    .then(res => res.json())
                    .then(data => {
                        if (data.length !== this.messages.length) {
                            // Check if there's a new message from the other person
                            const newMsgs = data.slice(this.messages.length);
                            const incoming = newMsgs.filter(m => m.sender_id !== this.userId);
                            if (incoming.length > 0) {
                                this.showBrowserNotif(
                                    'New message in {{ addslashes($conversation->name) }}',
                                    incoming[incoming.length - 1].body
                                );
                            }
                            this.messages = data;
                            this.scrollToBottom();
                        }
                    });
            },
            submitReply() {
                if (!this.replyBody.trim()) return;
                this.loading = true;
                const bodyToSend = this.replyBody;
                this.replyBody = '';
                fetch('{{ route('conversations.reply', $conversation) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ body: bodyToSend })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'ok') {
                        this.messages.push(data.message);
                        this.scrollToBottom();
                    }
                    this.loading = false;
                })
                .catch(err => {
                    console.error(err);
                    this.loading = false;
                });
            }
        }" class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl overflow-hidden shadow-sm">
            
            {{-- Messages list --}}
            <div class="p-6 space-y-4 max-h-[55vh] overflow-y-auto" id="chat-thread">
                <template x-for="msg in messages" :key="msg.id">
                    <div class="flex gap-2" :class="msg.sender_id === userId ? 'justify-end' : 'justify-start'">
                        <template x-if="msg.sender_id !== userId">
                            <div class="w-8 h-8 rounded-xl text-white text-xs font-black flex items-center justify-center flex-shrink-0 mt-0.5"
                                 :class="themeColor">
                                <span x-text="msg.sender_initial"></span>
                            </div>
                        </template>
                        <div class="max-w-[75%] flex flex-col gap-1" :class="msg.sender_id === userId ? 'items-end' : 'items-start'">
                            <div class="px-4 py-3 rounded-2xl" 
                                 :class="msg.sender_id === userId 
                                     ? (themeColor + ' text-white rounded-br-sm')
                                     : 'bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-100 rounded-bl-sm'">
                                <p class="text-sm leading-relaxed font-medium" x-text="msg.body"></p>
                            </div>
                            <span class="text-[10px] text-slate-400 font-bold px-1" x-text="msg.time"></span>
                        </div>
                        <template x-if="msg.sender_id === userId">
                            <div class="w-8 h-8 rounded-xl bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300 text-xs font-black flex items-center justify-center flex-shrink-0 mt-0.5">
                                <span x-text="userInitial"></span>
                            </div>
                        </template>
                    </div>
                </template>
            </div>

            {{-- Reply box --}}
            <div class="border-t border-slate-100 dark:border-slate-800 p-4">
                <form @submit.prevent="submitReply()" class="flex gap-3">
                    <textarea x-model="replyBody" rows="2" placeholder="Write a reply..."
                        @keydown.enter.prevent="submitReply()"
                        class="flex-1 px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-slate-100 placeholder-slate-400 font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all resize-none" required></textarea>
                    <button type="submit" :disabled="loading" class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow transition-colors flex items-center gap-1.5 self-end text-sm disabled:opacity-50">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                        Send
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-parent-layout>
