<?php

declare(strict_types=1);

namespace Sevaske\LaravelApiResponse\Tests;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
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
        /** @var array<string, mixed> $response */
        $response = $this->api->success('OK', ['id' => 1])->getData(true);

        $this->assertSame([
            'success' => true,
            'message' => 'OK',
            'data' => ['id' => 1],
        ], $response);
    }

    public function test_error_response(): void
    {
        /** @var array<string, mixed> $response */
        $response = $this->api->error('Error', ['field' => 'Required'], 400)->getData(true);

        $this->assertSame([
            'success' => false,
            'message' => 'Error',
            'errors' => ['field' => 'Required'],
        ], $response);
    }

    public function test_length_aware_pagination_response(): void
    {
        $paginator = new LengthAwarePaginator(
            items: $this->items(),
            total: 10,
            perPage: 2,
            currentPage: 1,
            options: ['path' => '/users']
        );

        /** @var array<string, mixed> $response */
        $response = $this->api
            ->success('OK', JsonResource::collection($paginator))
            ->getData(true);

        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('meta', $response);
        $this->assertArrayHasKey('links', $response);

        $this->assertArrayHasKey('data', $response);

        /** @var array<int, mixed> $data */
        $data = $response['data'];

        $this->assertCount(2, $data);

        /** @var array<string, mixed> $meta */
        $meta = $response['meta'];

        $this->assertSame(10, $meta['total']);
        $this->assertSame(5, $meta['last_page']);
        $this->assertSame(1, $meta['current_page']);
    }

    public function test_simple_pagination_response(): void
    {
        $paginator = new Paginator(
            items: $this->items(),
            perPage: 2,
            currentPage: 1,
            options: ['path' => '/users']
        );

        /** @var array<string, mixed> $response */
        $response = $this->api
            ->success('OK', JsonResource::collection($paginator))
            ->getData(true);

        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('meta', $response);
        $this->assertArrayHasKey('links', $response);

        /** @var array<string, mixed> $meta */
        $meta = $response['meta'];

        $this->assertSame(2, $meta['per_page']);
    }

    public function test_cursor_pagination_response(): void
    {
        $paginator = new CursorPaginator(
            items: $this->items(),
            perPage: 2,
            cursor: null,
            options: ['path' => '/users']
        );

        /** @var array<string, mixed> $response */
        $response = $this->api
            ->success('OK', JsonResource::collection($paginator))
            ->getData(true);

        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('meta', $response);
        $this->assertArrayHasKey('links', $response);

        /** @var array<string, mixed> $meta */
        $meta = $response['meta'];

        $this->assertSame(2, $meta['per_page']);
    }

    /**
     * @return Collection<int, array{id:int}>
     */
    private function items(): Collection
    {
        return collect([
            ['id' => 1],
            ['id' => 2],
        ]);
    }
}
