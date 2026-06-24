<?php

namespace App\Http\Controllers\Api\JobSeeker;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Services\MessageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function __construct(private MessageService $messaging) {}

    // ── GET /job-seeker/conversations ─────────────────────────────────────────

    public function conversations(Request $request): JsonResponse
    {
        $conversations = $this->messaging->getConversationsForUser($request->user());

        return response()->json($conversations);
    }

    // ── GET /job-seeker/conversations/unread-count ────────────────────────────

    public function unreadCount(Request $request): JsonResponse
    {
        $count = $this->messaging->totalUnreadForUser($request->user());

        return response()->json(['unread' => $count]);
    }

    // ── GET /job-seeker/conversations/{conversation}/messages ─────────────────

    public function messages(Request $request, Conversation $conversation): JsonResponse
    {
        $this->authorize('view', $conversation);

        $this->messaging->markConversationRead($conversation, $request->user());

        $messages = $this->messaging->getMessages($conversation, 30);

        return response()->json($messages);
    }

    // ── POST /job-seeker/conversations/{conversation}/messages ────────────────

    public function send(Request $request, Conversation $conversation): JsonResponse
    {
        // Use `send` policy — job seeker must be a participant
        $this->authorize('send', $conversation);

        $request->validate([
            // A message needs text OR an attachment (attachment-only is allowed).
            'body' => ['nullable', 'required_without:attachment', 'string', 'max:5000'],
            'attachment' => [
                'nullable',
                'file',
                'max:10240',
                'mimes:pdf,doc,docx,txt,png,jpg,jpeg,gif,webp',
            ],
        ]);

        $message = $this->messaging->sendMessage($conversation, $request->user(), [
            'body' => $request->input('body'),
            'attachment' => $request->file('attachment'),
        ]);

        return response()->json(['message' => $message], 201);
    }

    // ── POST /job-seeker/conversations/{conversation}/read ────────────────────

    public function markRead(Request $request, Conversation $conversation): JsonResponse
    {
        $this->authorize('view', $conversation);
        $this->messaging->markConversationRead($conversation, $request->user());

        return response()->json(['ok' => true]);
    }
}
