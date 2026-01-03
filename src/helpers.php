<?php

declare(strict_types=1);

use Sevaske\LaravelApiResponse\Contracts\ApiResponseContract;

if (! function_exists('api')) {
    function api(): ApiResponseContract
    {
        return app(ApiResponseContract::class);
    }
}
