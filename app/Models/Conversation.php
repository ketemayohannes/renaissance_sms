<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Conversation extends Model
{
    protected $fillable = ['type', 'name', 'created_by'];

    // ─── Relationships ────────────────────────────────────────────────

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'conversation_participants')
                    ->withPivot(['is_admin', 'joined_at'])
                    ->withTimestamps();
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at');
    }

    public function lastMessage(): HasMany
    {
        return $this->hasMany(Message::class)->latest()->limit(1);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ─── Scopes ───────────────────────────────────────────────────────

    /**
     * Only return conversations that a given user participates in.
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->whereHas('participants', fn ($q) => $q->where('users.id', $userId));
    }

    // ─── Helpers ─────────────────────────────────────────────────────

    /**
     * Count unread messages for a given user in this conversation.
     */
    public function unreadCountFor(User $user): int
    {
        return $this->messages()
            ->where('sender_id', '!=', $user->id)
            ->whereDoesntHave('reads', fn ($q) => $q->where('user_id', $user->id))
            ->count();
    }

    /**
     * Unread message counts for a user across many conversations in ONE query,
     * returned as [conversation_id => count]. Use this for inbox lists instead
     * of calling unreadCountFor() per conversation (which is an N+1).
     *
     * @param  iterable  $conversationIds
     */
    public static function unreadCountsFor(User $user, $conversationIds): \Illuminate\Support\Collection
    {
        $ids = collect($conversationIds);

        if ($ids->isEmpty()) {
            return collect();
        }

        return Message::whereIn('conversation_id', $ids)
            ->where('sender_id', '!=', $user->id)
            ->whereDoesntHave('reads', fn ($q) => $q->where('user_id', $user->id))
            ->groupBy('conversation_id')
            ->selectRaw('conversation_id, COUNT(*) as aggregate')
            ->pluck('aggregate', 'conversation_id');
    }

    /**
     * Get the other participant (for private chats).
     */
    public function otherParticipant(User $user): ?User
    {
        return $this->participants->firstWhere('id', '!=', $user->id);
    }
}
