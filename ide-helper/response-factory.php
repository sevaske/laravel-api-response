<?php

namespace Illuminate\Contracts\Routing;

use Illuminate\Http\JsonResponse;

/**
 * @method JsonResponse success(?string $message = null, mixed $data = null, int $status = 200)
 * @method JsonResponse error(?string $message = null, mixed $errors = null, int $status = 400)
 * @method JsonResponse created(?string $message = 'Created', mixed $data = null)
 * @method JsonResponse unauthorized(?string $message = 'Unauthorized')
 * @method JsonResponse forbidden(?string $message = 'Forbidden')
 * @method JsonResponse notFound(?string $message = 'Not Found')
 * @method JsonResponse validation(?string $message = 'The given data was invalid.', mixed $errors = null)
 */
interface ResponseFactory {}
