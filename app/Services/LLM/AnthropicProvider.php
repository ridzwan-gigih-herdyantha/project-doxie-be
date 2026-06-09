<?php

namespace App\Services\LLM;

use App\Contracts\LLMProviderInterface;
use GuzzleHttp\Client;

class AnthropicProvider implements LLMProviderInterface
{
    public function __construct(private readonly string $model) {}

    public function stream(string $systemPrompt, array $messages, callable $onToken): string
    {
        $client = new Client;
        $response = $client->post('https://api.anthropic.com/v1/messages', [
            'headers' => [
                'x-api-key' => config('services.anthropic.key'),
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ],
            'json' => [
                'model' => $this->model,
                'max_tokens' => 1024,
                'system' => $systemPrompt,
                'messages' => $messages,
                'stream' => true,
            ],
            'stream' => true,
        ]);

        $body = $response->getBody();
        $fullResponse = '';
        $buffer = '';

        while (! $body->eof()) {
            $buffer .= $body->read(1024);

            while (($pos = strpos($buffer, "\n")) !== false) {
                $line = substr($buffer, 0, $pos);
                $buffer = substr($buffer, $pos + 1);

                if (str_starts_with($line, 'data: ')) {
                    $data = json_decode(substr($line, 6), true);

                    if (
                        isset($data['type']) &&
                        $data['type'] === 'content_block_delta'
                    ) {
                        $token = $data['delta']['text'] ?? '';
                        $fullResponse .= $token;
                        $onToken($token);
                    }
                }
            }
        }

        return $fullResponse;
    }
}
