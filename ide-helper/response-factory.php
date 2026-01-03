<?php

namespace Illuminate\Contracts\Routing;

use Illuminate\Http\JsonResponse;

/**
 * @method JsonResponse success(?string $message = null, mixed $data = null, int $status = 200)
 * @method JsonResponse error(?string $message = null, mixed $errors = null, int $status = 400)
 */
interface ResponseFactory {}
