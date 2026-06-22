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

    /**
     * @OA\Get(
     *     path="/api/documents",
     *     summary="List the authenticated user's documents",
     *     tags={"Documents"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="List of documents",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Document"))
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $documents = $this->documentService->getUserDocuments($request->user()->id);

        return $this->successResponse($documents);
    }

    /**
     * @OA\Post(
     *     path="/api/documents",
     *     summary="Upload a PDF document",
     *     tags={"Documents"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *
     *             @OA\Schema(
     *                 required={"file"},
     *
     *                 @OA\Property(property="file", type="string", format="binary", description="PDF file, max 10 MB")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Document uploaded successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Document uploaded successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Document")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ValidationError")
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate(['file' => 'required|file|mimes:pdf|max:10240']);

        $document = $this->documentService->upload(
            $request->user()->id,
            $request->file('file')
        );

        return $this->successResponse($document, 'Document uploaded successfully', 201);
    }

    /**
     * @OA\Get(
     *     path="/api/documents/{document}",
     *     summary="Show a single document",
     *     tags={"Documents"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="document",
     *         in="path",
     *         required=true,
     *
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Document detail",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(property="data", ref="#/components/schemas/Document")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Not found",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="No query results for model [App\\Models\\Document] 1")
     *         )
     *     )
     * )
     */
    public function show(Request $request, Document $document): JsonResponse
    {
        abort_if($document->user_id !== $request->user()->id, 403);

        return $this->successResponse($document);
    }

    /**
     * @OA\Delete(
     *     path="/api/documents/{document}",
     *     summary="Delete a document",
     *     tags={"Documents"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="document",
     *         in="path",
     *         required=true,
     *
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Document deleted successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Document deleted successfully"),
     *             @OA\Property(property="data", type="object", nullable=true, example=null)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Not found",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="No query results for model [App\\Models\\Document] 1")
     *         )
     *     )
     * )
     */
    public function destroy(Request $request, Document $document): JsonResponse
    {
        abort_if($document->user_id !== $request->user()->id, 403);
        $this->documentService->delete($document);

        return $this->successResponse(null, 'Document deleted successfully');
    }
}
