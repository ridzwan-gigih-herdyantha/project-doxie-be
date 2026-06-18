<?php

namespace App\Services;

use App\Models\ChatSession;
use App\Models\Document;

class ChatSessionService
{
    public function getByDocument(int $documentId, int $userId)
    {
        return ChatSession::where('document_id', $documentId)
            ->where('user_id', $userId)
            ->latest()
            ->get();
    }

    public function getByUser(int $userId)
    {
        return ChatSession::where('user_id', $userId)
            ->latest()
            ->get();
    }

    public function create(Document $document, int $userId): ChatSession
    {
        return ChatSession::create([
            'user_id' => $userId,
            'document_id' => $document->id,
        ]);
    }
}
