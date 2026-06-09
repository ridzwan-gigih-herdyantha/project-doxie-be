<?php

namespace App\Services\LLM;

use App\Contracts\LLMProviderInterface;
use OpenAI\Laravel\Facades\OpenAI;

class OpenAIProvider implements LLMProviderInterface
{
    public function __construct(private readonly string $model) {}

    public function stream(string $systemPrompt, array $messages, callable $onToken): string
    {
        $stream = OpenAI::chat()->createStreamed([
            'model' => $this->model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ...$messages,
            ],
        ]);

        $fullResponse = '';

        foreach ($stream as $response) {
            $token = $response->choices[0]->delta->content ?? '';
            if ($token !== '') {
                $fullResponse .= $token;
                $onToken($token);
            }
        }

        return $fullResponse;
    }
}
