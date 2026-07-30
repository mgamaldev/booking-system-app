<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\JsonResponse;

trait ReturnsApiResponses
{
    /**
     * @param  array<string, mixed>  $data
     */
    protected function successResponse(array $data, int $status = 200): JsonResponse
    {
        return response()->json([
            'data' => $data,
        ], $status);
    }
}
