<?php

declare(strict_types=1);

namespace Sevaske\LaravelApiResponse;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Sevaske\ApiResponsePayload\Contracts\ApiResponsePayloadContract;
use Sevaske\LaravelApiResponse\Contracts\ApiResponseContract;

final readonly class ApiResponse implements ApiResponseContract
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
        if ($data instanceof JsonResource) {
            $rawPayload = $data->response()->getData(true);

            /** @var array<string, mixed> $payload */
            $payload = is_array($rawPayload) ? $rawPayload : [];

            $resourceData = $payload['data'] ?? null;
            unset($payload['data']);

            /** @var array<string, mixed> $extra */
            $extra = [
                $this->dataKey => $resourceData,
                ...$payload,
            ];
        } else {
            /** @var array<string, mixed> $extra */
            $extra = [
                $this->dataKey => $data,
            ];
        }

        /** @var array<string, mixed> $response */
        $response = $this->payload->build(true, $message, $extra);

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
