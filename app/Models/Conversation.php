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
     * Get the other participant (for private chats).
     */
    public function otherParticipant(User $user): ?User
    {
        return $this->participants->firstWhere('id', '!=', $user->id);
    }
}
