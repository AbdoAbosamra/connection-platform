<?php

namespace App\Services;

use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\EmployerProfile;
use App\Models\Job;
use App\Models\JobSeekerProfile;
use App\Models\Message;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MessageService
{
    // ──────────────────────────────────────────────────────────────────────────
    // Conversations
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Find or create a conversation between an employer and a job seeker.
     *
     * WHY NOT firstOrCreate() with null:
     * MySQL unique constraints treat NULL as distinct (NULL != NULL), so
     * firstOrCreate(['job_id' => null]) generates WHERE job_id = NULL which
     * matches zero rows and always tries to INSERT — potentially creating
     * duplicates. We use explicit whereNull() to avoid this.
     */
    public function getOrCreateConversation(
        EmployerProfile $employer,
        JobSeekerProfile $seeker,
        ?Job $job = null
    ): Conversation {
        $query = Conversation::where('employer_profile_id', $employer->id)
            ->where('job_seeker_profile_id', $seeker->id);

        if ($job) {
            $query->where('job_id', $job->id);
        } else {
            $query->whereNull('job_id');
        }

        $existing = $query->first();
        if ($existing) {
            return $existing;
        }

        return Conversation::create([
            'employer_profile_id' => $employer->id,
            'job_seeker_profile_id' => $seeker->id,
            'job_id' => $job?->id,
        ]);
    }

    /**
     * Returns paginated conversations for the given user.
     *
     * PERFORMANCE:
     *  - Eager-loads all relations needed by the card in one shot.
     *  - Includes a correlated subquery for unread_count per conversation,
     *    so the list renders badges without N+1 round-trips.
     *  - Ordered by last_message_at DESC (index: conv_employer_recent /
     *    conv_seeker_recent).
     *
     * @return LengthAwarePaginator<Conversation>
     */
    public function getConversationsForUser(User $user, int $perPage = 20): LengthAwarePaginator
    {
        $profileId = $user->isEmployer()
            ? $user->employerProfile?->id
            : $user->jobSeekerProfile?->id;

        $column = $user->isEmployer() ? 'employer_profile_id' : 'job_seeker_profile_id';

        return Conversation::where($column, $profileId)
            // withCount + alias + closure = correlated subquery with no N+1.
            // Generates: (SELECT count(*) FROM messages WHERE conversation_id = conversations.id
            //              AND sender_id != ? AND read_at IS NULL) as unread_count
            ->withCount([
                'messages as unread_count' => fn ($q) => $q
                    ->where('sender_id', '!=', $user->id)
                    ->whereNull('read_at'),
            ])
            ->with([
                'latestMessage',                         // preview text
                'latestMessage.sender:id,name,avatar',   // sender name for preview
                'employer:id,company_name,logo',
                'jobSeeker:id,user_id,headline',
                'jobSeeker.user:id,name,avatar',
                'job:id,title',
            ])
            ->orderByDesc('last_message_at')
            ->paginate($perPage);
    }

    /**
     * Total number of unread messages across ALL of the user's conversations.
     * Used for the nav badge.
     */
    public function totalUnreadForUser(User $user): int
    {
        $profileId = $user->isEmployer()
            ? $user->employerProfile?->id
            : $user->jobSeekerProfile?->id;

        $column = $user->isEmployer() ? 'employer_profile_id' : 'job_seeker_profile_id';

        return Message::whereHas('conversation', fn ($q) => $q->where($column, $profileId))
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->count();
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Messages
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Sends a message inside a conversation.
     *
     * TRANSACTIONAL: message creation + conversation pointer update are atomic.
     * If anything fails mid-way, neither is committed.
     *
     * ATTACHMENT: if an UploadedFile is provided it is stored in
     * storage/app/public/attachments/{conversation_id}/ with a unique name.
     * The stored relative path is saved; callers should use Storage::url() to
     * build the public URL.
     *
     * @param  array{body: string, attachment?: UploadedFile|null}  $data
     */
    public function sendMessage(
        Conversation $conversation,
        User $sender,
        array $data
    ): Message {
        $message = DB::transaction(function () use ($conversation, $sender, $data) {
            $attachmentPath = null;

            if (!empty($data['attachment']) && $data['attachment'] instanceof UploadedFile) {
                $attachmentPath = $data['attachment']->store(
                    "attachments/{$conversation->id}",
                    'public'
                );
            }

            $message = Message::create([
                'conversation_id' => $conversation->id,
                'sender_id' => $sender->id,
                // body is NOT NULL at the DB level; coerce null (attachment-only
                // messages) to an empty string.
                'body' => $data['body'] ?? '',
                'attachment' => $attachmentPath,
            ]);

            $conversation->update([
                'last_message_at' => now(),
                'last_message_id' => $message->id,
            ]);

            return $message->load('sender:id,name,avatar');
        });

        // Broadcast after commit so subscribers never see an uncommitted message.
        // No-op unless a real broadcaster (Reverb) is configured.
        MessageSent::dispatch($message);

        return $message;
    }

    /**
     * Mark all unread messages in a conversation as read for the given reader.
     * Only marks messages NOT sent by the reader (you never "unread" your own).
     * Uses a single bulk UPDATE — no N+1.
     */
    public function markConversationRead(Conversation $conversation, User $reader): void
    {
        Message::where('conversation_id', $conversation->id)
            ->where('sender_id', '!=', $reader->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    /**
     * Paginate messages in a conversation, oldest first.
     * Eager-loads sender name/avatar for display.
     * Adds attachment_url accessor so the frontend gets a ready-to-use URL.
     */
    public function getMessages(Conversation $conversation, int $perPage = 30): LengthAwarePaginator
    {
        return $conversation->messages()
            ->with('sender:id,name,avatar')
            ->orderBy('created_at')
            ->paginate($perPage);
    }
}
