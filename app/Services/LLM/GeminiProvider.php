<?php

namespace App\Services\LLM;

use App\Contracts\LLMProviderInterface;
use Gemini\Data\Content;
use Gemini\Enums\Role;
use Gemini\Laravel\Facades\Gemini;

class GeminiProvider implements LLMProviderInterface
{
    public function __construct(private readonly string $model) {}

    public function stream(string $systemPrompt, array $messages, callable $onToken): string
    {
        $history = array_map(
            fn (array $msg) => Content::parse(
                part: $msg['content'],
                role: $msg['role'] === 'assistant' ? Role::MODEL : Role::USER,
            ),
            $messages,
        );

        // The last message is the new user question; the rest is prior history.
        $question = array_pop($history);

        $chat = Gemini::generativeModel($this->model)
            ->withSystemInstruction(Content::parse($systemPrompt))
            ->startChat(history: $history);

        $fullResponse = '';

        foreach ($chat->streamSendMessage($question) as $response) {
            if (empty($response->candidates)) {
                continue;
            }

            $token = ($response->candidates[0]->content->parts[0] ?? null)?->text ?? '';

            if ($token !== '') {
                $fullResponse .= $token;
                $onToken($token);
            }
        }

        return $fullResponse;
    }
}
