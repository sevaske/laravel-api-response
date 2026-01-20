<?php

declare(strict_types=1);

namespace Sevaske\LaravelApiResponse\Pagination;

use Illuminate\Pagination\AbstractCursorPaginator;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Sevaske\LaravelApiResponse\Contracts\PaginationResolverContract;

/**
 * @implements PaginationResolverContract<int, mixed>
 */
final class PaginationResolver implements PaginationResolverContract
{
    public function resolve(
        AbstractPaginator|AbstractCursorPaginator $paginator
    ): array {
        /**
         * Cursor pagination
         */
        if ($paginator instanceof AbstractCursorPaginator) {
            return [
                'per_page' => $paginator->perPage(),
                'next_page_url' => $paginator->nextPageUrl(),
                'prev_page_url' => $paginator->previousPageUrl(),
            ];
        }

        $pagination = [
            'per_page' => $paginator->perPage(),
            'current_page' => $paginator->currentPage(),
        ];

        if ($paginator instanceof LengthAwarePaginator) {
            $pagination['total'] = $paginator->total();
            $pagination['last_page'] = $paginator->lastPage();
        }

        if ($paginator instanceof Paginator) {
            $pagination['has_more'] = $paginator->hasMorePages();
        }

        if ($paginator instanceof Paginator || $paginator instanceof LengthAwarePaginator) {
            $pagination['next_page_url'] = $paginator->nextPageUrl();
            $pagination['prev_page_url'] = $paginator->previousPageUrl();
        }

        return $pagination;
    }
}
