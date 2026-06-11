<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Services\ChatSessionService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatSessionController extends Controller
{
    use ApiResponseTrait;

    public function __construct(private readonly ChatSessionService $chatsessionService) {}

    /**
     * @OA\Get(
     *     path="/api/documents/{document}/session",
     *     summary="List chat sessions for a document",
     *     tags={"Chat Sessions"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="document",
     *         in="path",
     *         required=true,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(response=200, description="List of chat sessions"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function index(Request $request, Document $document): JsonResponse
    {
        abort_if($document->user_id !== $request->user()->id, 403);

        $conversations = $this->chatsessionService->getByDocument($document->id, $request->user()->id);

        return $this->successResponse($conversations);
    }

    /**
     * @OA\Post(
     *     path="/api/documents/{document}/session",
     *     summary="Create a new chat session for a document",
     *     tags={"Chat Sessions"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="document",
     *         in="path",
     *         required=true,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(response=201, description="Conversation created successfully"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=422, description="Document is still processing")
     * )
     */
    public function store(Request $request, Document $document): JsonResponse
    {
        abort_if($document->user_id !== $request->user()->id, 403);
        abort_if(! $document->isReady(), 422, 'Document is still processing.');

        $conversation = $this->chatsessionService->create($document, $request->user()->id);

        return $this->successResponse($conversation, 'Conversation created successfully', 201);
    }
}
