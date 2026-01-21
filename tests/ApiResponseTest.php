<?php

declare(strict_types=1);

namespace Sevaske\LaravelApiResponse\Tests;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Sevaske\ApiResponsePayload\ApiResponsePayload;
use Sevaske\LaravelApiResponse\ApiResponse;
use Sevaske\LaravelApiResponse\Contracts\ApiResponseContract;

final class ApiResponseTest extends TestCase
{
    private ApiResponseContract $api;

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

        $this->assertSame([
            'success' => true,
            'message' => 'OK',
            'data' => ['id' => 1],
        ], $response->getData(true));
    }

    public function test_error_response(): void
    {
        $response = $this->api->error('Error', ['field' => 'Required'], 400);

        $this->assertSame([
            'success' => false,
            'message' => 'Error',
            'errors' => ['field' => 'Required'],
        ], $response->getData(true));
    }

    public function test_length_aware_pagination_response(): void
    {
        /** @var array<int, array<string, mixed>> $items */
        $items = [['id' => 1], ['id' => 2]];

        $paginator = new LengthAwarePaginator(
            items: $items,
            total: 10,
            perPage: 2,
            currentPage: 1,
        );

        $resource = $this->resource($paginator, $items);
        $response = $this->api->success('OK', $resource);

        $this->assertSame([
            'success' => true,
            'message' => 'OK',
            'data' => $items,
            'per_page' => 2,
            'current_page' => 1,
            'total' => 10,
            'last_page' => 5,
            'next_page_url' => '/?page=2',
            'prev_page_url' => null,
        ], $response->getData(true));
    }

    public function test_simple_pagination_response(): void
    {
        /** @var array<int, array<string, mixed>> $items */
        $items = [['id' => 1], ['id' => 2]];

        $paginator = new Paginator(
            items: $items,
            perPage: 2,
            currentPage: 1,
        );

        $resource = $this->resource($paginator, $items);
        $response = $this->api->success('OK', $resource);

        $this->assertSame([
            'success' => true,
            'message' => 'OK',
            'data' => $items,
            'per_page' => 2,
            'current_page' => 1,
            'has_more' => false,
            'next_page_url' => null,
            'prev_page_url' => null,
        ], $response->getData(true));
    }

    public function test_cursor_pagination_response(): void
    {
        /** @var array<int, array<string, mixed>> $items */
        $items = [['id' => 1], ['id' => 2]];

        $paginator = new CursorPaginator(
            items: $items,
            perPage: 2,
            cursor: null,
            options: ['path' => '/test'],
        );

        $resource = $this->resource($paginator, $items);
        $response = $this->api->success('OK', $resource);

        $this->assertSame([
            'success' => true,
            'message' => 'OK',
            'data' => $items,
            'per_page' => 2,
            'next_page_url' => null,
            'prev_page_url' => null,
        ], $response->getData(true));
    }

    /**
     * Helper to build JsonResource with paginator.
     *
     * @param  array<int, array<string, mixed>>  $items
     */
    private function resource(mixed $paginator, array $items): JsonResource
    {
        return new class($paginator, $items) extends JsonResource
        {
            /**
             * @param  array<int, array<string, mixed>>  $items
             */
            public function __construct(mixed $resource, private array $items)
            {
                parent::__construct($resource);
            }

            /**
             * @return array<int, array<string, mixed>>
             */
            public function toArray($request): array
            {
                return $this->items;
            }
        };
    }
}
