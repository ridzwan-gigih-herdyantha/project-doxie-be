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

    public function index(Request $request, Document $document): JsonResponse
    {
        abort_if($document->user_id !== $request->user()->id, 403);

        $conversations = $this->chatsessionService->getByDocument($document->id, $request->user()->id);

        return $this->successResponse($conversations);
    }

    public function store(Request $request, Document $document): JsonResponse
    {
        abort_if($document->user_id !== $request->user()->id, 403);
        abort_if(! $document->isReady(), 422, 'Document is still processing.');

        $conversation = $this->chatsessionService->create($document, $request->user()->id);

        return $this->successResponse($conversation, 'Conversation created successfully', 201);
    }
}
