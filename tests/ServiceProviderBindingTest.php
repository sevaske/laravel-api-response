<?php

namespace Sevaske\LaravelApiResponse\Tests\Unit;

use Illuminate\Foundation\Application;
use Sevaske\ApiResponsePayload\ApiResponsePayload;
use Sevaske\LaravelApiResponse\ApiResponse;
use Sevaske\LaravelApiResponse\Contracts\ApiResponseContract;
use Sevaske\LaravelApiResponse\Tests\TestCase;

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
}
