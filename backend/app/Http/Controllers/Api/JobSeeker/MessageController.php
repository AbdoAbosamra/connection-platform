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

    public function conversations(Request $request): JsonResponse
    {
        $conversations = $this->messaging->getConversationsForUser($request->user());
        return response()->json($conversations);
    }

    public function messages(Request $request, Conversation $conversation): JsonResponse
    {
        $this->authorize('view', $conversation);
        $this->messaging->markConversationRead($conversation, $request->user());

        $messages = $conversation->messages()
            ->with('sender:id,name,avatar')
            ->paginate(30);

        return response()->json($messages);
    }

    public function send(Request $request, Conversation $conversation): JsonResponse
    {
        $this->authorize('view', $conversation);

        $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $message = $this->messaging->sendMessage($conversation, $request->user(), [
            'body' => $request->body,
        ]);

        return response()->json(['message' => $message], 201);
    }
}
