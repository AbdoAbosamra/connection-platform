<?php

namespace App\Policies;

use App\Models\Conversation;
use App\Models\User;

class ConversationPolicy
{
    public function view(User $user, Conversation $conversation): bool
    {
        if ($user->isAdmin()) return true;

        if ($user->isEmployer()) {
            return $conversation->employer->user_id === $user->id;
        }

        if ($user->isJobSeeker()) {
            return $conversation->jobSeeker->user_id === $user->id;
        }

        return false;
    }
}
