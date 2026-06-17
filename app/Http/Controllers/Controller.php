<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     title="Doxie AI API",
 *     version="1.0.0",
 *     description="AI Document Q&A SaaS - Upload PDF dan chat dengan dokumen via RAG",
 *
 *     @OA\Contact(email="you@example.com")
 * )
 *
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="Local development server"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="Token"
 * )
 *
 * @OA\Schema(
 *     schema="User",
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="John Doe"),
 *     @OA\Property(property="email", type="string", format="email", example="admin@doxie.ai"),
 *     @OA\Property(property="avatar_url", type="string", description="Auto-generated initial avatar as a base64-encoded SVG data URI", example="data:image/svg+xml;base64,PHN2ZyB4bWxucz0i..."),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2026-06-17T08:30:00.000000Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2026-06-17T08:30:00.000000Z")
 * )
 *
 * @OA\Schema(
 *     schema="Document",
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="title", type="string", example="Annual Report 2025"),
 *     @OA\Property(property="file_name", type="string", example="annual-report-2025.pdf"),
 *     @OA\Property(property="file_path", type="string", example="documents/1/annual-report-2025.pdf"),
 *     @OA\Property(property="file_size", type="integer", example=482133),
 *     @OA\Property(property="status", type="string", enum={"processing", "ready", "failed"}, example="ready"),
 *     @OA\Property(property="page_count", type="integer", example=12),
 *     @OA\Property(property="file_url", type="string", nullable=true, example="https://r2.example.com/documents/1/annual-report-2025.pdf?signature=..."),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2026-06-17T08:30:00.000000Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2026-06-17T08:30:00.000000Z")
 * )
 *
 * @OA\Schema(
 *     schema="ChatSession",
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="document_id", type="integer", example=1),
 *     @OA\Property(property="title", type="string", example="Chat about Annual Report 2025"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2026-06-17T08:30:00.000000Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2026-06-17T08:30:00.000000Z")
 * )
 *
 * @OA\Schema(
 *     schema="Message",
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="chat_session_id", type="integer", example=1),
 *     @OA\Property(property="role", type="string", enum={"user", "assistant"}, example="assistant"),
 *     @OA\Property(property="content", type="string", example="This document is the 2025 annual report covering financial performance and outlook."),
 *     @OA\Property(property="model_used", type="string", nullable=true, example="gpt-4o"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2026-06-17T08:30:00.000000Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2026-06-17T08:30:00.000000Z")
 * )
 *
 * @OA\Schema(
 *     schema="ValidationError",
 *
 *     @OA\Property(property="message", type="string", example="The given data was invalid."),
 *     @OA\Property(property="errors", type="object", example={"email": {"The email field is required."}})
 * )
 *
 * @OA\Schema(
 *     schema="ErrorResponse",
 *
 *     @OA\Property(property="success", type="boolean", example=false),
 *     @OA\Property(property="message", type="string", example="Forbidden"),
 *     @OA\Property(property="errors", type="object", nullable=true, example=null)
 * )
 */
abstract class Controller
{
    //
}
