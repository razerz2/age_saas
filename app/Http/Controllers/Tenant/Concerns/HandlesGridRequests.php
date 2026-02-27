<?php

namespace App\Http\Controllers\Tenant\Concerns;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

trait HandlesGridRequests
{
    /**
     * @param array<int, int> $allowed
     */
    protected function gridPerPage(Request $request, array $allowed = [10, 25, 50, 100], int $default = 10): int
    {
        $requested = (int) $request->input(
            'per_page',
            $request->input('perPage', $request->input('limit', $default))
        );

        return in_array($requested, $allowed, true) ? $requested : $default;
    }

    protected function gridPage(Request $request): int
    {
        return max(1, (int) $request->input('page', 1));
    }

    protected function gridSearch(Request $request): string
    {
        $search = $request->input('search');

        if (is_array($search)) {
            return trim((string) ($search['value'] ?? ''));
        }

        if ($search === null) {
            return trim((string) $request->input('search.value', ''));
        }

        return trim((string) $search);
    }

    /**
     * @param array<string, string> $sortable
     * @return array{key:string,column:string,direction:string}
     */
    protected function gridSort(Request $request, array $sortable, string $defaultKey, string $defaultDirection = 'asc'): array
    {
        $sortInput = $request->input('sort');
        $sortKey = '';
        $rawDirection = '';

        if (is_array($sortInput)) {
            $sortKey = trim((string) ($sortInput['column'] ?? ''));
            $rawDirection = trim((string) ($sortInput['direction'] ?? ''));
        } elseif (is_string($sortInput)) {
            $sortKey = trim($sortInput);

            if (str_contains($sortKey, ':')) {
                [$column, $direction] = array_pad(explode(':', $sortKey, 2), 2, '');
                $sortKey = trim((string) $column);
                $rawDirection = trim((string) $direction);
            } elseif (str_starts_with($sortKey, '-')) {
                $sortKey = ltrim($sortKey, '-');
                $rawDirection = 'desc';
            }
        }

        if ($sortKey === '') {
            $sortKey = trim((string) $request->input('sort.column', ''));
        }

        if ($rawDirection === '') {
            $rawDirection = trim((string) $request->input('dir', ''));
        }

        if ($rawDirection === '') {
            $rawDirection = trim((string) $request->input('direction', ''));
        }

        if ($rawDirection === '') {
            $rawDirection = trim((string) $request->input('sort.direction', $defaultDirection));
        }

        $direction = strtolower($rawDirection) === 'desc' ? 'desc' : 'asc';
        $resolvedKey = array_key_exists($sortKey, $sortable) ? $sortKey : $defaultKey;

        return [
            'key' => $resolvedKey,
            'column' => $sortable[$resolvedKey],
            'direction' => $direction,
        ];
    }

    /**
     * @return array{page:int,per_page:int,total:int,last_page:int,from:int,to:int}
     */
    protected function gridMeta(LengthAwarePaginator $paginator): array
    {
        return [
            'page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'last_page' => $paginator->lastPage(),
            'from' => $paginator->firstItem() ?? 0,
            'to' => $paginator->lastItem() ?? 0,
        ];
    }
}
