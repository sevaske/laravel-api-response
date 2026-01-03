<?php

declare(strict_types=1);

namespace Sevaske\LaravelApiResponse\Tests;

use Illuminate\Contracts\Config\Repository;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Sevaske\LaravelApiResponse\Providers\ApiResponseServiceProvider;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            ApiResponseServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        /** @var Repository $config */
        $config = $app['config'];

        $config->set('api-response', [
            'message_key' => 'message',
            'success_key' => 'success',
            'success_value' => true,
            'error_value' => false,
            'response_data_key' => 'data',
            'response_errors_key' => 'errors',
        ]);
    }
}
