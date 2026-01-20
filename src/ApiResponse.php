<?php

declare(strict_types=1);

namespace Sevaske\LaravelApiResponse;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Pagination\Cursor;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Sevaske\ApiResponsePayload\Contracts\ApiResponsePayloadContract;
use Sevaske\LaravelApiResponse\Contracts\ApiResponseContract;

final class ApiResponse implements ApiResponseContract
{
    /**
     * @param  ApiResponsePayloadContract  $payload  Payload builder (core)
     * @param  string  $dataKey  Key for successful response data
     * @param  string  $errorsKey  Key for error details
     */
    public function __construct(
        private ApiResponsePayloadContract $payload,
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
        $pagination = $this->extractPagination($data);

        $resolvedData = $data instanceof JsonResource
            ? $data->resolve()
            : $data;

        /** @var array<string, mixed> $response */
        $response = $this->payload->build(true, $message, [
            $this->dataKey => $resolvedData,
        ]);

        if ($pagination !== null) {
            $response['meta'] = [
                'pagination' => $pagination,
            ];
        }

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

    /**
     * @return array{
     *   per_page: int,
     *   current_page?: int,
     *   total?: int,
     *   last_page?: int,
     *   has_more?: bool,
     *   next_cursor?: string|null,
     *   prev_cursor?: string|null
     * }|null
     */
    private function extractPagination(mixed $data): ?array
    {
        if (! $data instanceof JsonResource) {
            return null;
        }

        $paginator = $data->resource;

        /**
         * Cursor pagination
         */
        if ($paginator instanceof CursorPaginator) {
            $next = $paginator->nextCursor();
            $prev = $paginator->previousCursor();

            return [
                'per_page' => $paginator->perPage(),
                'has_more' => $paginator->hasMorePages(),
                'next_cursor' => $next instanceof Cursor ? $next->encode() : null,
                'prev_cursor' => $prev instanceof Cursor ? $prev->encode() : null,
            ];
        }

        /**
         * Offset pagination
         */
        if (! $paginator instanceof AbstractPaginator) {
            return null;
        }

        $pagination = [
            'per_page' => $paginator->perPage(),
            'current_page' => $paginator->currentPage(),
        ];

        if ($paginator instanceof LengthAwarePaginator) {
            $pagination['total'] = $paginator->total();
            $pagination['last_page'] = $paginator->lastPage();
        }

        if ($paginator instanceof Paginator) {
            $pagination['has_more'] = $paginator->hasMorePages();
        }

        return $pagination;
    }
}
