<?php

namespace App\Exports\Tenant\Reports;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ReportQueryExport implements FromQuery, WithHeadings, WithMapping, WithChunkReading
{
    public function __construct(
        private readonly Builder $queryBuilder,
        private readonly array $headingsRow,
        private readonly Closure $mapRow,
        private readonly int $chunk = 1000,
    ) {
    }

    public function query(): Builder
    {
        return $this->queryBuilder;
    }

    public function headings(): array
    {
        return $this->headingsRow;
    }

    public function map($row): array
    {
        return ($this->mapRow)($row);
    }

    public function chunkSize(): int
    {
        return $this->chunk > 0 ? $this->chunk : 1000;
    }
}
