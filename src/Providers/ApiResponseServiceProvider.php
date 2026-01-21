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
        $this->registerPayloadBuilder();
        $this->registerApiResponse();
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../../config/api-response.php' => config_path('api-response.php'),
        ], 'api-response-config');

        $this->registerMacros();
    }

    /**
     * Register payload builder.
     */
    private function registerPayloadBuilder(): void
    {
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
    }

    /**
     * Register API response adapter.
     */
    private function registerApiResponse(): void
    {
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

    /**
     * Register response macros.
     */
    private function registerMacros(): void
    {
        Response::macro('success', function (
            ?string $message = null,
            mixed $data = null,
            int $status = 200
        ) {
            return app(ApiResponseContract::class)->success($message, $data, $status);
        });

        Response::macro('error', function (
            ?string $message = null,
            mixed $errors = null,
            int $status = 400
        ) {
            return app(ApiResponseContract::class)->error($message, $errors, $status);
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
