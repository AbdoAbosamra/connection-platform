<?php

namespace App\Http\Controllers\Api\Employer;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Job;
use App\Models\JobSeekerProfile;
use App\Services\MessageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function __construct(private MessageService $messaging) {}

    // ── GET /employer/conversations ───────────────────────────────────────────

    public function conversations(Request $request): JsonResponse
    {
        $conversations = $this->messaging->getConversationsForUser($request->user());

        return response()->json($conversations);
    }

    // ── GET /employer/conversations/unread-count ──────────────────────────────

    public function unreadCount(Request $request): JsonResponse
    {
        $count = $this->messaging->totalUnreadForUser($request->user());

        return response()->json(['unread' => $count]);
    }

    // ── GET /employer/conversations/{conversation}/messages ───────────────────

    public function messages(Request $request, Conversation $conversation): JsonResponse
    {
        $this->authorize('view', $conversation);

        // Mark as read before returning so the badge drops immediately
        $this->messaging->markConversationRead($conversation, $request->user());

        $messages = $this->messaging->getMessages($conversation, 30);

        return response()->json($messages);
    }

    // ── POST /employer/conversations/{conversation}/messages ──────────────────

    public function send(Request $request, Conversation $conversation): JsonResponse
    {
        // Use `send` policy — admins are excluded from sending
        $this->authorize('send', $conversation);

        $request->validate([
            // A message needs text OR an attachment (attachment-only is allowed).
            'body' => ['nullable', 'required_without:attachment', 'string', 'max:5000'],
            // Accepted MIME types + size limit (10 MB). Covers the main file types:
            // PDF, Word, images, plain text.  Exec-dangerous types are blocked by
            // the MIME check (no .exe, .php, .sh, etc.).
            'attachment' => [
                'nullable',
                'file',
                'max:10240',
                'mimes:pdf,doc,docx,txt,png,jpg,jpeg,gif,webp',
            ],
        ]);

        // FIX: attachment is now correctly passed through to the service.
        $message = $this->messaging->sendMessage($conversation, $request->user(), [
            'body' => $request->input('body'),
            'attachment' => $request->file('attachment'),
        ]);

        return response()->json(['message' => $message], 201);
    }

    // ── POST /employer/conversations ──────────────────────────────────────────

    public function initiate(Request $request): JsonResponse
    {
        $request->validate([
            'job_seeker_profile_id' => ['required', 'integer', 'exists:job_seeker_profiles,id'],
            // FIX: job_id now validated and used — employers can open job-specific threads
            'job_id' => ['nullable', 'integer', 'exists:jobs,id'],
        ]);

        $employer = $request->user()->employerProfile;

        abort_unless($employer, 403, 'Employer profile not found.');

        $seeker = JobSeekerProfile::findOrFail($request->job_seeker_profile_id);

        // FIX: job_id is now resolved and passed to the service
        $job = $request->filled('job_id')
            ? Job::findOrFail($request->job_id)
            : null;

        // Security: ensure the job belongs to this employer if supplied
        if ($job && $job->employer_profile_id !== $employer->id) {
            abort(403, 'You can only initiate conversations for your own job listings.');
        }

        $conversation = $this->messaging->getOrCreateConversation($employer, $seeker, $job);

        // Set relations directly from already-loaded models — avoids an extra DB
        // round-trip and the fragile latestMessage.sender chain on a new conversation.
        $seeker->loadMissing('user:id,name,avatar');
        $conversation->setRelation('jobSeeker', $seeker);
        $conversation->setRelation('job', $job);           // null is fine
        $conversation->setRelation('latestMessage', null); // brand-new thread
        $conversation->unread_count = 0;                   // no messages yet

        return response()->json(['conversation' => $conversation]);
    }

    // ── POST /employer/conversations/{conversation}/read ──────────────────────

    public function markRead(Request $request, Conversation $conversation): JsonResponse
    {
        $this->authorize('view', $conversation);
        $this->messaging->markConversationRead($conversation, $request->user());

        return response()->json(['ok' => true]);
    }
}
