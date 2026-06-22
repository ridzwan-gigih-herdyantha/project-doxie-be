<?php

namespace App\Traits;

use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\JsonResponse;

trait ApiResponseTrait
{
    protected function successResponse(mixed $data = null, string $message = 'Success', int $status = 200): JsonResponse
    {
        if ($data instanceof CursorPaginator || $data instanceof Paginator) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'data'    => $data->items(),
                'meta'    => [
                    'next_cursor'    => $data instanceof CursorPaginator ? $data->nextCursor()?->encode() : null,
                    'prev_cursor'    => $data instanceof CursorPaginator ? $data->previousCursor()?->encode() : null,
                    'next_page_url'  => $data->nextPageUrl(),
                    'prev_page_url'  => $data->previousPageUrl(),
                    'per_page'       => $data->perPage(),
                ],
            ], $status);
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], $status);
    }

    protected function errorResponse(string $message = 'Error', int $status = 400, mixed $errors = null): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors'  => $errors,
        ], $status);
    }
}