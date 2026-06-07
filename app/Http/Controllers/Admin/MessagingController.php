<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Message;
use App\Models\MessageRead;
use App\Models\User;
use App\Notifications\NewMessageReceived;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MessagingController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $conversations = $user->conversations()
            ->with(['participants', 'messages' => fn($q) => $q->latest()->limit(1)])
            ->get()
            ->map(function ($conv) use ($user) {
                $conv->unread_count = $conv->unreadCountFor($user);
                $conv->other        = $conv->otherParticipant($user);
                $conv->preview      = $conv->messages->first();
                return $conv;
            })
            ->sortByDesc(fn($c) => $c->preview?->created_at ?? $c->created_at);

        return view('admin.messages.index', compact('conversations'));
    }

    public function create()
    {
        // Admin can message any user with a portal account
        $users = User::whereHas('roles')
            ->where('id', '!=', auth()->id())
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return view('admin.messages.create', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'recipient_id' => 'required|exists:users,id|different:' . auth()->id(),
            'subject'      => 'required|string|max:255',
            'body'         => 'required|string|max:3000',
        ]);

        $authUser  = auth()->user();
        $recipient = User::findOrFail($validated['recipient_id']);

        $conversation = DB::transaction(function () use ($authUser, $recipient, $validated) {
            $conv = Conversation::create([
                'type'       => 'private',
                'name'       => $validated['subject'],
                'created_by' => $authUser->id,
            ]);

            ConversationParticipant::insert([
                ['conversation_id' => $conv->id, 'user_id' => $authUser->id,   'joined_at' => now(), 'created_at' => now(), 'updated_at' => now()],
                ['conversation_id' => $conv->id, 'user_id' => $recipient->id, 'joined_at' => now(), 'created_at' => now(), 'updated_at' => now()],
            ]);

            Message::create([
                'conversation_id' => $conv->id,
                'sender_id'       => $authUser->id,
                'body'            => $validated['body'],
            ]);

            return $conv;
        });

        $recipient->notify(new NewMessageReceived($conversation, $authUser));

        return redirect()->route('admin.messages.show', $conversation)
            ->with('success', 'Message sent successfully.');
    }

    public function show(Conversation $conversation)
    {
        $user = auth()->user();
        $this->authorizeConversation($conversation, $user);

        $conversation->load(['messages.sender', 'participants']);
        $this->markAsRead($conversation, $user);

        return view('admin.messages.show', compact('conversation', 'user'));
    }

    public function reply(Request $request, Conversation $conversation)
    {
        $user = auth()->user();
        $this->authorizeConversation($conversation, $user);

        $request->validate(['body' => 'required|string|max:3000']);

        Message::create([
            'conversation_id' => $conversation->id,
            'sender_id'       => $user->id,
            'body'            => $request->body,
        ]);

        $conversation->touch();

        foreach ($conversation->participants as $participant) {
            if ($participant->id !== $user->id) {
                $participant->notify(new NewMessageReceived($conversation, $user));
            }
        }

        return redirect()->route('admin.messages.show', $conversation)
            ->with('success', 'Reply sent.');
    }

    public function unreadCount()
    {
        $user  = auth()->user();
        $count = Message::whereIn('conversation_id',
                    ConversationParticipant::where('user_id', $user->id)->pluck('conversation_id')
                 )
                 ->where('sender_id', '!=', $user->id)
                 ->whereDoesntHave('reads', fn($q) => $q->where('user_id', $user->id))
                 ->count();

        return response()->json(['count' => $count]);
    }

    // ─── Private ──────────────────────────────────────────────────────

    private function authorizeConversation(Conversation $conv, $user): void
    {
        abort_unless(
            $conv->participants->contains('id', $user->id),
            403,
            'You are not a participant in this conversation.'
        );
    }

    private function markAsRead(Conversation $conv, $user): void
    {
        $unread = $conv->messages
            ->where('sender_id', '!=', $user->id)
            ->filter(fn($m) => !$m->isReadBy($user));

        foreach ($unread as $message) {
            MessageRead::firstOrCreate(
                ['message_id' => $message->id, 'user_id' => $user->id],
                ['read_at' => now()]
            );
        }
    }
}
