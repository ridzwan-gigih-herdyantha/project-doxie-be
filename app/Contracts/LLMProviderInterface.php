<?php

namespace App\Contracts;

interface LLMProviderInterface
{
    public function stream(string $systemPrompt, array $messages, callable $onToken): string;
}
