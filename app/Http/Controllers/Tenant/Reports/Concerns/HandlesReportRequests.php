<?php

namespace App\Http\Controllers\Tenant\Reports\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

trait HandlesReportRequests
{
    protected function resolvePerPage(Request $request, int $default = 25): int
    {
        $allowed = [10, 25, 50, 100];
        $requested = (int) $request->input('per_page', $request->input('limit', $default));

        return in_array($requested, $allowed, true) ? $requested : $default;
    }

    protected function resolvePage(Request $request): int
    {
        return max(1, (int) $request->input('page', 1));
    }

    protected function resolveSearchTerm(Request $request): string
    {
        $search = $request->input('search', '');

        if (is_array($search)) {
            $search = $search['value'] ?? '';
        }

        return trim((string) $search);
    }

    /**
     * @return array{column: string, direction: string}
     */
    protected function resolveSort(
        Request $request,
        array $sortable,
        string $defaultColumn,
        string $defaultDirection = 'desc'
    ): array {
        $sortInput = $request->input('sort');
        $dirInput = $request->input('dir', $defaultDirection);

        $sortKey = null;

        if (is_array($sortInput)) {
            $sortKey = $sortInput['column'] ?? null;
            $dirInput = $sortInput['direction'] ?? $dirInput;
        } elseif (is_string($sortInput) && $sortInput !== '') {
            $sortKey = $sortInput;
        }

        $column = $sortable[$sortKey] ?? $defaultColumn;
        $direction = strtolower((string) $dirInput) === 'desc' ? 'desc' : 'asc';

        return [
            'column' => $column,
            'direction' => $direction,
        ];
    }

    protected function paginateQuery(Builder $query, Request $request, int $defaultPerPage = 25): LengthAwarePaginator
    {
        $perPage = $this->resolvePerPage($request, $defaultPerPage);
        $page = $this->resolvePage($request);

        return $query
            ->paginate($perPage, ['*'], 'page', $page)
            ->appends($request->query());
    }

    protected function paginationMeta(LengthAwarePaginator $paginator): array
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

    protected function gridResponse(LengthAwarePaginator $paginator, array $rows, array $extra = []): JsonResponse
    {
        return response()->json(array_merge([
            'data' => $rows,
            'meta' => $this->paginationMeta($paginator),
        ], $extra));
    }
}
