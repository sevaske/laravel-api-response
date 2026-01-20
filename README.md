# Laravel API Response

**A simple library for a simple task**: building consistent JSON API responses in Laravel. Fully customizable when you need it

#### What this package is

* a small abstraction over JSON responses
* a way to standardize API responses across your app
* minimal by default, customizable by design
* IDE-friendly

#### Default response format

Out of the box, the response looks like this:

```json
{
  "success": true,
  "message": "OK",
  "data": {
    "id": 1
  }
}
```

Error response:

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "email": "Invalid"
  }
}
```

All keys and values are fully configurable via config or by replacing the payload builder.

## Installation

```bash
composer require sevaske/laravel-api-response
```

Optional config publishing:

```bash
php artisan vendor:publish --tag=api-response-config
```

## Usage

#### 1. Dependency Injection (recommended)

```php
use Sevaske\LaravelApiResponse\Contracts\ApiResponseContract;

class UserController
{
    public function __construct(
        private ApiResponseContract $api
    ) {}

    public function index()
    {
        return $this->api->success('OK', [
            'id' => 1,
        ]);
    }
}
```

#### 2. Via `response()` macros

```php
return response()->success(
    message: 'OK',
    data: ['id' => 1],
);

return response()->error(
    message: 'Validation failed',
    errors: ['email' => 'Invalid']
);
```

#### 3. Via helper

```php
return api()->success(
    message: 'OK',
    data: ['id' => 1],
);
```

## Pagination

Pagination is detected automatically when a Laravel `JsonResource` wrapping a paginator
is passed as `data`.

Supported paginators:

* `LengthAwarePaginator` (`paginate()`)
* `Paginator` (`simplePaginate()`)
* `CursorPaginator` (`cursorPaginate()`)

```php
use App\Http\Resources\UserResource;
use App\Models\User;

$users = User::paginate();

return api()->success(
    data: UserResource::collection($users)
);
```

### Pagination response format

Pagination fields are added **at the top level of the response**, alongside the `data` key,
following Laravel's native pagination style (no `meta` wrapper).

**Length-aware pagination (`paginate()`):**

```json
{
  "success": true,
  "data": [
    {"id": 1},
    {"id": 2}
  ],
  "per_page": 15,
  "current_page": 1,
  "total": 100,
  "last_page": 7,
  "next_page_url": "/?page=2",
  "prev_page_url": null
}
```

**Simple pagination (`simplePaginate()`):**

```json
{
  "success": true,
  "data": [
    {"id": 1},
    {"id": 2}
  ],
  "per_page": 15,
  "current_page": 1,
  "has_more": true,
  "next_page_url": "/?page=2",
  "prev_page_url": null
}
```

**Cursor pagination (`cursorPaginate()`):**

```json
{
  "success": true,
  "data": [
    {"id": 1},
    {"id": 2}
  ],
  "per_page": 15,
  "has_more": true,
  "next_cursor": "eyJpZCI6Mn0",
  "prev_cursor": null
}
```

### Custom pagination resolver

Pagination extraction is handled by a dedicated resolver. You can replace it with your own
implementation if you need a different pagination structure.

```php
// config/api-response.php

return [
    'pagination' => [
        // Resolver responsible for extracting pagination data
        // from Laravel paginator instances
        'resolver' => Sevaske\LaravelApiResponse\Pagination\PaginationResolver::class,
    ],
];
```

Bind your own resolver:

```php
use Sevaske\LaravelApiResponse\Contracts\PaginationResolverContract;

$this->app->bind(
    PaginationResolverContract::class,
    MyCustomPaginationResolver::class
);
```

## Customization

Change response keys:

```php
return [
    'success_key' => 'ok',
    'message_key' => 'msg',
    'data_key'    => 'results',
    'errors_key'  => 'errors',
];
```

Change the "success" value format:

```php
return [
    'success_value' => 1,
    'error_value'   => 0,
];
```

## Extending

Bind your own response implementation

```php
use Sevaske\LaravelApiResponse\Contracts\ApiResponseContract;

$this->app->bind(ApiResponseContract::class, MyCustomApiResponse::class);
```

Replace the payload builder

```php
use Sevaske\ApiResponsePayload\Contracts\ApiResponsePayloadContract;

$this->app->bind(ApiResponsePayloadContract::class, MyPayloadBuilder::class);
```

This allows full control over the final response structure without touching controllers.

## License

[MIT](LICENSE)
