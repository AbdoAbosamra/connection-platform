<?php

namespace App\Broadcasting;

use App\Models\Conversation;
use App\Models\User;

/**
 * Authorizes access to a conversation's realtime channel. Mirrors
 * ConversationPolicy::view so WebSocket access can never exceed REST access.
 *
 * Class-based so the rule is unit-testable in isolation (independent of the
 * configured broadcaster).
 */
class ConversationChannel
{
    public function join(User $user, Conversation $conversation): bool
    {
        if ($user->isAdmin()) {
            return true;
        }
        if ($user->isEmployer()) {
            return $conversation->employer?->user_id === $user->id;
        }
        if ($user->isJobSeeker()) {
            return $conversation->jobSeeker?->user_id === $user->id;
        }

        return false;
    }
}
