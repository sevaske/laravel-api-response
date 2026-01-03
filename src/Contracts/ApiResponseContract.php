<?php

namespace Sevaske\LaravelApiResponse\Contracts;

use Illuminate\Http\JsonResponse;

interface ApiResponseContract
{
    /**
     * Successful API response
     */
    public function success(?string $message = null, mixed $data = null, int $status = 200): JsonResponse;

    /**
     * Error API response
     */
    public function error(?string $message = null, mixed $errors = null, int $status = 400): JsonResponse;
}
