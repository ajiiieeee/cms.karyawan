<?php

namespace App\Exports;

use App\Exports\Sheets\PendaftaranDetailKursusSheet;
use App\Exports\Sheets\PendaftaranSiswaSheet;
use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class PendaftaranMultiSheetExport implements Export, WithMultipleSheets
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function sheets(): array
    {
        return [
            new PendaftaranSiswaSheet($this->filters),
            new PendaftaranDetailKursusSheet($this->filters),
        ];
    }
}