<?php

namespace App\Jobs;

use App\Models\Document;
use App\Services\DocumentProcessingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessDocumentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 180;

    public function __construct(private readonly Document $document) {}

    public function handle(DocumentProcessingService $processingService): void
    {
        try {
            $pdfContent = Storage::disk('r2')->get($this->document->file_path);
            $processingService->process($this->document, $pdfContent);
        } catch (\Exception $e) {
            Log::error('Document processing failed', [
                'document_id' => $this->document->id,
                'error' => $e->getMessage(),
            ]);
            $this->document->update(['status' => 'failed']);
            throw $e;
        }
    }
}
