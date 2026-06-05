<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentChunk;
use Illuminate\Support\Facades\DB;
use Smalot\PdfParser\Parser;

class DocumentProcessingService
{
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
        $embeddings = $this->embeddingService->generateBatch($chunks);

        foreach ($chunks as $index => $chunk) {
            $vector = $this->embeddingService->formatForPgvector($embeddings[$index]);

            DocumentChunk::insert([
                'document_id' => $document->id,
                'content' => $chunk,
                'chunk_index' => $index,
                'embedding' => DB::raw("'{$vector}'::vector"),
                'created_at' => now(),
            ]);
        }

        $document->update([
            'status' => 'ready',
            'page_count' => $pageCount,
        ]);
    }

    private function chunkText(string $text): array
    {
        $chunkSize = 2000;
        $overlap = 400;
        $chunks = [];
        $start = 0;
        $length = strlen($text);

        while ($start < $length) {
            $chunk = substr($text, $start, $chunkSize);

            if (trim($chunk) !== '') {
                $chunks[] = trim($chunk);
            }

            $start += ($chunkSize - $overlap);
        }

        return $chunks;
    }
}
