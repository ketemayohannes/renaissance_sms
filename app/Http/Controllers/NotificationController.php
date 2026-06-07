<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /** Paginated list of all notifications for the authenticated user. */
    public function index()
    {
        $notifications = auth()->user()
            ->notifications()
            ->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    /** Mark a single notification as read; returns JSON for AJAX or redirects. */
    public function markRead(string $id, Request $request)
    {
        $notification = auth()->user()
            ->notifications()
            ->findOrFail($id);

        $notification->markAsRead();

        if ($request->expectsJson()) {
            return response()->json(['status' => 'ok']);
        }

        // Redirect to the notification's action URL if provided
        $url = $notification->data['url'] ?? route('dashboard');
        return redirect($url);
    }

    /** Mark every unread notification as read. */
    public function markAllRead(Request $request)
    {
        auth()->user()->unreadNotifications()->update(['read_at' => now()]);

        if ($request->expectsJson()) {
            return response()->json(['status' => 'ok']);
        }

        return back()->with('success', 'All notifications marked as read.');
    }

    /** AJAX endpoint — returns unread count for the bell icon. */
    public function count()
    {
        return response()->json([
            'count' => auth()->user()->unreadNotifications()->count(),
        ]);
    }

    /** AJAX endpoint — returns the 6 latest notifications for the dropdown panel. */
    public function latest()
    {
        $items = auth()->user()
            ->notifications()
            ->take(6)
            ->get()
            ->map(fn($n) => [
                'id'         => $n->id,
                'type'       => $n->data['type']    ?? 'info',
                'title'      => $n->data['title']   ?? 'Notification',
                'body'       => $n->data['body']    ?? '',
                'url'        => $n->data['url']     ?? '#',
                'read'       => !is_null($n->read_at),
                'time'       => $n->created_at->diffForHumans(),
            ]);

        return response()->json($items);
    }

    /** Delete all notifications for the authenticated user. */
    public function clearAll(Request $request)
    {
        auth()->user()->notifications()->delete();

        if ($request->expectsJson()) {
            return response()->json(['status' => 'ok']);
        }

        return back()->with('success', 'All notifications cleared.');
    }

    /** Fetch all messages for the given conversation (JSON). */
    public function fetchMessages(\App\Models\Conversation $conversation)
    {
        $user = auth()->user();
        $conversation->loadMissing('participants');
        abort_unless($conversation->participants->contains('id', $user->id), 403);

        $messages = $conversation->messages()
            ->with('sender')
            ->get()
            ->map(fn($m) => [
                'id'             => $m->id,
                'body'           => $m->body,
                'sender_id'      => $m->sender_id,
                'sender_initial' => substr($m->sender->name ?? '?', 0, 1),
                'time'           => $m->created_at->format('M d, g:i A'),
            ]);

        // Mark unread messages as read
        $unread = $conversation->messages
            ->where('sender_id', '!=', $user->id)
            ->filter(fn($m) => !$m->isReadBy($user));

        foreach ($unread as $message) {
            \App\Models\MessageRead::firstOrCreate(
                ['message_id' => $message->id, 'user_id' => $user->id],
                ['read_at' => now()]
            );
        }

        // ── Auto-dismiss bell notifications for this conversation ──────────
        // If the user is actively viewing the chat, any NewMessageReceived
        // notifications for this conversation should be silently marked read
        // so the badge doesn't appear while the chat is already open.
        $user->unreadNotifications()
            ->where('type', \App\Notifications\NewMessageReceived::class)
            ->get()
            ->filter(fn($n) => ($n->data['conversation_id'] ?? null) == $conversation->id)
            ->each->markAsRead();

        return response()->json($messages);
    }

    /** AJAX endpoint to send a reply. */
    public function ajaxReply(Request $request, \App\Models\Conversation $conversation)
    {
        $user = auth()->user();
        $conversation->loadMissing('participants');
        abort_unless($conversation->participants->contains('id', $user->id), 403);

        $validated = $request->validate([
            'body' => 'required|string|max:3000',
        ]);

        $message = \App\Models\Message::create([
            'conversation_id' => $conversation->id,
            'sender_id'       => $user->id,
            'body'            => $validated['body'],
        ]);

        $conversation->touch();

        // Notify other participants after the response is sent — keeps AJAX reply fast
        $participantsToNotify = $conversation->participants->filter(fn($p) => $p->id !== $user->id);
        app()->terminating(function () use ($participantsToNotify, $conversation, $user) {
            foreach ($participantsToNotify as $participant) {
                $participant->notify(new \App\Notifications\NewMessageReceived($conversation, $user));
            }
        });

        return response()->json([
            'status'  => 'ok',
            'message' => [
                'id'             => $message->id,
                'body'           => $message->body,
                'sender_id'      => $message->sender_id,
                'sender_initial' => substr($user->name, 0, 1),
                'time'           => $message->created_at->format('M d, g:i A'),
            ]
        ]);
    }
}
