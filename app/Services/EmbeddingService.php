<?php

namespace App\Services;

use OpenAI\Laravel\Facades\OpenAI;

class EmbeddingService
{
    private const MODEL = 'text-embedding-3-small';

    public function generate(string $text): array
    {
        return $this->generateBatch([$text])[0];
    }

    /**
     * Generate embeddings for multiple texts in a single request.
     *
     * @param  array<int, string>  $texts
     * @return array<int, array<int, float>>
     */
    public function generateBatch(array $texts): array
    {
        $response = OpenAI::embeddings()->create([
            'model' => self::MODEL,
            'input' => array_values($texts),
        ]);

        return collect($response->embeddings)
            ->sortBy('index')
            ->map(fn ($embedding) => $embedding->embedding)
            ->values()
            ->all();
    }

    public function formatForPgvector(array $embedding): string
    {
        return '['.implode(',', $embedding).']';
    }
}
