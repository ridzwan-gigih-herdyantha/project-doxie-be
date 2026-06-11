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
 *     url="http://localhost:8000",
 *     description="Local development server"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="Token"
 * )
 */
abstract class Controller
{
    //
}
