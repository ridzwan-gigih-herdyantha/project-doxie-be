<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatSession;
use App\Models\Message;
use App\Services\ChatService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MessageController extends Controller
{
    use ApiResponseTrait;

    public function __construct(private readonly ChatService $chatService) {}

    public function index(Request $request, ChatSession $chatSession): JsonResponse
    {
        abort_if($chatSession->user_id !== $request->user()->id, 403);

        $messages = $chatSession->messages()->orderBy('created_at')->get();

        return $this->successResponse($messages);
    }

    public function store(Request $request, ChatSession $chatSession): StreamedResponse
    {
        abort_if($chatSession->user_id !== $request->user()->id, 403);

        $request->validate([
            'question' => 'required|string',
            'model' => 'required|in:gpt-4o,claude-3-5-sonnet-20241022,gemini-1.5-flash',
        ]);

        Message::create([
            'chat_session_id' => $chatSession->id,
            'role' => 'user',
            'content' => $request->question,
        ]);

        return response()->stream(function () use ($chatSession, $request) {

            $fullResponse = $this->chatService->stream(
                chatSession: $chatSession,
                question: $request->question,
                model: $request->model,
                onToken: function (string $token) {
                    echo 'data: '.json_encode(['content' => $token])."\n\n";
                    ob_flush();
                    flush();
                }
            );

            Message::create([
                'chat_session_id' => $chatSession->id,
                'role' => 'assistant',
                'content' => $fullResponse,
                'model_used' => $request->model,
            ]);

            echo "data: [DONE]\n\n";
            ob_flush();
            flush();

        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
        ]);
    }
}
