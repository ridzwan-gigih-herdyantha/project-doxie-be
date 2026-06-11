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

    /**
     * @OA\Get(
     *     path="/api/session/{chatSession}/messages",
     *     summary="List messages in a chat session",
     *     tags={"Messages"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="chatSession",
     *         in="path",
     *         required=true,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(response=200, description="List of messages"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function index(Request $request, ChatSession $chatSession): JsonResponse
    {
        abort_if($chatSession->user_id !== $request->user()->id, 403);

        $messages = $chatSession->messages()->orderBy('created_at')->get();

        return $this->successResponse($messages);
    }

    /**
     * @OA\Post(
     *     path="/api/session/{chatSession}/messages",
     *     summary="Send a question and stream the AI answer (SSE)",
     *     description="Returns a text/event-stream. Each event is `data: {""content"": ""...""}` and the stream ends with `data: [DONE]`.",
     *     tags={"Messages"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="chatSession",
     *         in="path",
     *         required=true,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"question", "model"},
     *
     *             @OA\Property(property="question", type="string", example="What is this document about?"),
     *             @OA\Property(property="model", type="string", enum={"gpt-4o", "claude-3-5-sonnet-20241022", "gemini-2.5-flash"}, example="gpt-4o")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Server-sent events stream of the answer",
     *
     *         @OA\MediaType(mediaType="text/event-stream")
     *     ),
     *
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(Request $request, ChatSession $chatSession): StreamedResponse
    {
        abort_if($chatSession->user_id !== $request->user()->id, 403);

        $request->validate([
            'question' => 'required|string',
            'model' => 'required|in:gpt-4o,claude-3-5-sonnet-20241022,gemini-2.5-flash',
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
