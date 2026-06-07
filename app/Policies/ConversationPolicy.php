<?php

namespace App\Policies;

use App\Models\Conversation;
use App\Models\User;

class ConversationPolicy
{
    /** Any participant can view the conversation. */
    public function view(User $user, Conversation $conversation): bool
    {
        return $conversation->participants->contains('id', $user->id);
    }

    /** Any participant can reply. */
    public function reply(User $user, Conversation $conversation): bool
    {
        return $conversation->participants->contains('id', $user->id);
    }
}
