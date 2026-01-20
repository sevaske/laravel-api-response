<?php

declare(strict_types=1);

namespace Sevaske\LaravelApiResponse\Tests;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Sevaske\ApiResponsePayload\ApiResponsePayload;
use Sevaske\LaravelApiResponse\ApiResponse;

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

    public function test_length_aware_pagination_response(): void
    {
        $items = [
            ['id' => 1],
            ['id' => 2],
        ];

        $paginator = new LengthAwarePaginator(
            items: $items,
            total: 10,
            perPage: 2,
            currentPage: 1,
        );

        $resource = new class($paginator, $items) extends JsonResource
        {
            /** @var array<int, array<string, mixed>> */
            private array $items;

            /**
             * @param  array<int, array<string, mixed>>  $items
             */
            public function __construct(mixed $resource, array $items)
            {
                parent::__construct($resource);
                $this->items = $items;
            }

            /**
             * @return array<int, array<string, mixed>>
             */
            public function toArray($request): array
            {
                return $this->items;
            }
        };

        $response = $this->api->success('OK', $resource);

        $this->assertSame([
            'success' => true,
            'message' => 'OK',
            'data' => $items,
            'meta' => [
                'pagination' => [
                    'current_page' => 1,
                    'per_page' => 2,
                    'total' => 10,
                    'last_page' => 5,
                ],
            ],
        ], $response->getData(true));
    }

    public function test_simple_pagination_response(): void
    {
        $items = [
            ['id' => 1],
            ['id' => 2],
        ];

        $paginator = new Paginator(
            items: $items,
            perPage: 2,
            currentPage: 1,
        );

        $resource = new class($paginator, $items) extends JsonResource
        {
            /** @var array<int, array<string, mixed>> */
            private array $items;

            /**
             * @param  array<int, array<string, mixed>>  $items
             */
            public function __construct(mixed $resource, array $items)
            {
                parent::__construct($resource);
                $this->items = $items;
            }

            /**
             * @return array<int, array<string, mixed>>
             */
            public function toArray($request): array
            {
                return $this->items;
            }
        };

        $response = $this->api->success('OK', $resource);

        $this->assertSame([
            'success' => true,
            'message' => 'OK',
            'data' => $items,
            'meta' => [
                'pagination' => [
                    'current_page' => 1,
                    'per_page' => 2,
                    'has_more' => false,
                ],
            ],
        ], $response->getData(true));
    }
}
