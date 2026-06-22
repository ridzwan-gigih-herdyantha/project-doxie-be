<?php

namespace App\Services;

use App\Events\DocumentReady;
use App\Models\Document;
use App\Models\DocumentChunk;
use Illuminate\Support\Facades\DB;
use Smalot\PdfParser\Parser;
use Yethee\Tiktoken\EncoderProvider;

class DocumentProcessingService
{

    private const TOKENIZER_MODEL = 'text-embedding-3-small';

    private const CHUNK_TOKENS = 800;

    private const CHUNK_OVERLAP_TOKENS = 100;

    public function __construct(
        private readonly EmbeddingService $embeddingService
    ) {}

    public function process(Document $document, string $pdfContent): void
    {
        $parser = new Parser;
        $pdf = $parser->parseContent($pdfContent);
        $text = $pdf->getText();

        $text = mb_convert_encoding(
            $text,
            'UTF-8',
            'UTF-8'
        );
        
        $text = iconv(
            'UTF-8',
            'UTF-8//IGNORE',
            $text
        );
        
        $text = preg_replace(
            '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u',
            '',
            $text
        );

        $pageCount = count($pdf->getPages());

        $chunks = $this->chunkText($text);

        $chunks = array_values(array_filter(
            $chunks,
            function ($chunk) {
                return mb_check_encoding(
                    $chunk['content'],
                    'UTF-8'
                );
            }
        ));

        $embeddings = $this->embeddingService->generateBatch(
            array_column($chunks, 'content')
        );

        foreach ($chunks as $index => $chunk) {
            $vector = $this->embeddingService->formatForPgvector($embeddings[$index]);

            DocumentChunk::insert([
                'document_id' => $document->id,
                'content' => $chunk['content'],
                'chunk_index' => $index,
                'token_count' => $chunk['token_count'],
                'embedding' => DB::raw("'{$vector}'::vector"),
                'created_at' => now(),
            ]);
        }

        $document->update([
            'status' => 'ready',
            'page_count' => $pageCount,
        ]);
        DocumentReady::dispatch($document);
    }

    /**
     * Split text into token-based chunks with overlap.
     *
     * @return array<int, array{content: string, token_count: int}>
     */
    private function chunkText(string $text): array
    {
        $encoder = (new EncoderProvider)
            ->getForModel(self::TOKENIZER_MODEL);

        $sentences = preg_split(
            '/(?<=[.!?])\s+/',
            $text,
            -1,
            PREG_SPLIT_NO_EMPTY
        );

        $chunks = [];

        $currentText = '';
        $currentTokens = [];

        foreach ($sentences as $sentence) {

            $sentenceTokens = $encoder->encode($sentence);

            if (
                count($currentTokens) + count($sentenceTokens)
                > self::CHUNK_TOKENS
            ) {

                if ($currentText !== '') {
                    $chunks[] = [
                        'content' => trim($currentText),
                        'token_count' => count($currentTokens),
                    ];
                }

                $overlapTokens = array_slice(
                    $currentTokens,
                    -self::CHUNK_OVERLAP_TOKENS
                );

                $currentTokens = $overlapTokens;
                $currentText = $encoder->decode($overlapTokens);
            }

            $currentText .= ' ' . $sentence;

            $currentTokens = array_merge(
                $currentTokens,
                $sentenceTokens
            );
        }

        if ($currentText !== '') {
            $chunks[] = [
                'content' => trim($currentText),
                'token_count' => count($currentTokens),
            ];
        }

        return $chunks;
    }
}
