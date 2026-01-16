<?php

declare(strict_types=1);

namespace Sevaske\LaravelApiResponse\Providers;

use Illuminate\Config\Repository;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\ServiceProvider;
use Sevaske\ApiResponsePayload\ApiResponsePayload;
use Sevaske\ApiResponsePayload\Contracts\ApiResponsePayloadContract;
use Sevaske\LaravelApiResponse\ApiResponse;
use Sevaske\LaravelApiResponse\Contracts\ApiResponseContract;

final class ApiResponseServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        /**
         * Payload builder
         */
        $this->app->singleton(ApiResponsePayloadContract::class, function (Application $app) {
            /** @var Repository $config */
            $config = $app['config'];

            return new ApiResponsePayload(
                messageKey: $config->string('api-response.message_key', 'message'),
                successKey: $config->string('api-response.success_key', 'success'),
                successValue: $this->resolveScalar(
                    $config->get('api-response.success_value', true),
                    true
                ),
                errorValue: $this->resolveScalar(
                    $config->get('api-response.error_value', false),
                    false
                ),
            );
        });

        /**
         * Response adapter
         */
        $this->app->singleton(ApiResponseContract::class, function (Application $app) {
            /** @var Repository $config */
            $config = $app['config'];

            return new ApiResponse(
                payload: $app->make(ApiResponsePayloadContract::class),
                dataKey: $config->string('api-response.data_key', 'data'),
                errorsKey: $config->string('api-response.errors_key', 'errors'),
            );
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../../config/api-response.php' => config_path('api-response.php'),
        ], 'api-response-config');

        $this->registerMacros();
    }

    protected function registerMacros(): void
    {
        Response::macro('success', function (
            ?string $message = null,
            mixed $data = null,
            int $status = 200
        ) {
            /** @var ApiResponseContract $api */
            $api = app(ApiResponseContract::class);

            return $api->success($message, $data, $status);
        });

        Response::macro('error', function (
            ?string $message = null,
            mixed $errors = null,
            int $status = 400
        ) {
            /** @var ApiResponseContract $api */
            $api = app(ApiResponseContract::class);

            return $api->error($message, $errors, $status);
        });
    }

    /**
     * Ensures config value is scalar and valid for payload.
     */
    private function resolveScalar(
        mixed $value,
        bool|int|string $default
    ): bool|int|string {
        return match (true) {
            is_bool($value),
            is_int($value),
            is_string($value) => $value,
            default => $default,
        };
    }
}
