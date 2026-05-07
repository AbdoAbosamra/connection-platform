<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\EmployerProfile;
use App\Models\Job;
use App\Models\JobSeekerProfile;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class MessageService
{
    public function getOrCreateConversation(
        EmployerProfile $employer,
        JobSeekerProfile $seeker,
        ?Job $job = null
    ): Conversation {
        return Conversation::firstOrCreate([
            'employer_profile_id'   => $employer->id,
            'job_seeker_profile_id' => $seeker->id,
            'job_id'                => $job?->id,
        ]);
    }

    public function sendMessage(Conversation $conversation, User $sender, array $data): Message
    {
        return DB::transaction(function () use ($conversation, $sender, $data) {
            $message = Message::create([
                'conversation_id' => $conversation->id,
                'sender_id'       => $sender->id,
                'body'            => $data['body'],
                'attachment'      => $data['attachment'] ?? null,
            ]);

            $conversation->update([
                'last_message_at' => now(),
                'last_message_id' => $message->id,
            ]);

            return $message->load('sender:id,name,avatar');
        });
    }

    public function markConversationRead(Conversation $conversation, User $reader): void
    {
        Message::where('conversation_id', $conversation->id)
               ->whereNot('sender_id', $reader->id)
               ->whereNull('read_at')
               ->update(['read_at' => now()]);
    }

    public function getConversationsForUser(User $user, int $perPage = 20)
    {
        $profileId = $user->isEmployer()
            ? $user->employerProfile?->id
            : $user->jobSeekerProfile?->id;

        $column = $user->isEmployer() ? 'employer_profile_id' : 'job_seeker_profile_id';

        return Conversation::where($column, $profileId)
            ->with([
                'latestMessage',
                'employer:id,company_name,logo',
                'jobSeeker.user:id,name,avatar',
                'job:id,title',
            ])
            ->orderByDesc('last_message_at')
            ->paginate($perPage);
    }
}
