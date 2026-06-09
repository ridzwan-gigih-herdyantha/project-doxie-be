<?php

namespace App\Services;

use App\Models\ChatSession;
use App\Services\LLM\LLMFactory;
use Illuminate\Support\Facades\DB;

class ChatService
{
    public function __construct(
        private readonly EmbeddingService $embeddingService
    ) {}

    public function stream(ChatSession $chatSession, string $question, string $model, callable $onToken): string
    {
        $queryEmbedding = $this->embeddingService->generate($question);
        $vector = $this->embeddingService->formatForPgvector($queryEmbedding);

        $relevantChunks = DB::select(
            'SELECT content FROM document_chunks WHERE document_id = ? ORDER BY embedding <=> ?::vector LIMIT 5',
            [$chatSession->document_id, $vector]
        );

        $context = collect($relevantChunks)->pluck('content')->implode("\n\n");

        $systemPrompt = "You are a helpful assistant. Answer questions based only on the following document context.
If the answer is not found in the context, say you don't know.

Context:
{$context}";

        $messages = $chatSession->messages()
            ->orderBy('created_at')
            ->get()
            ->map(fn ($msg) => [
                'role' => $msg->role,
                'content' => $msg->content,
            ])
            ->toArray();

        $provider = LLMFactory::make($model);

        return $provider->stream($systemPrompt, $messages, $onToken);
    }
}
