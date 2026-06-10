<?php

namespace App\Services;

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
        $pageCount = count($pdf->getPages());

        $chunks = $this->chunkText($text);
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
    }

    /**
     * Split text into token-based chunks with overlap.
     *
     * @return array<int, array{content: string, token_count: int}>
     */
    private function chunkText(string $text): array
    {
        $encoder = (new EncoderProvider)->getForModel(self::TOKENIZER_MODEL);
        $tokens = $encoder->encode($text);
        $total = count($tokens);

        if ($total === 0) {
            return [];
        }

        $step = self::CHUNK_TOKENS - self::CHUNK_OVERLAP_TOKENS;
        $chunks = [];

        for ($start = 0; $start < $total; $start += $step) {
            $slice = array_slice($tokens, $start, self::CHUNK_TOKENS);
            $content = trim($encoder->decode($slice));

            if ($content !== '') {
                $chunks[] = [
                    'content' => $content,
                    'token_count' => count($slice),
                ];
            }
        }

        return $chunks;
    }
}
