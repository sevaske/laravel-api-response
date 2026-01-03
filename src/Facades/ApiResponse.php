<?php

namespace Sevaske\LaravelApiResponse\Facades;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Facade;

/**
 * @method static JsonResponse success(string $message, mixed $data = null, int $status = 200)
 * @method static JsonResponse error(string $message, mixed $errors = null, int $status = 400)
 *
 * @see \Sevaske\LaravelApiResponse\ApiResponse
 */
class ApiResponse extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'api-response';
    }
}
