<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Message;
use App\Models\MessageRead;
use App\Notifications\NewMessageReceived;
use App\Models\StudentGuardian;
use App\Services\TeacherService;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

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

        return view('teacher.messages.index', compact('conversations'));
    }

    public function show(Conversation $conversation)
    {
        $user = auth()->user();
        $this->authorizeConversation($conversation, $user);

        $conversation->load(['messages.sender', 'participants']);
        $this->markAsRead($conversation, $user);

        return view('teacher.messages.show', compact('conversation', 'user'));
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

        return redirect()->route('teacher.messages.show', $conversation)
            ->with('success', 'Reply sent.');
    }

    public function create(Request $request)
    {
        $user = auth()->user();
        $section = app(TeacherService::class)->getHomeroomSection($user);

        if (!$section) {
            return redirect()->route('teacher.messages.index')
                ->with('error', 'You must be a Homeroom Teacher to initiate conversations with parents.');
        }

        // Fetch active students in the homeroom section
        $students = $section->enrollments()
            ->where('status', 'active')
            ->with(['student.user', 'student.guardians.user'])
            ->get()
            ->pluck('student');

        // Extract guardians with active portal accounts
        $recipients = [];
        foreach ($students as $student) {
            foreach ($student->guardians as $guardian) {
                if ($guardian->user) {
                    $recipients[] = [
                        'guardian_user_id' => $guardian->user->id,
                        'guardian_name'    => $guardian->full_name,
                        'student_name'     => $student->user->name,
                        'relationship'     => $guardian->relationship ?? 'Guardian',
                    ];
                }
            }
        }

        $recipients = collect($recipients)->unique('guardian_user_id')->values();

        // Check if there is a pre-selected recipient from URL
        $preselectedId = $request->query('recipient_id');

        return view('teacher.messages.create', compact('recipients', 'section', 'preselectedId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'recipient_id' => 'required|exists:users,id|different:' . auth()->id(),
            'subject'      => 'required|string|max:255',
            'body'         => 'required|string|max:3000',
        ]);

        $user = auth()->user();
        $section = app(TeacherService::class)->getHomeroomSection($user);

        if (!$section) {
            return redirect()->route('teacher.messages.index')
                ->with('error', 'You must be a Homeroom Teacher to initiate conversations.');
        }

        $recipientId = $validated['recipient_id'];

        // Secure IDOR check: Verify the recipient is a guardian of an active student in the teacher's homeroom section
        $studentGuardian = StudentGuardian::with('student.user')
            ->where('user_id', $recipientId)
            ->whereIn('student_id', function ($query) use ($section) {
                $query->select('student_id')
                    ->from('student_enrollments')
                    ->where('section_id', $section->id)
                    ->where('status', 'active');
            })
            ->first();

        abort_unless($studentGuardian, 403, 'You can only message parents of active students in your homeroom class.');

        $studentName = $studentGuardian->student->user->name;
        $recipient   = \App\Models\User::findOrFail($recipientId);

        $conversation = DB::transaction(function () use ($user, $recipient, $validated, $studentName) {
            $conv = Conversation::create([
                'type'       => 'private',
                'name'       => $validated['subject'] . ' — ' . $studentName,
                'created_by' => $user->id,
            ]);

            ConversationParticipant::insert([
                ['conversation_id' => $conv->id, 'user_id' => $user->id,        'joined_at' => now(), 'created_at' => now(), 'updated_at' => now()],
                ['conversation_id' => $conv->id, 'user_id' => $recipient->id, 'joined_at' => now(), 'created_at' => now(), 'updated_at' => now()],
            ]);

            Message::create([
                'conversation_id' => $conv->id,
                'sender_id'       => $user->id,
                'body'            => $validated['body'],
            ]);

            return $conv;
        });

        // Notify parent
        $recipient->notify(new NewMessageReceived($conversation, $user));

        return redirect()->route('teacher.messages.show', $conversation)
            ->with('success', 'Message sent successfully.');
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
