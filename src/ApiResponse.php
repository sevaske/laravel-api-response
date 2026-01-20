<?php

declare(strict_types=1);

namespace Sevaske\LaravelApiResponse;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\AbstractCursorPaginator;
use Illuminate\Pagination\AbstractPaginator;
use Sevaske\ApiResponsePayload\Contracts\ApiResponsePayloadContract;
use Sevaske\LaravelApiResponse\Contracts\ApiResponseContract;
use Sevaske\LaravelApiResponse\Contracts\PaginationResolverContract;

final class ApiResponse implements ApiResponseContract
{
    /**
     * @param  ApiResponsePayloadContract  $payload  Payload builder (core)
     * @param  PaginationResolverContract<int, mixed>  $paginationResolver  Pagination resolver
     * @param  string  $dataKey  Key for successful response data
     * @param  string  $errorsKey  Key for error details
     */
    public function __construct(
        private ApiResponsePayloadContract $payload,
        private PaginationResolverContract $paginationResolver,
        private string $dataKey = 'data',
        private string $errorsKey = 'errors',
    ) {}

    /**
     * Generic successful response.
     */
    public function success(
        ?string $message = null,
        mixed $data = null,
        int $status = 200
    ): JsonResponse {

        $pagination = [];

        if ($data instanceof JsonResource) {
            $paginator = $data->resource;

            if ($paginator instanceof AbstractPaginator || $paginator instanceof AbstractCursorPaginator) {
                $pagination = $this->paginationResolver->resolve($paginator);
            }

            $data = $data->resolve();
        }

        /** @var array<string, mixed> $response */
        $response = $this->payload->build(true, $message, [
            $this->dataKey => $data,
            ...$pagination,
        ]);

        return response()->json($response, $status);
    }

    /**
     * Generic error response.
     */
    public function error(
        ?string $message = null,
        mixed $errors = null,
        int $status = 400
    ): JsonResponse {
        /** @var array<string, mixed> $response */
        $response = $this->payload->build(false, $message, [
            $this->errorsKey => $errors,
        ]);

        return response()->json($response, $status);
    }

    public function created(
        ?string $message = 'Created',
        mixed $data = null
    ): JsonResponse {
        return $this->success($message, $data, 201);
    }

    public function unauthorized(
        ?string $message = 'Unauthorized'
    ): JsonResponse {
        return $this->error($message, null, 401);
    }

    public function forbidden(
        ?string $message = 'Forbidden'
    ): JsonResponse {
        return $this->error($message, null, 403);
    }

    public function notFound(
        ?string $message = 'Not Found'
    ): JsonResponse {
        return $this->error($message, null, 404);
    }

    /**
     * @param  array<string, mixed>|null  $errors
     */
    public function validation(
        ?string $message = 'The given data was invalid.',
        ?array $errors = null
    ): JsonResponse {
        return $this->error($message, $errors, 422);
    }
}
