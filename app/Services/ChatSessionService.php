<?php

namespace App\Services;

use App\Models\ChatSession;
use App\Models\Document;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ChatSessionService
{
    public function getByDocument(int $documentId, int $userId, int $perPage = 10): LengthAwarePaginator
    {
        return ChatSession::with('document:id,uuid')
            ->where('document_id', $documentId)
            ->where('user_id', $userId)
            ->orderByLastActivity()
            ->paginate($perPage);
    }

    public function getByUser(int $userId, int $perPage = 10): LengthAwarePaginator
    {
        return ChatSession::with('document:id,uuid')
            ->where('user_id', $userId)
            ->orderByLastActivity()
            ->paginate($perPage);
    }

    public function create(Document $document, int $userId): ChatSession
    {
        $chatSession = ChatSession::create([
            'user_id' => $userId,
            'document_id' => $document->id,
        ]);

        return $chatSession->setRelation('document', $document);
    }
}
