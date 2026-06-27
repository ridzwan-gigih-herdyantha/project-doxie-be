<?php

namespace App\Services;

use App\Models\ChatSession;
use App\Services\LLM\LLMFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use OpenAI\Laravel\Facades\OpenAI;

class ChatService
{
    private const TITLE_MODEL = 'gpt-4o-mini';

    public function __construct(
        private readonly EmbeddingService $embeddingService
    ) {}

    public function stream(ChatSession $chatSession, string $question, string $model, callable $onToken): string
    {
        $queryEmbedding = $this->embeddingService->generate($question);
        $vector = $this->embeddingService->formatForPgvector($queryEmbedding);

        $relevantChunks = DB::select(
            'SELECT content, page_number FROM document_chunks WHERE document_id = ? ORDER BY embedding <=> ?::vector LIMIT 5',
            [$chatSession->document_id, $vector]
        );

        $context = collect($relevantChunks)
            ->map(fn ($chunk) => "[Page {$chunk->page_number}]\n{$chunk->content}")
            ->implode("\n\n");

        $systemPrompt = "You are a helpful assistant. Answer questions based only on the following document context.
If the answer is not found in the context, say you don't know. Always respond in the same language as the user's question, including when you don't know the answer.
When relevant, cite the page number(s) the answer comes from.

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

    public function generateTitle(ChatSession $chatSession): void
    {
        if ($chatSession->title !== null) {
            return;
        }

        $conversation = $chatSession->messages()
            ->orderBy('created_at')
            ->limit(2)
            ->get()
            ->map(fn ($msg) => ucfirst($msg->role).': '.$msg->content)
            ->implode("\n\n");

        if ($conversation === '') {
            return;
        }

        $response = OpenAI::chat()->create([
            'model' => self::TITLE_MODEL,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'Create a short, descriptive title for the conversation below. '
                                .'Return only the title, without quotes or explanation. '
                                .'Maximum 6 words. '
                                .'Use the dominant language of the conversation. '
                                .'Avoid generic titles; summarize the main topic.',
                ],
                ['role' => 'user', 'content' => $conversation],
            ],
        ]);

        $title = trim($response->choices[0]->message->content ?? '');

        if ($title !== '') {
            $chatSession->update(['title' => Str::limit($title, 100, '')]);
        }
    }
}
