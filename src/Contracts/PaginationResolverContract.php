<?php

declare(strict_types=1);

namespace Sevaske\LaravelApiResponse\Contracts;

use Illuminate\Pagination\AbstractCursorPaginator;
use Illuminate\Pagination\AbstractPaginator;

/**
 * @template TKey of array-key
 * @template TValue
 */
interface PaginationResolverContract
{
    /**
     * @param  AbstractPaginator<TKey, TValue>|AbstractCursorPaginator<TKey, TValue>  $paginator
     * @return array<string, mixed>
     */
    public function resolve(
        AbstractPaginator|AbstractCursorPaginator $paginator
    ): array;
}
