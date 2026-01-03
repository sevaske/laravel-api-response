<?php

declare(strict_types=1);

namespace Sevaske\LaravelApiResponse\Tests\Unit;

use Sevaske\ApiResponsePayload\ApiResponsePayload;
use Sevaske\LaravelApiResponse\ApiResponse;
use Sevaske\LaravelApiResponse\Tests\TestCase;

final class ApiResponseTest extends TestCase
{
    private ApiResponse $api;

    protected function setUp(): void
    {
        parent::setUp();

        $payload = new ApiResponsePayload(
            messageKey: 'message',
            successKey: 'success',
            successValue: true,
            errorValue: false,
        );

        $this->api = new ApiResponse(
            payload: $payload,
            dataKey: 'data',
            errorsKey: 'errors',
        );
    }

    public function test_success_response(): void
    {
        $response = $this->api->success('OK', ['id' => 1]);

        $this->assertSame(200, $response->getStatusCode());

        $this->assertSame([
            'success' => true,
            'message' => 'OK',
            'data' => ['id' => 1],
        ], $response->getData(true));
    }

    public function test_error_response(): void
    {
        $response = $this->api->error('Error', ['field' => 'Required'], 400);

        $this->assertSame(400, $response->getStatusCode());

        $this->assertSame([
            'success' => false,
            'message' => 'Error',
            'errors' => ['field' => 'Required'],
        ], $response->getData(true));
    }

    public function test_created_response(): void
    {
        $response = $this->api->created('Created', ['id' => 10]);

        $this->assertSame(201, $response->getStatusCode());
    }

    public function test_validation_response(): void
    {
        $response = $this->api->validation(
            errors: ['email' => ['Invalid']]
        );

        $this->assertSame(422, $response->getStatusCode());
        $this->assertSame([
            'success' => false,
            'message' => 'The given data was invalid.',
            'errors' => ['email' => ['Invalid']],
        ], $response->getData(true));
    }
}
