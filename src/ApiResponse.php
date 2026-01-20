<?php

declare(strict_types=1);

namespace Sevaske\LaravelApiResponse;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\AbstractPaginator;
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

        return response()->json(
            $this->payload->build(true, $message, array_filter([
                $this->dataKey => $data,
                'meta' => $pagination ? ['pagination' => $pagination] : null,
            ])),
            $status
        );
    }

    /**
     * Generic error response.
     */
    public function error(
        ?string $message = null,
        mixed $errors = null,
        int $status = 400
    ): JsonResponse {
        return response()->json(
            $this->payload->build(false, $message, [
                $this->errorsKey => $errors,
            ]),
            $status
        );
    }

    /**
     * 201 Created.
     */
    public function created(
        ?string $message = 'Created',
        mixed $data = null,
    ): JsonResponse {
        return $this->success($message, $data, 201);
    }

    /**
     * 401 Unauthorized.
     */
    public function unauthorized(
        ?string $message = 'Unauthorized',
    ): JsonResponse {
        return $this->error($message, null, 401);
    }

    /**
     * 403 Forbidden.
     */
    public function forbidden(
        ?string $message = 'Forbidden',
    ): JsonResponse {
        return $this->error($message, null, 403);
    }

    /**
     * 404 Not Found.
     */
    public function notFound(
        ?string $message = 'Not Found',
    ): JsonResponse {
        return $this->error($message, null, 404);
    }

    /**
     * 422 Validation error.
     */
    public function validation(
        ?string $message = 'The given data was invalid.',
        mixed $errors = null,
    ): JsonResponse {
        return $this->error($message, $errors, 422);
    }

    /**
     * @return array{
     *   current_page: int,
     *   per_page: int,
     *   total?: int,
     *   last_page?: int,
     *   has_more?: bool
     * }|null
     */
    private function extractPagination(mixed $data): ?array
    {
        if (! $data instanceof JsonResource) {
            return null;
        }

        $paginator = $data->resource;

        if (! $paginator instanceof AbstractPaginator) {
            return null;
        }

        $pagination = [
            'current_page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
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
