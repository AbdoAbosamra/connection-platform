<?php

use App\Broadcasting\ConversationChannel;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

/*
| Broadcast channel authorization. The callback returns true to grant a user
| access to a private channel. These mirror the REST-layer policies so realtime
| can't leak data the API wouldn't.
*/

// Per-user channel — used by the notification bell. Default Laravel notification
// broadcast channel is "App.Models.User.{id}".
Broadcast::channel('App.Models.User.{id}', function (User $user, int $id) {
    return $user->id === $id;
});

// A conversation thread — only its two participants (or an admin) may listen.
// Delegates to the unit-testable ConversationChannel class.
Broadcast::channel('conversation.{conversation}', ConversationChannel::class);
