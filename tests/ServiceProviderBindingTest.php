<?php

namespace Tests;

use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Sevaske\ApiResponsePayload\ApiResponsePayload;
use Sevaske\LaravelApiResponse\ApiResponse;
use Sevaske\LaravelApiResponse\Contracts\ApiResponseContract;

class ServiceProviderBindingTest extends TestCase
{
    private Application $appInstance;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var Application $app */
        $app = $this->app;

        $this->appInstance = $app;
    }

    public function test_payload_builder_is_bound(): void
    {
        $payload = $this->appInstance->make(ApiResponsePayload::class);

        $this->assertInstanceOf(ApiResponsePayload::class, $payload);
    }

    public function test_api_response_contract_is_bound(): void
    {
        $response = $this->appInstance->make(ApiResponseContract::class);

        $this->assertInstanceOf(ApiResponse::class, $response);
    }

    public function test_api_response_is_singleton(): void
    {
        $a = $this->appInstance->make(ApiResponseContract::class);
        $b = $this->appInstance->make(ApiResponseContract::class);

        $this->assertSame($a, $b);
    }

    public function test_api_response_binding_can_be_overridden(): void
    {
        $custom = new class implements ApiResponseContract
        {
            public function success(
                ?string $message = null,
                mixed $data = null,
                int $status = 200
            ): JsonResponse {
                return response()->json([
                    'custom' => true,
                ], $status);
            }

            public function error(
                ?string $message = null,
                mixed $errors = null,
                int $status = 400
            ): JsonResponse {
                return response()->json([
                    'custom' => false,
                ], $status);
            }
        };

        $this->appInstance->instance(
            ApiResponseContract::class,
            $custom
        );

        $response = $this->appInstance->make(ApiResponseContract::class);

        $this->assertSame($custom, $response);
        $this->assertInstanceOf(JsonResponse::class, $response->success());
    }
}
