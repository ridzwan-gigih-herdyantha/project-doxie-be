<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Services\DocumentService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    use ApiResponseTrait;

    public function __construct(private readonly DocumentService $documentService) {}

    public function index(Request $request): JsonResponse
    {
        $documents = $this->documentService->getUserDocuments($request->user()->id);

        return $this->successResponse($documents);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate(['file' => 'required|file|mimes:pdf|max:10240']);

        $document = $this->documentService->upload(
            $request->user()->id,
            $request->file('file')
        );

        return $this->successResponse($document, 'Document uploaded successfully', 201);
    }

    public function show(Request $request, Document $document): JsonResponse
    {
        abort_if($document->user_id !== $request->user()->id, 403);

        return $this->successResponse($document);
    }

    public function destroy(Request $request, Document $document): JsonResponse
    {
        abort_if($document->user_id !== $request->user()->id, 403);
        $this->documentService->delete($document);

        return $this->successResponse(null, 'Document deleted successfully');
    }
}
