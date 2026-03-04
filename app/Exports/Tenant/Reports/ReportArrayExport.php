<?php

namespace App\Exports\Tenant\Reports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ReportArrayExport implements FromArray, WithHeadings
{
    public function __construct(
        private readonly array $rows,
        private readonly array $headingsRow,
    ) {
    }

    public function array(): array
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return $this->headingsRow;
    }
}
