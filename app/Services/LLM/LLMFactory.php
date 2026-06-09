<?php

namespace App\Services\LLM;

use App\Contracts\LLMProviderInterface;
use InvalidArgumentException;

class LLMFactory
{
    public static function make(string $model): LLMProviderInterface
    {
        return match (true) {
            str_starts_with($model, 'gpt') => new OpenAIProvider($model),
            str_starts_with($model, 'claude') => new AnthropicProvider($model),
            str_starts_with($model, 'gemini') => new GeminiProvider($model),
            default => throw new InvalidArgumentException("Unsupported model: {$model}"),
        };
    }
}
