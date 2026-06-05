<?php

namespace App\Services;

use App\Jobs\ProcessDocumentJob;
use App\Models\Document;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class DocumentService
{
    private const MAX_DOCUMENTS = 5;

    public function getUserDocuments(int $userId)
    {
        return Document::where('user_id', $userId)->latest()->get();
    }

    public function upload(int $userId, UploadedFile $file): Document
    {
        $this->checkDocumentLimit($userId);

        $fileName = $file->getClientOriginalName();
        $filePath = $file->store('documents/'.$userId, 'r2');

        $document = Document::create([
            'user_id' => $userId,
            'title' => pathinfo($fileName, PATHINFO_FILENAME),
            'file_name' => $fileName,
            'file_path' => $filePath,
            'file_size' => $file->getSize(),
            'status' => 'processing',
        ]);

        ProcessDocumentJob::dispatch($document);

        return $document;
    }

    public function delete(Document $document): void
    {
        Storage::disk('r2')->delete($document->file_path);
        $document->delete();
    }

    private function checkDocumentLimit(int $userId): void
    {
        $count = Document::where('user_id', $userId)->count();

        if ($count >= self::MAX_DOCUMENTS) {
            abort(422, 'Maximum document limit reached (5 documents).');
        }
    }
}
