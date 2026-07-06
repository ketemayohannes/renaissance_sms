<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Message;
use App\Models\MessageRead;
use App\Models\Student;
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
            ->get();

        // Batch the unread counts into one query to avoid an N+1 across the inbox.
        $unreadCounts = Conversation::unreadCountsFor($user, $conversations->pluck('id'));

        $conversations = $conversations
            ->map(function ($conv) use ($user, $unreadCounts) {
                $conv->unread_count = (int) ($unreadCounts[$conv->id] ?? 0);
                $conv->other        = $conv->otherParticipant($user);
                $conv->preview      = $conv->messages->first();
                return $conv;
            })
            ->sortByDesc(fn($c) => $c->preview?->created_at ?? $c->created_at);

        return view('parent.messages.index', compact('conversations'));
    }

    public function create()
    {
        $user     = auth()->user();
        $children = $user->linked_students->load([
            'currentEnrollment.section.homeroomTeacher',
            'currentEnrollment.section.gradeLevel',
        ]);
        return view('parent.messages.create', compact('children'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'subject'    => 'required|string|max:255',
            'body'       => 'required|string|max:3000',
        ]);

        $user = auth()->user();

        // IDOR guard — parent must own this child
        $isLinked = $user->guardianProfiles()
            ->where('student_id', $validated['student_id'])
            ->exists();
        abort_unless($isLinked, 403, 'Unauthorized access.');

        $student = Student::with('currentEnrollment.section.homeroomTeacher')
            ->findOrFail($validated['student_id']);
        $teacher     = $student->currentEnrollment?->section?->homeroomTeacher ?? null;

        abort_unless($teacher, 422, 'No homeroom teacher assigned to this student\'s section.');

        // homeroomTeacher() is belongsTo(User::class) so $teacher IS already the User
        $teacherUser = $teacher;
        abort_unless($teacherUser, 422, 'Teacher does not have a portal account yet.');

        $conversation = DB::transaction(function () use ($user, $teacherUser, $validated, $student) {
            $conv = Conversation::create([
                'type'       => 'private',
                'name'       => $validated['subject'] . ' — ' . $student->full_name,
                'created_by' => $user->id,
            ]);

            ConversationParticipant::insert([
                ['conversation_id' => $conv->id, 'user_id' => $user->id,        'joined_at' => now(), 'created_at' => now(), 'updated_at' => now()],
                ['conversation_id' => $conv->id, 'user_id' => $teacherUser->id, 'joined_at' => now(), 'created_at' => now(), 'updated_at' => now()],
            ]);

            Message::create([
                'conversation_id' => $conv->id,
                'sender_id'       => $user->id,
                'body'            => $validated['body'],
            ]);

            return $conv;
        });

        // Notify teacher
        $teacherUser->notify(new NewMessageReceived($conversation, $user));

        return redirect()->route('parent.messages.show', $conversation)
            ->with('success', 'Message sent successfully.');
    }

    public function show(Conversation $conversation)
    {
        $user = auth()->user();
        $this->authorizeConversation($conversation, $user);

        $conversation->load(['messages.sender', 'participants']);

        // Mark all messages from others as read
        $this->markAsRead($conversation, $user);

        return view('parent.messages.show', compact('conversation', 'user'));
    }

    public function reply(Request $request, Conversation $conversation)
    {
        $user = auth()->user();
        $this->authorizeConversation($conversation, $user);

        $request->validate(['body' => 'required|string|max:3000']);

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id'       => $user->id,
            'body'            => $request->body,
        ]);

        $conversation->touch(); // update updated_at so inbox sort works

        // Notify other participants
        foreach ($conversation->participants as $participant) {
            if ($participant->id !== $user->id) {
                $participant->notify(new NewMessageReceived($conversation, $user));
            }
        }

        return redirect()->route('parent.messages.show', $conversation)
            ->with('success', 'Reply sent.');
    }

    public function unreadCount()
    {
        $user = auth()->user();
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
