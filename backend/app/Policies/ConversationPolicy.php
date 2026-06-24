<?php

namespace App\Policies;

use App\Models\Conversation;
use App\Models\User;

class ConversationPolicy
{
    /**
     * Can the user VIEW this conversation (read messages, load history)?
     * Admins have blanket access. Participants are verified by profile FK → user_id.
     * Null-safe: if either profile relation is missing, access is denied (not an exception).
     */
    public function view(User $user, Conversation $conversation): bool
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

    /**
     * Can the user SEND a message in this conversation?
     * Admins are excluded from sending (read-only oversight).
     * Both participants may send — same ownership check as view.
     */
    public function send(User $user, Conversation $conversation): bool
    {
        if ($user->isEmployer()) {
            return $conversation->employer?->user_id === $user->id;
        }

        if ($user->isJobSeeker()) {
            return $conversation->jobSeeker?->user_id === $user->id;
        }

        return false;
    }
}
